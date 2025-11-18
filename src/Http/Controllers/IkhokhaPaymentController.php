<?php


namespace Elmmac\Ikhokha\Http\Controllers;

use App\Http\Controllers\Controller;
use Elmmac\Ikhokha\Models\IkhokhaPayment;
use Elmmac\Ikhokha\IkhokhaClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class IkhokhaPaymentController extends Controller
{
    public function initiate(Request $request): Redirector|RedirectResponse
    {
        // Fetch, Set iKhokha & other settings
        $endpoint = config('ikhokha.base_url');
        $appID = config('ikhokha.entity_id');
        $appSecret = config('ikhokha.app_secret');
        $currency = config('ikhokha.currency');
        $mode = config('ikhokha.mode');
        $amount = $request->input(key: 'amount');
        // $amount = 20;
        $user = Auth::user(); // for testing, replace with actual user retrieval
        $externalTransactionID = 'IKH_' . strtoupper(string: Str::random(length: 10));

        // Prepare request body
        $body = [
            "entityID" => $appID,
            "amount" => (int) $amount * 100,
            "currency" => $currency,
            "requesterUrl" => url()->current(),
            "mode" => $mode,
            "externalTransactionID" => $externalTransactionID,
            "urls" => [
                "callbackUrl" => route(name: 'ikhokha.webhook'),
                "successPageUrl" => route(name: 'ikhokha.success'),
                "failurePageUrl" => route(name: 'ikhokha.failed'),
                "cancelUrl" => route(name: 'ikhokha.cancel'),
            ]
        ];

        // Start iKhokha cURL Flow - PHP
        $stringifiedBody = json_encode(value: $body);
        $payloadToSign = $this->createPayloadToSign(urlPath: $endpoint, body: $stringifiedBody);
        $ikSign = $this->generateSignature(payloadToSign: $payloadToSign, secret: $appSecret);

        // Initialize cURL session
        $ch = curl_init(url: $endpoint);
        // Set cURL options
        curl_setopt(handle: $ch, option: CURLOPT_CUSTOMREQUEST, value: "POST");
        curl_setopt(handle: $ch, option: CURLOPT_POSTFIELDS, value: $stringifiedBody);
        curl_setopt(handle: $ch, option: CURLOPT_RETURNTRANSFER, value: true);
        curl_setopt(handle: $ch, option: CURLOPT_HTTPHEADER, value: [
            "Content-Type: application/json",
            "IK-APPID: $appID",
            "IK-SIGN: $ikSign"
        ]);
        // End iKhokha cURL Flow - PHP

        Log::info(message: 'iKhokha Signature Data', context: [
            'signature' => $ikSign,
            $body,
        ]);

        // Execute cURL session
        $response = curl_exec(handle: $ch);
        curl_close(handle: $ch);
        $data = json_decode(json: $response, associative: true);

        // Handle errors safely
        if (!$response || empty($data['paylinkUrl'])) {
            Log::error(message: 'iKhokha Payment Error', context: ['response' => $data]);
            return back()->with(key: 'error', value: 'Payment initialization failed. Please try again.');
        }

        // Save Payment Record
        IkhokhaPayment::create(attributes: [
            'user_id' => $user->id ?? null, // (comment below line for testing & uncomment this line)
            // 'user_id' => 1, // for testing (replace with actual user retrieval uncomment above line)
            'customer_email' => $user->email ?? $request->input(key: 'customer_email', default: 'misael@elmmac.co.za'),
            // 'customer_email' => 'misael@elmmac.co.za',
            'transaction_id' => $externalTransactionID,
            // 'description' => $request->input('description', 'Payment'),
            'description' => 'Test Payment', // for testing (Replace with actual description & uncomment above line but comment this line)
            'paylink_id' => $data['paylinkID'] ?? null,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'payment_url' => $data['paylinkUrl'] ?? null,
            'webhook_signature' => $ikSign,
            'metadata' => $data,
        ]);

        Log::info(message: 'iKhokha Payment Initiated', context: ['response' => $data]);
        // Redirect user to iKhokha payment page
        return redirect(to: $data['paylinkUrl']);

    }

    public function webhook(Request $request): Response
    {
        // We are verifying the transaction against the webhook payload status NOT the Signature as per iKhokha docs. ikhokha signature creation is not consistent.

        // Log raw payload for debugging first
        Log::info('iKhokha Webhook Hit and webhook received');

        // 🧾 Capture & log webhook for records
        $headers = $request->headers->all();
        $payload = $request->all();

        Log::info('iKhokha Webhook received', [
            'headers' => $headers,
            'payload_raw' => $payload,
        ]);

        $status = strtoupper($payload['status'] ?? '');
        $externalTransactionID = $payload['externalTransactionID'] ?? null;
        $paylinkID = $payload['paylinkID'] ?? null;

        if (!$externalTransactionID) {
            Log::error('Webhook missing externalTransactionID', ['payload' => $payload]);
            return response('Bad Request', 400);
        }

        // 🧩 Locate payment record
        $payment = IkhokhaPayment::where('transaction_id', $externalTransactionID)->first();
        if (!$payment) {
            Log::error('Webhook: Payment Record not found', ['transaction_id' => $externalTransactionID]);
            return response('Payment not found', 404);
        }

        // ♻️ Prevent double processing
        if ($payment->status === 'successful') {
            return response('This Transaction Already processed', 200);
        }

        // 🧠 Save whatever iKhokha sends, just for future audits
        $receivedSign = $headers['ik-sign'][0] ?? null;
        $payment->update([
            'webhook_signature' => $receivedSign, // update latest received signature
            'webhook_payload' => json_encode($payload),
        ]);

        // ✅ Trust the payload: if SUCCESS, it’s paid
        if ($status === 'SUCCESS' || $status === 'PAID') {
            try {
                $payment->update(['status' => 'successful']);
                // TODO: Add your business logic here, e.g., mark order as paid, send email, etc.

                Log::info('✅ Payment confirmed via payload, Order marked as paid', ['reference' => $payment->transaction_id]);

            } catch (Exception $e) {
                Log::error('Failed to process successful payment webhook', [
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $payment->update(['status' => 'failed']);
            Log::warning('⚠️ Payment marked as failed by webhook', [
                'transaction_id' => $payment->transaction_id,
                'status' => $status,
            ]);
        }

        return response('OK', 200);
    }

    public function success(): View
    {
        // return view('ikhokha.success'); // make Blade view or simple msg
        return view('ikhokha::success');
    }

    public function failed(): View
    {
        // return view('ikhokha.failed');
        return view('ikhokha::failed');
    }

    public function cancel(): View
    {
        // return view('ikhokha.cancel');
        return view('ikhokha::cancel');
    }


    // start helper methods here
    public static function escapeString($str): array|string
    {
        $escaped = preg_replace(['/[\\"\'\"]/u', '/\x00/'], ['\\\\$0', '\\0'], (string) $str);
        $cleaned = str_replace('\/', '/', $escaped);
        return $cleaned;
    }

    public static function createPayloadToSign($urlPath, $body): array|string
    {
        $parsedUrl = parse_url($urlPath);
        $basePath = $parsedUrl['path'];
        if (!$basePath) {
            throw new Exception("No path present in the URL");
        }
        $payload = $basePath . $body;
        $escapedPayloadString = self::escapeString($payload);
        return $escapedPayloadString;
    }

    public static function generateSignature($payloadToSign, $secret): string
    {
        return hash_hmac('sha256', $payloadToSign, $secret);
    }

    // get ikhokha seetings if needed from DB settings table and not env file
    static function getSetting($key, $default = null): mixed
    {
        try {
            return DB::table('settings')->where('key', $key)->value('value') ?? $default;
        } catch (Exception $e) {
            Log::error('Failed to fetch setting: ' . $key, ['error' => $e->getMessage()]);
            return $default;
        }
    }
    // End helper methods here

}