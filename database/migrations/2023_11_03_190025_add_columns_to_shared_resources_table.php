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
        Schema::table('shared_resources', function (Blueprint $table) {
            $table->boolean('show_total')->default(true);
            $table->boolean('show_timeframe')->default(true);
            $table->boolean('show_units')->default(true);
            $table->boolean('show_time')->default(true);
            $table->boolean('show_date')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shared_resources', function (Blueprint $table) {
            //
        });
    }
};
