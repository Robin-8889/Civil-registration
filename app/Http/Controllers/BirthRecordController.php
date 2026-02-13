<?php

namespace App\Http\Controllers;

use App\Models\BirthRecord;
use App\Models\RegistrationOffice;
use Illuminate\Http\Request;

class BirthRecordController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', BirthRecord::class);

        $user = auth()->user();
        if ($user->isSystemAdmin()) {
            $records = BirthRecord::with(['office'])->paginate(15);
        } else {
            // Non-admin users only see records from their office
            $records = BirthRecord::where('registration_office_id', $user->registration_office_id)
                ->with(['office'])->paginate(15);
        }

        return view('birth_records.index', compact('records'));
    }

    public function create()
    {
        $this->authorize('create', BirthRecord::class);
        $offices = RegistrationOffice::all();
        return view('birth_records.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', BirthRecord::class);
        $validated = $request->validate([
            'nationality' => 'required|string',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string',
            'child_first_name' => 'required|string',
            'child_middle_name' => 'nullable|string',
            'child_last_name' => 'required|string',
            'gender' => 'required|in:M,F',
            'father_name' => 'nullable|string',
            'mother_name' => 'nullable|string',
        ]);

        $validated['registration_date'] = now()->toDateString();
        $validated['status'] = 'pending';

        BirthRecord::create($validated);

        return redirect()->route('birth_records.index')->with('success', 'Birth record registered successfully.');
    }

    public function show(BirthRecord $birthRecord)
    {
        $this->authorize('view', $birthRecord);
        return view('birth_records.show', compact('birthRecord'));
    }

    public function edit(BirthRecord $birthRecord)
    {
        $this->authorize('update', $birthRecord);
        $offices = RegistrationOffice::all();
        return view('birth_records.edit', compact('birthRecord', 'offices'));
    }

    public function update(Request $request, BirthRecord $birthRecord)
    {
        $this->authorize('update', $birthRecord);
        $validated = $request->validate([
            'nationality' => 'required|string',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'child_first_name' => 'required|string',
            'child_middle_name' => 'nullable|string',
            'child_last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:M,F',
            'place_of_birth' => 'required|string',
            'father_name' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'status' => 'required|in:registered,pending,rejected',
        ]);

        $birthRecord->update($validated);

        return redirect()->route('birth_records.show', $birthRecord)->with('success', 'Birth record updated successfully.');
    }

    public function destroy(BirthRecord $birthRecord)
    {
        $this->authorize('delete', $birthRecord);
        $birthRecord->delete();
        return redirect()->route('birth_records.index')->with('success', 'Birth record deleted.');
    }
}
