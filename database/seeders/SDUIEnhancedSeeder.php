<?php

namespace Database\Seeders;

use App\Models\UIComponent;
use App\Models\UIExperiment;
use App\Models\UITelemetry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SDUIEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        UIComponent::query()->delete();
        UIExperiment::query()->delete();
        UITelemetry::query()->delete();

        // 1. Seed UI Components with Platform & Version rules
        $components = [
            // Home screen
            [
                'type' => 'header',
                'name' => 'Main Hero Header',
                'screen' => 'home',
                'properties' => [
                    'title' => '⚡ Server-Driven UI Architecture',
                    'subtitle' => 'Zero-deployment frontend driven directly by Laravel 12 APIs',
                    'style' => 'text-align: center; color: #0d6efd;',
                ],
                'platforms' => ['all'],
                'min_version' => null,
                'max_version' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Home Promotion Banner',
                'screen' => 'home',
                'properties' => [
                    'title' => '🎉 Exclusive 30% Off Upgrade',
                    'content' => 'Unlock premium cloud telemetry and live multi-device visualizer today.',
                    'button_text' => 'Claim Offer Now',
                    'badge' => 'LIMITED TIME',
                    'theme' => 'primary',
                ],
                'platforms' => ['all'],
                'min_version' => '1.0.0',
                'max_version' => null,
                'order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Mobile Quick Pay Card',
                'screen' => 'home',
                'properties' => [
                    'title' => '📱 Apple Pay & Google Wallet Ready',
                    'content' => 'One-tap biometric checkout enabled for native mobile apps.',
                    'button_text' => 'Pay in 1-Click',
                    'badge' => 'MOBILE ONLY',
                    'theme' => 'dark',
                ],
                'platforms' => ['ios', 'android'],
                'min_version' => '2.0.0',
                'max_version' => null,
                'order' => 3,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Desktop Wide Showcase',
                'screen' => 'home',
                'properties' => [
                    'title' => '💻 Desktop Enterprise Analytics Suite',
                    'content' => 'High-resolution multi-monitor telemetry graphs and raw JSON schema inspector.',
                    'button_text' => 'Launch Desktop Suite',
                    'badge' => 'WEB ONLY',
                    'theme' => 'info',
                ],
                'platforms' => ['web'],
                'min_version' => null,
                'max_version' => null,
                'order' => 4,
                'is_active' => true,
            ],
            [
                'type' => 'button',
                'name' => 'Primary CTA Button',
                'screen' => 'home',
                'properties' => [
                    'text' => 'Explore Live Documentation →',
                    'variant' => 'btn-primary',
                    'size' => 'btn-lg',
                ],
                'platforms' => ['all'],
                'min_version' => null,
                'max_version' => null,
                'order' => 5,
                'is_active' => true,
            ],

            // Dashboard screen
            [
                'type' => 'header',
                'name' => 'Dashboard Overview Header',
                'screen' => 'dashboard',
                'properties' => [
                    'title' => '📊 Production Telemetry & Observability',
                    'subtitle' => 'Real-time throughput and component conversion metrics',
                ],
                'platforms' => ['all'],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Weekly Conversion KPI Card',
                'screen' => 'dashboard',
                'properties' => [
                    'title' => 'Active Mobile DAU',
                    'content' => '42,850 active daily app sessions streaming server-driven schemas.',
                    'button_text' => 'View Traffic Heatmap',
                    'theme' => 'success',
                ],
                'platforms' => ['all'],
                'order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Android Push Gateway Notice',
                'screen' => 'dashboard',
                'properties' => [
                    'title' => '🤖 FCM Android Background Sync',
                    'content' => 'Instant silent push triggers component cache invalidation without user reload.',
                    'button_text' => 'Configure FCM',
                    'badge' => 'ANDROID ONLY',
                ],
                'platforms' => ['android'],
                'order' => 3,
                'is_active' => true,
            ],

            // Profile screen
            [
                'type' => 'header',
                'name' => 'Profile Header',
                'screen' => 'profile',
                'properties' => [
                    'title' => '👤 User Account & Preferences',
                    'subtitle' => 'Manage identity, push preferences, and device bindings',
                ],
                'platforms' => ['all'],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'form',
                'name' => 'Profile Update Form',
                'screen' => 'profile',
                'properties' => [
                    'title' => 'Personal Information',
                    'fields' => [
                        ['label' => 'Full Name', 'type' => 'text', 'placeholder' => 'Enter your full name'],
                        ['label' => 'Work Email', 'type' => 'email', 'placeholder' => 'name@company.com'],
                        ['label' => 'Default Device Platform', 'type' => 'text', 'placeholder' => 'iOS / Android / Web'],
                    ],
                ],
                'platforms' => ['all'],
                'order' => 2,
                'is_active' => true,
            ],

            // Settings screen
            [
                'type' => 'header',
                'name' => 'Settings Header',
                'screen' => 'settings',
                'properties' => [
                    'title' => '⚙️ Application Settings',
                    'subtitle' => 'Adjust theme, caching, and experiment participation',
                ],
                'platforms' => ['all'],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Theme Customizer',
                'screen' => 'settings',
                'properties' => [
                    'title' => 'Appearance & Dark Mode',
                    'content' => 'Server sends adaptive theme variables based on client system settings.',
                    'button_text' => 'Toggle Dark Palette',
                ],
                'platforms' => ['all'],
                'order' => 2,
                'is_active' => true,
            ],
        ];

        $createdComponents = [];
        foreach ($components as $compData) {
            $createdComponents[] = UIComponent::create($compData);
        }

        // 2. Seed an A/B Experiment on the Home Promotion Banner (Component #2)
        $targetComp = UIComponent::where('name', 'Home Promotion Banner')->first();
        if ($targetComp) {
            $exp = UIExperiment::create([
                'name' => 'Spring Sale: 30% Off vs 50% Flash Deal CTA',
                'screen' => 'home',
                'target_component_id' => $targetComp->id,
                'traffic_percent' => 50,
                'variant_name' => 'Variant B (50% Flash Urgency)',
                'variant_properties' => [
                    'title' => '🔥 FLASH SALE: 50% OFF Limited Drop!',
                    'content' => '⚡ Only 14 licenses remaining at this discount. Offer expires tonight at midnight!',
                    'button_text' => 'Claim 50% Flash Discount ➔',
                    'badge' => 'URGENT 50% OFF',
                    'theme' => 'danger',
                ],
                'status' => 'active',
                'goal_action' => 'click',
            ]);
        }

        // 3. Seed Realistic Telemetry Impressions and Clicks
        $platforms = ['web', 'ios', 'android'];
        $variants = ['control', 'variant_b'];

        foreach ($createdComponents as $c) {
            // Seed 40 to 120 impressions per component
            $impCount = rand(45, 120);
            // Some components have high CTR (25%), some moderate (10%), some low (2%)
            $ctrRate = match($c->type) {
                'card' => rand(12, 28) / 100,
                'button' => rand(18, 35) / 100,
                'form' => rand(8, 16) / 100,
                default => rand(2, 6) / 100,
            };
            $clickCount = (int) round($impCount * $ctrRate);

            for ($i = 0; $i < $impCount; $i++) {
                $plat = $platforms[array_rand($platforms)];
                $var = $variants[array_rand($variants)];
                UITelemetry::create([
                    'component_id' => $c->id,
                    'screen' => $c->screen,
                    'event_type' => 'impression',
                    'platform' => $plat,
                    'variant' => $var,
                    'visitor_id' => 'vis_' . substr(md5($i . $c->id), 0, 8),
                    'ip_address' => '192.168.1.' . rand(10, 200),
                    'created_at' => now()->subMinutes(rand(1, 1440)),
                ]);
            }

            for ($j = 0; $j < $clickCount; $j++) {
                $plat = $platforms[array_rand($platforms)];
                $var = $variants[array_rand($variants)];
                UITelemetry::create([
                    'component_id' => $c->id,
                    'screen' => $c->screen,
                    'event_type' => 'click',
                    'platform' => $plat,
                    'variant' => $var,
                    'visitor_id' => 'vis_' . substr(md5($j . $c->id . 'clk'), 0, 8),
                    'ip_address' => '192.168.1.' . rand(10, 200),
                    'created_at' => now()->subMinutes(rand(1, 1440)),
                ]);
            }
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
