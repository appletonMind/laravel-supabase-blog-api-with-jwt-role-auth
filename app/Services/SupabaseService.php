<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected $url;
    protected $apiKey;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->apiKey = env('SUPABASE_API_KEY');
    }

    // Method to sign the URL of a private file
    public function getSignedUrl($filePath, $expiresIn = 3600)
    {
        // We make the request to obtain the signed URL
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->url}/storage/v1/object/sign/{$filePath}", [
            'expiresIn' => $expiresIn,
        ]);

        // We check if the response is successful
        if ($response->failed()) {
            throw new \Exception('Error signing the URL: ' . $response->body());
        }

        // We return the signed URL
        return $response->json()['signedURL'];
    }
}
