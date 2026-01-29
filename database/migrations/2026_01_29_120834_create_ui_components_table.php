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
        Schema::create('ui_components', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // header, card, button, form, etc.
            $table->string('name');
            $table->json('properties')->nullable(); // JSON for dynamic properties
            $table->string('screen'); // home, profile, dashboard
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Add index for better performance
            $table->index(['screen', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ui_components');
    }
};