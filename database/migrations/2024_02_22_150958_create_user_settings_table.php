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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('theme')->default('dark');
            $table->string('sort_notes_by')->default('created_at');
            $table->string('sort_notes_direction')->default('asc');
            $table->string('sort_items_by')->default('created_at');
            $table->string('sort_items_direction')->default('asc');
            $table->string('sort_events_by')->default('created_at');
            $table->string('sort_events_direction')->default('asc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
