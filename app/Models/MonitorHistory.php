<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            ->select(DB::raw('DATE(checked_at) as date'))
            ->selectRaw('COUNT(CASE WHEN status IN (8, 9) THEN 1 END) as down_count')
            ->selectRaw('COUNT(*) as total_checks')
            ->selectRaw('AVG(uptime_ratio) as avg_uptime')
            ->groupBy(DB::raw('DATE(checked_at)'))
            ->orderBy('date')
            ->get();
    }
    
    public static function getTodayIncidents()
    {
        return self::where('checked_at', '>=', Carbon::today())
            ->whereIn('status', [8, 9])
            ->select('monitor_name')
            ->selectRaw('COUNT(*) as incident_count')
            ->groupBy('monitor_name')
            ->orderByDesc('incident_count')
            ->get();
    }
}