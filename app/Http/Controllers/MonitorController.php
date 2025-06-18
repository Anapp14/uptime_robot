<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class MonitorController extends Controller
{
    public function index()
    {
        return view('monitor');
    }

    public function data()
    {
        $apiKey = env('UPTIMEROBOT_API_KEY');

        $response = Http::post('https://api.uptimerobot.com/v2/getMonitors', [
            'api_key' => $apiKey,
            'format' => 'json',
            'logs' => 1,
            'all_time_uptime_ratio' => 1,
            'custom_uptime_ratios' => '1', // uptime 1 hari (24 jam)
        ]);

        $monitors = $response->json()['monitors'] ?? [];

        // Ambil waktu sekarang dan hitung 24 jam ke belakang
        $now = time();
        $last24h = $now - (24 * 60 * 60);

        foreach ($monitors as &$monitor) {
            if (isset($monitor['logs'])) {
                // Filter log yang hanya terjadi dalam 24 jam terakhir
                $monitor['logs'] = array_filter($monitor['logs'], function ($log) use ($last24h) {
                    return $log['datetime'] >= $last24h;
                });

                // Ambil maksimal 10 log terakhir dari yang difilter
                $monitor['logs'] = array_slice($monitor['logs'], 0, 10);
            }
        }

        return response()->json($monitors);
    }
}
