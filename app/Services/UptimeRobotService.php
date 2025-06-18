<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UptimeRobotService
{
    protected $apiUrl = 'https://api.uptimerobot.com/v2/';

    public function getMonitors()
    {
        $response = Http::asForm()->post($this->apiUrl . 'getMonitors', [
            'api_key' => config('services.uptimerobot.api_key'),
            'format' => 'json'
        ]);

        return $response->json();
    }
}
