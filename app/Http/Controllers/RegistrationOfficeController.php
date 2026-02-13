<?php

namespace App\Http\Controllers;

use App\Models\RegistrationOffice;
use Illuminate\Http\Request;

class RegistrationOfficeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RegistrationOffice::class);

        $q = $request->query('q');
        $region = $request->query('region');
        $status = $request->query('status');

        $query = RegistrationOffice::query();

        if ($q) {
            $query->where(function ($builder) use ($q) {
                $like = '%' . $q . '%';
                $builder->where('office_name', 'like', $like)
                    ->orWhere('location', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('region', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        if ($region) {
            $query->where('region', $region);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $records = $query->orderBy('region')->orderBy('office_name')
            ->paginate(15)
            ->appends($request->query());

        $regions = RegistrationOffice::distinct('region')
            ->orderBy('region')
            ->pluck('region');

        return view('registration_offices.index', compact('records', 'regions', 'q', 'region', 'status'));
    }

    public function create()
    {
        $this->authorize('create', RegistrationOffice::class);
        return view('registration_offices.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', RegistrationOffice::class);
        $validated = $request->validate([
            'office_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string',
        ]);

        RegistrationOffice::create($validated);

        return redirect()->route('registration_offices.index')->with('success', 'Office created successfully.');
    }

    public function show(RegistrationOffice $registrationOffice)
    {
        $this->authorize('view', $registrationOffice);
        $office = $registrationOffice;
        $office->load(['users', 'birthRecords', 'marriageRecords', 'deathRecords']);
        return view('registration_offices.show', compact('office'));
    }

    public function edit(RegistrationOffice $registrationOffice)
    {
        $this->authorize('update', $registrationOffice);
        $office = $registrationOffice;
        return view('registration_offices.edit', compact('office'));
    }

    public function update(Request $request, RegistrationOffice $registrationOffice)
    {
        $this->authorize('update', $registrationOffice);
        $validated = $request->validate([
            'office_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        $registrationOffice->update($validated);

        return redirect()->route('registration_offices.show', $registrationOffice)->with('success', 'Office updated.');
    }

    public function destroy(RegistrationOffice $registrationOffice)
    {
        $this->authorize('delete', $registrationOffice);
        $registrationOffice->delete();
        return redirect()->route('registration_offices.index')->with('success', 'Office deleted.');
    }
}
