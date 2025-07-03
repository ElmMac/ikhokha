<?php


namespace Elmmac\Ikhokha\Http\Controllers;

use App\Http\Controllers\Controller;
use Elmmac\Ikhokha\Models\IkhokhaPayment;
use Elmmac\Ikhokha\IkhokhaClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IkhokhaPaymentController extends Controller
{
    public function initiate(Request $request)
    {
        // We'll build this to call IkhokhaClient and generate payment link
        // validate input details
        $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string|max:5', // Optional, default is 'ZAR'
            'description' => 'nullable|string', // order id unique
        ]);

        $transactionId = strtoupper(uniqid('IKH_')); // e.g., IKH_65C2D71F3A6D1
        $payment = IkhokhaPayment::create(
            [
                'user_id' => auth()->id(), // attach user
                'transaction_id' => $transactionId,
                'amount' => $request->amount,
                'currency' => 'ZAR',
                'status' => 'PENDING',
                'description' => $request->description,
            ]

        );
        Log::info('order saved in DB');

        // Prepare API request to ikhokha
        $payload = [
            "entityID" => config('ikhokha.entry_id'),
            "externalEntityID" => config('ikhokha.external_entity_id'),
            "amount" => 5000, // dynamically be generated
            "currency" => "ZAR",
            "requesterUrl" => config('ikhokha.urls.requester_url'),
            "mode" => "live",
            "externalTransactionID" => $transactionId,
            "urls" => [
                "callbackUrl" => config('ikhokha.urls.callback_url'),
                "successPageUrl" => config('ikhokha.urls.success_url'),
                "failurePageUrl" => config('ikhokha.urls.failure_url'),
                "cancelUrl" => config('ikhokha.urls.cancel_url')
            ]
        ];

        // Step 4: Send POST request to iKhokha — JSON request body
        // $response = Http::withHeaders(headers: [
        //     'Accept' => 'application/json',
        //     'Content-Type' => 'application/json',
        // ])->post(url: config(key: 'ikhokha.base_url'), data: $payload);
        $client = new IkhokhaClient();
        $response = $client->createPaymentLink($payload);

        // Step 5: Handle JSON response
        $responseData = $response->json();
        if ($responseData['responseCode'] === "00" && $responseData['externalTransactionID'] === $transactionId) {
            // Save iKhokha payment link & ID
            $payment->update([
                'paylink_id' => $responseData['paylinkID'] ?? null,
                'paylink_url' => $responseData['paylinkUrl'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'paylink' => $responseData['paylinkUrl']
            ]);
        } else {
            Log::error('iKhokha Payment Init Error', [
                'response' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.'
            ], 500);
        }


    }

    public function handleWebhook(Request $request)
    {
        // Log raw payload for debugging first
        Log::info('iKhokha Webhook Hit', ['payload' => $request->getContent()]);

        // Get headers
        $signature = $request->header('ik-sign');
        $appId = $request->header('ik-appid');
        $callbackUrl = config('ikhokha.urls.callback_url');

        // 1. Grab the JSON payload
        // payload
        // $data = $request->all(); // output string
        $payload = $request->getContent(); // output an array

        // Step 3: Validate signature (protect your cashflow, bro)
        // if (!$this->isValidWebhook($payload, $signature, $callbackUrl)) {
        //     Log::warning('Invalid webhook signature received', [
        //         'payload' => $payload,
        //         'signature' => $signature,
        //     ]);

        //     return response()->json(['error' => 'Invalid signature'], 403);
        // }

        // Optional: Verify ik-sign here (we’ll do this in next step if you want)
        // if ($this->isValidWebhook($payload, $signature, $callbackUrl)) {
        // Do your webhook logic here
        $data = json_decode($payload, true);
        Log::warning('WebHook Recieved Successfully');

        // 2. Validate the required fields: paylinkID, status, etc.
        // Basic validation
        if (!isset($data['status']) || !isset($data['externalTransactionID'])) {
            Log::warning('Webhook missing expected fields');
            return response()->json(['error' => 'Missing required data'], 422);
        }

        $payment = IkhokhaPayment::where('transaction_id', $data['externalTransactionID'])->first();

        // check DB for that/this specific order payment instance
        if (!$payment) {
            Log::error('Payment not found for transaction: ' . $data['externalTransactionID']);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // check for duplicate webhook process
        if (in_array(needle: $payment->status, haystack: ['completed', 'failed'])) {
            Log::info('Duplicate webhook ignored: already finalized');
            return response()->json(['message' => 'Already handled'], 200);
        }

        $status = strtoupper($data['status']); // keep it upper for consistency

        // switch ($status) {
        //     case 'SUCCESS':
        //         $payment->status = 'completed';
        //         $payment->paid_at = now();
        //         $payment->webhook_received_at = now() ?? null;
        //         $payment->webhook_signature = $signature ?? null;
        //         $payment->ik_app_id = $appId ?? null;
        //         break;
        //     case 'FAILURE':
        //         $payment->status = 'failed';
        //         $payment->webhook_received_at = now() ?? null;
        //         $payment->webhook_signature = $signature ?? null;
        //         $payment->ik_app_id = $appId ?? null;
        //         break;
        //     case 'PENDING':
        //         $payment->status = 'pending';
        //         break;
        //     default:
        //         $payment->status = 'unknown';
        // }
        // $payment->save();

        $newStatus = match ($status) {
            'SUCCESS' => 'completed',
            'FAILURE' => 'failed',
            default => 'unknown'
        };
        $payment->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'completed' ? now() : null,
            'webhook_received_at' => now() ?? null,
            'webhook_signature' => $signature ?? null,
            'ik_app_id' => $appId ?? null,
        ]);

        Log::info('Webhook processed successfully', [
            'transaction_id' => $payment->transaction_id,
            'new_status' => $newStatus,
        ]);

        return response()->json(data: ['message' => 'Webhook handled successfully'], status: 200);

        // }
    }

    public function isValidWebhook($payload, $signatureHeader, $callbackUrl) // a helper method
    {
        $appSecret = config('ikhokha.app_secret');

        // Rebuild what iKhokha hashed
        $dataToHash = json_encode($payload, JSON_UNESCAPED_SLASHES) . $callbackUrl;

        // Generate HMAC using SHA1
        $generatedSignature = hash_hmac('sha1', $dataToHash, $appSecret);

        if (app()->environment('local')) {
            return true;
        }

        return hash_equals($generatedSignature, $signatureHeader);
    }



    public function success()
    {
        // return view('ikhokha.success'); // make Blade view or simple msg
        return view('ikhokha::success');
    }

    public function failed()
    {
        // return view('ikhokha.failed');
        return view('ikhokha::failed');
    }

    public function cancel()
    {
        // return view('ikhokha.cancel');
        return view('ikhokha::cancel');
    }

}
