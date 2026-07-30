<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\UIComponent;
use Illuminate\Http\Request;

class UIController extends Controller
{
    /**
     * API : Get Components
     */
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

    /**
     * API : Get Screens
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
    public function demo($screen = 'home')
    {
        return view('demo', compact('screen'));
    }

    /**
     * Admin Dashboard
     * Search + Filter + Pagination + Statistics
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

        // Filter Status
        if ($request->status != "") {
            $query->where('is_active', $request->status);
        }

        $components = $query
            ->orderBy('id', 'asc')
            ->paginate(3)
            ->withQueryString();

        // Statistics

        $statistics = [

            'total' => UIComponent::count(),

            'active' => UIComponent::where('is_active', 1)->count(),

            'inactive' => UIComponent::where('is_active', 0)->count(),

            'screens' => UIComponent::distinct('screen')->count(),

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
     * Create Component
     */
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
     * Export Components CSV
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
                    'Properties',
                    'Status',
                    'Created Date'

                ]);




                foreach ($components as $component) {


                    fputcsv($handle, [

                        $component->id,

                        $component->type,

                        $component->name,

                        $component->screen,

                        json_encode($component->properties),

                        $component->is_active
                            ? 'Active'
                            : 'Inactive',

                        $component->created_at

                    ]);
                }



                fclose($handle);
            },
            200,
            [

                'Content-Type' => 'text/csv',

                'Content-Disposition' =>
                'attachment; filename="ui_components.csv"',


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
