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

        /** @var \App\Models\User $user */
        $user = auth()->user();
        if ($user->isSystemAdmin()) {
            $records = DeathRecord::with(['deceased', 'informant', 'office'])->paginate(15);
        } elseif ($user->isRegistrar() && $user->office) {
            // Registrars can see records from all offices in their region
            $records = DeathRecord::whereHas('office', function($query) use ($user) {
                $query->where('region', $user->office->region);
            })->with(['deceased', 'informant', 'office'])->paginate(15);
        } else {
            // Other users only see records from their specific office
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
        $userOfficeId = auth()->user()->registration_office_id;
        return view('death_records.create', compact('birthRecords', 'offices', 'userOfficeId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DeathRecord::class);
        $validated = $request->validate([
            'deceased_birth_id' => 'required|exists:birth_records,id',
            'informant_birth_id' => 'nullable|exists:birth_records,id',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'date_of_death' => 'required|date|before_or_equal:today',
            'place_of_death' => 'required|string',
            'cause_of_death' => 'nullable|string',
            'informant_name' => 'nullable|string',
            'informant_relation' => 'nullable|string',
        ], [
            'date_of_death.before_or_equal' => 'Death date cannot be in the future. Please provide a valid past date.',
        ]);

        // Validate death date is after birth date
        $birthRecord = BirthRecord::find($validated['deceased_birth_id']);
        if ($birthRecord && $validated['date_of_death'] <= $birthRecord->date_of_birth) {
            return back()->withErrors(['date_of_death' => 'Death date must be after the person\'s birth date.'])->withInput();
        }

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
