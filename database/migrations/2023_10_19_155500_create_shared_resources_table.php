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
        Schema::create('shared_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('resource_id');
            $table->string('resource_type');
            $table->string('sort_by')->default('created_at');
            $table->string('sort_direction')->default('asc');
            $table->string('input_at')->default('top');
            $table->boolean('show_deletes')->default(true);
            $table->boolean('show_checks')->default(true);
            $table->boolean('move_checked')->default(false);
            $table->boolean('show_item_info')->default(false);
            $table->boolean('show_total')->default(true);
            $table->boolean('show_timeframe')->default(true);
            $table->boolean('show_units')->default(true);
            $table->boolean('show_time')->default(true);
            $table->boolean('show_date')->default(true);
            $table->boolean('can_sort')->default(true);
            $table->boolean('can_check')->default(true);
            $table->boolean('can_add')->default(true);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_resources');
    }
};
