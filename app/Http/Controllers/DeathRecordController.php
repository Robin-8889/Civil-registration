<?php

namespace App\Http\Controllers;

use App\Models\DeathRecord;
use App\Models\BirthRecord;
use App\Models\RegistrationOffice;
use Illuminate\Http\Request;

class DeathRecordController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', DeathRecord::class);

        $user = auth()->user();
        if ($user->isSystemAdmin()) {
            $records = DeathRecord::with(['deceased', 'informant', 'office'])->paginate(15);
        } else {
            // Non-admin users only see records from their office
            $records = DeathRecord::where('registration_office_id', $user->registration_office_id)
                ->with(['deceased', 'informant', 'office'])->paginate(15);
        }

        return view('death_records.index', compact('records'));
    }

    public function create()
    {
        $this->authorize('create', DeathRecord::class);
        $birthRecords = BirthRecord::all();
        $offices = RegistrationOffice::all();
        return view('death_records.create', compact('birthRecords', 'offices'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DeathRecord::class);
        $validated = $request->validate([
            'deceased_birth_id' => 'required|exists:birth_records,id',
            'informant_birth_id' => 'nullable|exists:birth_records,id',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'date_of_death' => 'required|date',
            'place_of_death' => 'required|string',
            'cause_of_death' => 'nullable|string',
            'informant_name' => 'nullable|string',
            'informant_relation' => 'nullable|string',
        ]);

        $validated['registration_date'] = now()->toDateString();
        $validated['status'] = 'pending';

        DeathRecord::create($validated);

        return redirect()->route('death_records.index')->with('success', 'Death record registered successfully.');
    }

    public function show(DeathRecord $deathRecord)
    {
        $this->authorize('view', $deathRecord);
        return view('death_records.show', compact('deathRecord'));
    }

    public function edit(DeathRecord $deathRecord)
    {
        $this->authorize('update', $deathRecord);
        $birthRecords = BirthRecord::all();
        $offices = RegistrationOffice::all();
        return view('death_records.edit', compact('deathRecord', 'birthRecords', 'offices'));
    }

    public function update(Request $request, DeathRecord $deathRecord)
    {
        $this->authorize('update', $deathRecord);
        $validated = $request->validate([
            'deceased_birth_id' => 'required|exists:birth_records,id',
            'informant_birth_id' => 'nullable|exists:birth_records,id',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'date_of_death' => 'required|date',
            'place_of_death' => 'required|string',
            'cause_of_death' => 'required|string',
            'informant_name' => 'nullable|string',
            'informant_relation' => 'nullable|string',
            'status' => 'required|in:registered,pending,rejected',
        ]);

        $deathRecord->update($validated);

        return redirect()->route('death_records.show', $deathRecord)->with('success', 'Death record updated.');
    }

    public function destroy(DeathRecord $deathRecord)
    {
        $this->authorize('delete', $deathRecord);
        $deathRecord->delete();
        return redirect()->route('death_records.index')->with('success', 'Death record deleted.');
    }
}
