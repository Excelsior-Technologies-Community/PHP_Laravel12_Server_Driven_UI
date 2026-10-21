<?php

namespace App\Http\Controllers;

use App\Models\UIComponent;
use App\Models\UIExperiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UIStudioController extends Controller
{
    /**
     * Display Visual Drag-and-Drop Canvas & Multi-Device Simulator
     */
    public function index(Request $request)
    {
        $screen = $request->query('screen', 'home');
        $platform = $request->query('platform', 'all');
        $deviceFrame = $request->query('device', 'iphone'); // iphone, pixel, desktop

        $components = UIComponent::where('screen', $screen)
            ->when($platform !== 'all', function ($q) use ($platform) {
                $q->forPlatform($platform);
            })
            ->orderBy('order')
            ->get();

        $screens = UIComponent::distinct()->pluck('screen')->toArray();
        $activeExperiments = UIExperiment::where('screen', $screen)->where('status', 'active')->count();

        return view('admin.studio', compact(
            'components',
            'screen',
            'platform',
            'deviceFrame',
            'screens',
            'activeExperiments'
        ));
    }

    /**
     * API: Save Drag-and-Drop Reordered Components
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:ui_components,id',
            'items.*.order' => 'required|integer',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                UIComponent::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Component layout order persisted successfully!',
        ]);
    }

    /**
     * API: Update Component via Visual Property Inspector
     */
    public function updateProperties(Request $request, $id)
    {
        $component = UIComponent::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'properties' => 'sometimes|array',
            'platforms' => 'nullable|array',
            'min_version' => 'nullable|string',
            'max_version' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->has('name')) {
            $component->name = $request->name;
        }

        if ($request->has('properties')) {
            $component->properties = $request->properties;
        }

        if ($request->has('platforms')) {
            $component->platforms = $request->platforms;
        }

        if ($request->has('min_version')) {
            $component->min_version = $request->min_version;
        }

        if ($request->has('max_version')) {
            $component->max_version = $request->max_version;
        }

        if ($request->has('is_active')) {
            $component->is_active = (bool) $request->is_active;
        }

        $component->save();

        return response()->json([
            'success' => true,
            'message' => 'Properties updated successfully.',
            'component' => $component,
        ]);
    }
}
