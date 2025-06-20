<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\MonitorHistory;
use Carbon\Carbon;

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
            
            // Store historical data
            $this->storeHistoricalData($monitor);
        }

        return response()->json($monitors);
    }
    
    public function history()
    {
        $weeklyStats = MonitorHistory::getWeeklyStats();
        $todayIncidents = MonitorHistory::getTodayIncidents();
        
        // Generate last 7 days data
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            
            $dayStats = $weeklyStats->firstWhere('date', $dateStr);
            
            $last7Days[] = [
                'date' => $dateStr,
                'formatted_date' => $date->format('M d'),
                'day_name' => $date->format('D'),
                'down_count' => $dayStats ? $dayStats->down_count : 0,
                'total_checks' => $dayStats ? $dayStats->total_checks : 0,
                'avg_uptime' => $dayStats ? round($dayStats->avg_uptime, 2) : 100,
                'is_today' => $date->isToday()
            ];
        }
        
        return response()->json([
            'weekly_stats' => $last7Days,
            'today_incidents' => $todayIncidents,
            'summary' => [
                'total_incidents_week' => $weeklyStats->sum('down_count'),
                'avg_uptime_week' => round($weeklyStats->avg('avg_uptime'), 2),
                'worst_day' => $weeklyStats->sortByDesc('down_count')->first()
            ]
        ]);
    }
    
    private function storeHistoricalData($monitor)
    {
        // Only store data every 5 minutes to avoid too much data
        $lastRecord = MonitorHistory::where('monitor_id', $monitor['id'])
            ->where('checked_at', '>', Carbon::now()->subMinutes(5))
            ->first();
            
        if (!$lastRecord) {
            MonitorHistory::create([
                'monitor_id' => $monitor['id'],
                'monitor_name' => $monitor['friendly_name'],
                'status' => $monitor['status'],
                'uptime_ratio' => floatval(explode('-', $monitor['custom_uptime_ratio'] ?? '100')[0]),
                'checked_at' => Carbon::now()
            ]);
        }
    }
}