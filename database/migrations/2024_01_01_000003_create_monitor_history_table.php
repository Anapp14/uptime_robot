<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monitor_history', function (Blueprint $table) {
            $table->id();
            $table->string('monitor_id');
            $table->string('monitor_name');
            $table->integer('status'); // 2=up, 8/9=down, 0=paused
            $table->decimal('uptime_ratio', 5, 2)->default(0);
            $table->timestamp('checked_at');
            $table->timestamps();
            
            $table->index(['monitor_id', 'checked_at']);
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_history');
    }
};