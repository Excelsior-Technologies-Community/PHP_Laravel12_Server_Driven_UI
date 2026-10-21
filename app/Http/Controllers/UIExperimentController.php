<?php

namespace App\Http\Controllers;

use App\Models\UIComponent;
use App\Models\UIExperiment;
use App\Models\UITelemetry;
use Illuminate\Http\Request;

class UIExperimentController extends Controller
{
    /**
     * A/B Testing Management & Experimentation Studio
     */
    public function index()
    {
        $experiments = UIExperiment::with('targetComponent')->latest()->get()->map(function ($exp) {
            $targetId = $exp->target_component_id;

            // Telemetry for Control
            $controlImpressions = UITelemetry::where('component_id', $targetId)
                ->where('variant', 'control')
                ->where('event_type', 'impression')
                ->count();
            $controlClicks = UITelemetry::where('component_id', $targetId)
                ->where('variant', 'control')
                ->where('event_type', 'click')
                ->count();
            $controlCtr = $controlImpressions > 0 ? round(($controlClicks / $controlImpressions) * 100, 2) : 0;

            // Telemetry for Variant B
            $variantImpressions = UITelemetry::where('component_id', $targetId)
                ->where('variant', 'variant_b')
                ->where('event_type', 'impression')
                ->count();
            $variantClicks = UITelemetry::where('component_id', $targetId)
                ->where('variant', 'variant_b')
                ->where('event_type', 'click')
                ->count();
            $variantCtr = $variantImpressions > 0 ? round(($variantClicks / $variantImpressions) * 100, 2) : 0;

            // Lift Calculation
            $lift = $controlCtr > 0 ? round((($variantCtr - $controlCtr) / $controlCtr) * 100, 1) : 0;

            return [
                'id' => $exp->id,
                'name' => $exp->name,
                'screen' => $exp->screen,
                'status' => $exp->status,
                'traffic_percent' => $exp->traffic_percent,
                'variant_name' => $exp->variant_name,
                'target_component' => $exp->targetComponent,
                'variant_properties' => $exp->variant_properties,
                'control' => [
                    'impressions' => $controlImpressions,
                    'clicks' => $controlClicks,
                    'ctr' => $controlCtr,
                ],
                'variant_b' => [
                    'impressions' => $variantImpressions,
                    'clicks' => $variantClicks,
                    'ctr' => $variantCtr,
                ],
                'lift' => $lift,
            ];
        });

        $components = UIComponent::where('is_active', true)->get();
        $screens = UIComponent::distinct()->pluck('screen')->toArray();

        return view('admin.experiments', compact('experiments', 'components', 'screens'));
    }

    /**
     * Create New A/B Experiment
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'screen' => 'required|string',
            'target_component_id' => 'required|exists:ui_components,id',
            'traffic_percent' => 'required|integer|min:1|max:99',
            'variant_name' => 'required|string|max:255',
            'variant_properties' => 'required|string',
        ]);

        $properties = json_decode($request->variant_properties, true);
        if (!$properties) {
            return redirect()->back()->with('error', 'Invalid JSON in Variant Properties.');
        }

        UIExperiment::create([
            'name' => $request->name,
            'screen' => $request->screen,
            'target_component_id' => $request->target_component_id,
            'traffic_percent' => $request->traffic_percent,
            'variant_name' => $request->variant_name,
            'variant_properties' => $properties,
            'status' => 'active',
            'goal_action' => 'click',
        ]);

        return redirect()->route('admin.experiments')->with('success', 'A/B Experiment launched successfully!');
    }

    /**
     * Toggle Experiment Status (Active <-> Paused)
     */
    public function toggle($id)
    {
        $exp = UIExperiment::findOrFail($id);
        $exp->status = $exp->status === 'active' ? 'paused' : 'active';
        $exp->save();

        return redirect()->back()->with('success', "Experiment {$exp->name} is now {$exp->status}.");
    }

    /**
     * Delete Experiment
     */
    public function destroy($id)
    {
        $exp = UIExperiment::findOrFail($id);
        $exp->delete();

        return redirect()->back()->with('success', 'Experiment removed.');
    }
}
