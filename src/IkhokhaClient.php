<?php

namespace Elmmac\Ikhokha;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

class IkhokhaClient
{
    protected string $baseUrl;
    protected string $entityId;
    protected string $clientMode;
    protected string $currency;

    public function __construct()
    {
        $this->baseUrl = config('ikhokha.base_url');
        $this->entityId = config('ikhokha.entity_id');
        $this->clientMode = config('ikhokha.mode');
        $this->currency = config('ikhokha.currency');
    }

    // Create payment link method
    public function createPaymentLink(array $data)
    {
        $client = new \GuzzleHttp\Client();
        $endpoint = $this->baseUrl;

        try {
            $response = $client->post(
                $endpoint,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => $data
                ]
            );
            $body = json_decode($response->getBody(), true);
            Log::info('iKhokha payment link created successfully', $body);

            return $body;
        } catch (RequestException $e) {
            Log::error('iKhokha createPaymentLink error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            return ['error' => 'Request failed and no response was returned.'];
        }
    }
}
