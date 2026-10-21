<?php

namespace App\Http\Controllers;

use App\Models\UIComponent;
use App\Models\UITelemetry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UITelemetryController extends Controller
{
    /**
     * Display Analytics Radar & Component CTR Heatmap Dashboard
     */
    public function index(Request $request)
    {
        $selectedScreen = $request->query('screen', 'all');

        $query = UITelemetry::query();
        if ($selectedScreen !== 'all') {
            $query->where('screen', $selectedScreen);
        }

        $totalImpressions = (clone $query)->where('event_type', 'impression')->count();
        $totalClicks = (clone $query)->where('event_type', 'click')->count();
        $overallCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;

        // Platform breakdown
        $platformStats = UITelemetry::select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        // Components CTR analysis
        $components = UIComponent::orderBy('screen')->orderBy('order')->get()->map(function ($comp) {
            $impressions = UITelemetry::where('component_id', $comp->id)
                ->where('event_type', 'impression')
                ->count();
            $clicks = UITelemetry::where('component_id', $comp->id)
                ->where('event_type', 'click')
                ->count();
            $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

            // Heat status
            if ($ctr >= 15) {
                $heat = 'high';
                $heatBadge = 'success';
                $heatLabel = 'High Engagement (≥15%)';
            } elseif ($ctr >= 5) {
                $heat = 'medium';
                $heatBadge = 'warning';
                $heatLabel = 'Moderate Engagement (5-15%)';
            } else {
                $heat = 'low';
                $heatBadge = 'danger';
                $heatLabel = 'Low / Zero Engagement (<5%)';
            }

            return [
                'id' => $comp->id,
                'name' => $comp->name,
                'type' => $comp->type,
                'screen' => $comp->screen,
                'platforms' => $comp->platforms ?? ['all'],
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $ctr,
                'heat' => $heat,
                'heatBadge' => $heatBadge,
                'heatLabel' => $heatLabel,
            ];
        });

        // Top performing and lowest performing components
        $sorted = $components->sortByDesc('ctr');
        $topComponent = $sorted->first();
        $lowestComponent = $sorted->where('impressions', '>', 0)->last();

        $screens = UIComponent::distinct()->pluck('screen')->toArray();

        return view('admin.telemetry', compact(
            'totalImpressions',
            'totalClicks',
            'overallCtr',
            'platformStats',
            'components',
            'topComponent',
            'lowestComponent',
            'screens',
            'selectedScreen'
        ));
    }

    /**
     * API: Record Component Impression
     */
    public function logImpression(Request $request)
    {
        $request->validate([
            'component_id' => 'required',
            'screen' => 'required|string',
        ]);

        $entry = UITelemetry::create([
            'component_id' => $request->component_id,
            'screen' => $request->screen,
            'event_type' => 'impression',
            'platform' => $request->platform ?? 'web',
            'variant' => $request->variant ?? 'control',
            'visitor_id' => $request->visitor_id ?? 'vis_anon',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Impression recorded',
            'id' => $entry->id,
        ]);
    }

    /**
     * API: Record Component Click / Interaction
     */
    public function logClick(Request $request)
    {
        $request->validate([
            'component_id' => 'required',
            'screen' => 'required|string',
        ]);

        $entry = UITelemetry::create([
            'component_id' => $request->component_id,
            'screen' => $request->screen,
            'event_type' => 'click',
            'platform' => $request->platform ?? 'web',
            'variant' => $request->variant ?? 'control',
            'visitor_id' => $request->visitor_id ?? 'vis_anon',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
            'id' => $entry->id,
        ]);
    }

    /**
     * Synthetic Telemetry Generator for Live Demonstrations
     */
    public function generateSynthetic(Request $request)
    {
        $components = UIComponent::all();
        if ($components->isEmpty()) {
            return redirect()->back()->with('error', 'No UI components available to generate traffic for.');
        }

        $platforms = ['web', 'ios', 'android'];
        $variants = ['control', 'variant_b'];
        $count = 150;

        for ($i = 0; $i < $count; $i++) {
            $comp = $components->random();
            $platform = $platforms[array_rand($platforms)];
            $variant = $variants[array_rand($variants)];
            $visId = 'vis_sim_' . rand(100, 999);

            UITelemetry::create([
                'component_id' => $comp->id,
                'screen' => $comp->screen,
                'event_type' => 'impression',
                'platform' => $platform,
                'variant' => $variant,
                'visitor_id' => $visId,
                'ip_address' => '10.0.0.' . rand(1, 254),
                'created_at' => now()->subMinutes(rand(1, 360)),
            ]);

            // 18% chance of click
            if (rand(1, 100) <= 18) {
                UITelemetry::create([
                    'component_id' => $comp->id,
                    'screen' => $comp->screen,
                    'event_type' => 'click',
                    'platform' => $platform,
                    'variant' => $variant,
                    'visitor_id' => $visId,
                    'ip_address' => '10.0.0.' . rand(1, 254),
                    'created_at' => now()->subMinutes(rand(1, 350)),
                ]);
            }
        }

        return redirect()->back()->with('success', "Generated 150+ realistic synthetic impressions and interactions across Web, iOS, and Android!");
    }
}
