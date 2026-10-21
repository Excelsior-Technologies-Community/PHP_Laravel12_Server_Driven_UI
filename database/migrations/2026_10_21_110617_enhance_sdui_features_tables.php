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
        Schema::table('ui_components', function (Blueprint $table) {
            $table->json('platforms')->nullable()->after('properties');
            $table->string('min_version', 32)->nullable()->after('platforms');
            $table->string('max_version', 32)->nullable()->after('min_version');
            $table->string('variant', 32)->default('control')->after('is_active');
            $table->unsignedBigInteger('experiment_id')->nullable()->after('variant');
        });

        Schema::create('ui_experiments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('screen');
            $table->unsignedBigInteger('target_component_id')->nullable();
            $table->integer('traffic_percent')->default(50); // 0-100%
            $table->string('variant_name')->default('Variant B');
            $table->json('variant_properties');
            $table->string('status', 32)->default('active'); // active, paused, ended
            $table->string('goal_action', 64)->default('click'); // click, submit
            $table->timestamps();
        });

        Schema::create('ui_telemetry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id')->nullable()->index();
            $table->string('screen', 64)->index();
            $table->string('event_type', 32)->index(); // impression, click
            $table->string('platform', 32)->default('web')->index(); // web, ios, android
            $table->string('variant', 32)->default('control');
            $table->string('visitor_id', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ui_telemetry');
        Schema::dropIfExists('ui_experiments');

        Schema::table('ui_components', function (Blueprint $table) {
            $table->dropColumn(['platforms', 'min_version', 'max_version', 'variant', 'experiment_id']);
        });
    }
};
