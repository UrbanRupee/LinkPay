<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provider;

class ProviderController extends Controller
{
    /**
     * Display a listing of the providers.
     */
    public function index()
    {
        $providers = Provider::orderBy('name')->get();
        $title = "Providers";
        return view('admin.providers.index', compact('providers', 'title'));
    }

    /**
     * Store a newly created provider in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:providers,name',
            'location' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'commercial_mdr' => 'nullable|string|max:500', // Validation for the new field
            'settlement_timeline' => 'nullable|string|max:255',
            'settlement_mode' => 'nullable|string|max:255',
            'contact_spoc' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'risk_and_blacklisting' => 'nullable|string',
            'status' => 'required|in:active,inactive,hold',
        ]);

        Provider::create([
            'name' => $request->name,
            'location' => $request->location,
            'service_type' => $request->service_type,
            'url' => $request->url,
            'commercial_mdr' => $request->commercial_mdr, // Use the input value directly
            'cards' => $request->has('cards'),
            'apms' => $request->has('apms'),
            'bank_transfer' => $request->has('bank_transfer'),
            'in' => $request->has('in'),
            'out' => $request->has('out'),
            'settlement_timeline' => $request->settlement_timeline,
            'settlement_mode' => $request->settlement_mode,
            'contact_spoc' => $request->contact_spoc,
            'contact_number' => $request->contact_number,
            'risk_and_blacklisting' => $request->risk_and_blacklisting,
            'status' => $request->status,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Provider added successfully!']);
    }

    /**
     * Show the form for editing the specified provider.
     */
    public function edit(Provider $provider)
    {
        return response()->json($provider);
    }

    /**
     * Update the specified provider in storage.
     */
    public function update(Request $request, Provider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:providers,name,' . $provider->id,
            'location' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'commercial_mdr' => 'nullable|string|max:500', // Validation for the new field
            'settlement_timeline' => 'nullable|string|max:255',
            'settlement_mode' => 'nullable|string|max:255',
            'contact_spoc' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'risk_and_blacklisting' => 'nullable|string',
            'status' => 'required|in:active,inactive,hold',
        ]);

        $provider->update([
            'name' => $request->name,
            'location' => $request->location,
            'service_type' => $request->service_type,
            'url' => $request->url,
            'commercial_mdr' => $request->commercial_mdr, // Use the input value directly
            'cards' => $request->has('cards'),
            'apms' => $request->has('apms'),
            'bank_transfer' => $request->has('bank_transfer'),
            'in' => $request->has('in'),
            'out' => $request->has('out'),
            'settlement_timeline' => $request->settlement_timeline,
            'settlement_mode' => $request->settlement_mode,
            'contact_spoc' => $request->contact_spoc,
            'contact_number' => $request->contact_number,
            'risk_and_blacklisting' => $request->risk_and_blacklisting,
            'status' => $request->status,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Provider updated successfully!']);
    }

    /**
     * Remove the specified provider from storage.
     */
    public function destroy(Provider $provider)
    {
        try {
            $provider->delete();
            return response()->json(['status' => 'success', 'message' => 'Provider deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete provider: ' . $e->getMessage()], 500);
        }
    }
}