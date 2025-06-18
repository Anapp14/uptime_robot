<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonitorHistory extends Model
{
    protected $table = 'monitor_history';
    
    protected $fillable = [
        'monitor_id',
        'monitor_name',
        'status',
        'uptime_ratio',
        'checked_at'
    ];
    
    protected $casts = [
        'checked_at' => 'datetime',
        'uptime_ratio' => 'decimal:2'
    ];
    
    public static function getWeeklyStats()
    {
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        
        return self::where('checked_at', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(checked_at) as date')
            ->selectRaw('COUNT(CASE WHEN status IN (8, 9) THEN 1 END) as down_count')
            ->selectRaw('COUNT(*) as total_checks')
            ->selectRaw('AVG(uptime_ratio) as avg_uptime')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    
    public static function getTodayIncidents()
    {
        return self::where('checked_at', '>=', Carbon::today())
            ->where('status', 'IN', [8, 9])
            ->selectRaw('monitor_name, COUNT(*) as incident_count')
            ->groupBy('monitor_name')
            ->orderByDesc('incident_count')
            ->get();
    }
}