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
            $table->string('access')->default('write');
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
