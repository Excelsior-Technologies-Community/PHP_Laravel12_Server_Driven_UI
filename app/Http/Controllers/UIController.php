<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\UIComponent;
use App\Models\UIExperiment;
use App\Models\UITelemetry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UIController extends Controller
{
    /**
     * API: Get Server-Driven UI Components with Platform & A/B Experiment Resolution
     */
    public function getComponents(Request $request, $screen)
    {
        // 1. Resolve Client Platform & App Version
        $platform = strtolower($request->header('X-Platform') ?? $request->query('platform') ?? 'web');
        $clientVersion = $request->header('X-App-Version') ?? $request->query('app_version');

        // 2. Resolve Visitor ID for deterministic sticky A/B bucket allocation
        $visitorId = $request->header('X-Visitor-ID') 
            ?? $request->query('visitor_id') 
            ?? $request->cookie('sdui_visitor_id') 
            ?? 'vis_' . substr(md5($request->ip() . ($request->userAgent() ?? '') . date('Ymd')), 0, 10);

        // 3. Query Active Components for this Screen
        $query = UIComponent::where('screen', $screen)
            ->where('is_active', true)
            ->orderBy('order');

        // Apply Platform Filtering
        if ($platform && $platform !== 'all') {
            $query->forPlatform($platform);
        }

        $allComponents = $query->get();

        // 4. Apply App Version Gating
        if ($clientVersion) {
            $allComponents = $allComponents->filter(function ($comp) use ($clientVersion) {
                if (!empty($comp->min_version) && version_compare($clientVersion, $comp->min_version, '<')) {
                    return false;
                }
                if (!empty($comp->max_version) && version_compare($clientVersion, $comp->max_version, '>')) {
                    return false;
                }
                return true;
            })->values();
        }

        // 5. A/B Testing Resolution
        $activeExperiments = UIExperiment::where('screen', $screen)
            ->where('status', 'active')
            ->get();

        $experimentMetadata = [];

        $transformedComponents = $allComponents->map(function ($component) use ($activeExperiments, $visitorId, &$experimentMetadata, $screen, $platform) {
            $variant = 'control';
            $properties = $component->properties;
            $appliedExperiment = null;

            foreach ($activeExperiments as $exp) {
                if ($exp->target_component_id == $component->id) {
                    $isAssigned = $exp->shouldAssignVariant($visitorId);
                    if ($isAssigned) {
                        $variant = 'variant_b';
                        $properties = $exp->variant_properties;
                        $appliedExperiment = [
                            'experiment_id' => $exp->id,
                            'experiment_name' => $exp->name,
                            'variant_name' => $exp->variant_name,
                            'traffic_percent' => $exp->traffic_percent,
                        ];
                        $experimentMetadata[] = $appliedExperiment;
                    } else {
                        $appliedExperiment = [
                            'experiment_id' => $exp->id,
                            'experiment_name' => $exp->name,
                            'variant_name' => 'Control',
                            'traffic_percent' => $exp->traffic_percent,
                        ];
                    }
                    break;
                }
            }

            // 6. Automatically log Telemetry Impression for served components
            UITelemetry::create([
                'component_id' => $component->id,
                'screen' => $screen,
                'event_type' => 'impression',
                'platform' => $platform,
                'variant' => $variant,
                'visitor_id' => $visitorId,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return [
                'id' => $component->id,
                'type' => $component->type,
                'name' => $component->name,
                'properties' => $properties,
                'screen' => $component->screen,
                'order' => $component->order,
                'platforms' => $component->platforms ?? ['all'],
                'min_version' => $component->min_version,
                'variant' => $variant,
                'experiment' => $appliedExperiment,
            ];
        });

        return response()->json([
            'success' => true,
            'screen' => $screen,
            'client_meta' => [
                'platform' => $platform,
                'app_version' => $clientVersion ?? 'latest',
                'visitor_id' => $visitorId,
                'active_experiments_count' => count($experimentMetadata),
                'experiments' => $experimentMetadata,
            ],
            'components' => $transformedComponents,
        ])->cookie('sdui_visitor_id', $visitorId, 60 * 24 * 30);
    }

    /**
     * API: Get Screens
     */
    public function getScreens()
    {
        return response()->json([
            'success' => true,
            'screens' => UIComponent::distinct()->pluck('screen'),
        ]);
    }

    /**
     * Demo Screen
     */
    public function demo(Request $request, $screen = 'home')
    {
        $platform = $request->query('platform', 'web');
        $version = $request->query('app_version', '2.5.0');
        $visitorId = $request->query('visitor_id', 'vis_tester_' . rand(100, 999));

        return view('demo', compact('screen', 'platform', 'version', 'visitorId'));
    }

    /**
     * Admin Dashboard with Search, Filter, Pagination, and CTR Heat Indicators
     */
    public function admin(Request $request)
    {
        $query = UIComponent::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('screen', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Filter Screen
        if ($request->filled('screen')) {
            $query->where('screen', $request->screen);
        }

        // Filter Platform
        if ($request->filled('platform') && $request->platform !== 'all') {
            $query->forPlatform($request->platform);
        }

        // Filter Status
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $components = $query->orderBy('screen')->orderBy('order')->paginate(6)->withQueryString();

        // Attach real-time CTR heat status to each component
        foreach ($components as $comp) {
            $imp = UITelemetry::where('component_id', $comp->id)->where('event_type', 'impression')->count();
            $clk = UITelemetry::where('component_id', $comp->id)->where('event_type', 'click')->count();
            $comp->metrics_impressions = $imp;
            $comp->metrics_clicks = $clk;
            $comp->metrics_ctr = $imp > 0 ? round(($clk / $imp) * 100, 1) : 0;
        }

        // Statistics
        $statistics = [
            'total' => UIComponent::count(),
            'active' => UIComponent::where('is_active', 1)->count(),
            'inactive' => UIComponent::where('is_active', 0)->count(),
            'screens' => UIComponent::distinct('screen')->count(),
            'experiments' => UIExperiment::where('status', 'active')->count(),
            'telemetry_events' => UITelemetry::count(),
        ];

        // Chart Data
        $chartData = [
            'headers' => UIComponent::where('type', 'header')->count(),
            'cards' => UIComponent::where('type', 'card')->count(),
            'buttons' => UIComponent::where('type', 'button')->count(),
            'forms' => UIComponent::where('type', 'form')->count(),
        ];

        return view('admin', compact(
            'components',
            'statistics',
            'chartData'
        ));
    }

    /**
     * Create Component with Platform and Version Support
     */
    public function createComponent(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'name' => 'required|string',
            'screen' => 'required|string',
            'properties' => 'required',
        ]);

        $properties = is_array($request->properties) ? $request->properties : json_decode($request->properties, true);
        $platforms = $request->platforms ? (is_array($request->platforms) ? $request->platforms : json_decode($request->platforms, true)) : ['all'];

        $component = UIComponent::create([
            'type' => $request->type,
            'name' => $request->name,
            'screen' => $request->screen,
            'properties' => $properties,
            'platforms' => $platforms,
            'min_version' => $request->min_version,
            'max_version' => $request->max_version,
            'order' => (UIComponent::where('screen', $request->screen)->max('order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Component Created Successfully.',
            'component' => $component,
        ]);
    }

    /**
     * Toggle Status
     */
    public function toggleComponent($id)
    {
        $component = UIComponent::findOrFail($id);
        $component->is_active = !$component->is_active;
        $component->save();

        return response()->json([
            'success' => true,
            'message' => 'Status Updated Successfully.',
            'is_active' => $component->is_active,
        ]);
    }

    /**
     * Preview Component
     */
    public function previewComponent($id)
    {
        $component = UIComponent::findOrFail($id);

        return response()->json([
            'success' => true,
            'component' => [
                'id' => $component->id,
                'type' => $component->type,
                'name' => $component->name,
                'screen' => $component->screen,
                'properties' => $component->properties,
                'platforms' => $component->platforms ?? ['all'],
                'min_version' => $component->min_version,
                'order' => $component->order,
                'is_active' => $component->is_active,
            ]
        ]);
    }

    /**
     * Duplicate Component
     */
    public function duplicateComponent($id)
    {
        $component = UIComponent::findOrFail($id);

        $duplicate = UIComponent::create([
            'type' => $component->type,
            'name' => $component->name . ' Copy',
            'properties' => $component->properties,
            'platforms' => $component->platforms,
            'min_version' => $component->min_version,
            'max_version' => $component->max_version,
            'screen' => $component->screen,
            'order' => (UIComponent::where('screen', $component->screen)->max('order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Component duplicated successfully.',
            'component' => $duplicate,
        ]);
    }

    /**
     * Export Components CSV with Platforms & Version Rules
     */
    public function exportCSV()
    {
        $components = UIComponent::all();

        return new StreamedResponse(
            function () use ($components) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'ID',
                    'Type',
                    'Name',
                    'Screen',
                    'Platforms',
                    'Min Version',
                    'Properties',
                    'Status',
                    'Created Date',
                ]);

                foreach ($components as $component) {
                    fputcsv($handle, [
                        $component->id,
                        $component->type,
                        $component->name,
                        $component->screen,
                        implode(', ', $component->platforms ?? ['all']),
                        $component->min_version ?? 'Any',
                        json_encode($component->properties),
                        $component->is_active ? 'Active' : 'Inactive',
                        $component->created_at,
                    ]);
                }

                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="ui_components.csv"',
            ]
        );
    }

    /**
     * Delete Component
     */
    public function deleteComponent($id)
    {
        $component = UIComponent::findOrFail($id);
        $component->delete();

        return response()->json([
            'success' => true,
            'message' => 'Component deleted successfully'
        ]);
    }
}
