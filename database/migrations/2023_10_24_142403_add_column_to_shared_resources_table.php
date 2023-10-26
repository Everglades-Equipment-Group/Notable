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
            $table->string('sort_by')->default('created_at');
            $table->string('sort_direction')->default('asc');
            $table->string('input_at')->default('top');
            $table->boolean('show_deletes')->default(true);
            $table->boolean('show_checks')->default(true);
            $table->boolean('move_checked')->default(false);
            $table->boolean('show_item_info')->default(false);
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
