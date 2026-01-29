<?php

namespace App\Http\Controllers;

use App\Models\UIComponent;
use Illuminate\Http\Request;

class UIController extends Controller
{
    // API endpoint to get UI components for a screen
    public function getComponents($screen)
    {
        $components = UIComponent::where('screen', $screen)
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($component) {
                return [
                    'id' => $component->id,
                    'type' => $component->type,
                    'name' => $component->name,
                    'properties' => $component->properties,
                    'screen' => $component->screen,
                ];
            });

        return response()->json([
            'success' => true,
            'screen' => $screen,
            'components' => $components,
        ]);
    }

    // API endpoint to get all screens
    public function getScreens()
    {
        $screens = UIComponent::distinct()->pluck('screen');
        
        return response()->json([
            'success' => true,
            'screens' => $screens,
        ]);
    }

    // Demo page to test server-driven UI
    public function demo($screen = 'home')
    {
        return view('demo', compact('screen'));
    }

    // Admin page to manage components
    public function admin()
    {
        $components = UIComponent::orderBy('screen')->orderBy('order')->get();
        return view('admin', compact('components'));
    }

    // Create new component
    public function createComponent(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'name' => 'required|string',
            'screen' => 'required|string',
            'properties' => 'required|json',
        ]);

        $component = UIComponent::create([
            'type' => $request->type,
            'name' => $request->name,
            'screen' => $request->screen,
            'properties' => json_decode($request->properties, true),
            'order' => UIComponent::where('screen', $request->screen)->max('order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Component created successfully',
            'component' => $component,
        ]);
    }

    // Toggle component status
    public function toggleComponent($id)
    {
        $component = UIComponent::findOrFail($id);
        $component->is_active = !$component->is_active;
        $component->save();

        return response()->json([
            'success' => true,
            'message' => 'Component status updated',
            'is_active' => $component->is_active,
        ]);
    }
}