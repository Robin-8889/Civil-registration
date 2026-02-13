<?php

namespace App\Http\Controllers;

use App\Models\MarriageRecord;
use App\Models\BirthRecord;
use App\Models\RegistrationOffice;
use Illuminate\Http\Request;

class MarriageRecordController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', MarriageRecord::class);

        $user = auth()->user();
        if ($user->isSystemAdmin()) {
            $records = MarriageRecord::with(['groom', 'bride', 'office'])->paginate(15);
        } else {
            // Non-admin users only see records from their office
            $records = MarriageRecord::where('registration_office_id', $user->registration_office_id)
                ->with(['groom', 'bride', 'office'])->paginate(15);
        }

        return view('marriage_records.index', compact('records'));
    }

    public function create()
    {
        $this->authorize('create', MarriageRecord::class);
        $birthRecords = BirthRecord::with('child')->get();
        $offices = RegistrationOffice::all();
        return view('marriage_records.create', compact('birthRecords', 'offices'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', MarriageRecord::class);
        $validated = $request->validate([
            'groom_id' => 'required|exists:birth_records,id|different:bride_id',
            'bride_id' => 'required|exists:birth_records,id',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'date_of_marriage' => 'required|date',
            'place_of_marriage' => 'required|string',
            'witness1_name' => 'nullable|string',
            'witness2_name' => 'nullable|string',
        ]);

        $validated['registration_date'] = now()->toDateString();
        $validated['status'] = 'pending';

        MarriageRecord::create($validated);

        return redirect()->route('marriage_records.index')->with('success', 'Marriage record registered successfully.');
    }

    public function show(MarriageRecord $marriageRecord)
    {
        $this->authorize('view', $marriageRecord);
        return view('marriage_records.show', compact('marriageRecord'));
    }

    public function edit(MarriageRecord $marriageRecord)
    {
        $this->authorize('update', $marriageRecord);
        $birthRecords = BirthRecord::with('child')->get();
        $offices = RegistrationOffice::all();
        return view('marriage_records.edit', compact('marriageRecord', 'birthRecords', 'offices'));
    }

    public function update(Request $request, MarriageRecord $marriageRecord)
    {
        $this->authorize('update', $marriageRecord);
        $validated = $request->validate([
            'groom_id' => 'required|exists:birth_records,id',
            'bride_id' => 'required|exists:birth_records,id',
            'registration_office_id' => 'required|exists:registration_offices,id',
            'date_of_marriage' => 'required|date',
            'place_of_marriage' => 'required|string',
            'witness1_name' => 'nullable|string',
            'witness2_name' => 'nullable|string',
            'status' => 'required|in:registered,pending,rejected',
        ]);

        $marriageRecord->update($validated);

        return redirect()->route('marriage_records.show', $marriageRecord)->with('success', 'Marriage record updated.');
    }

    public function destroy(MarriageRecord $marriageRecord)
    {
        $this->authorize('delete', $marriageRecord);
        $marriageRecord->delete();
        return redirect()->route('marriage_records.index')->with('success', 'Marriage record deleted.');
    }
}
