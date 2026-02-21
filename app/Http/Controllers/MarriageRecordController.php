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

        /** @var \App\Models\User $user */
        $user = auth()->user();
        if ($user->isSystemAdmin()) {
            $records = MarriageRecord::with(['groom', 'bride', 'office'])->paginate(15);
        } elseif ($user->isRegistrar() && $user->office) {
            // Registrars can see records from all offices in their region
            $records = MarriageRecord::whereHas('office', function($query) use ($user) {
                $query->where('region', $user->office->region);
            })->with(['groom', 'bride', 'office'])->paginate(15);
        } else {
            // Other users only see records from their specific office
            $records = MarriageRecord::where('registration_office_id', $user->registration_office_id)
                ->with(['groom', 'bride', 'office'])->paginate(15);
        }

        return view('marriage_records.index', compact('records'));
    }

    public function create()
    {
        $this->authorize('create', MarriageRecord::class);
        $grooms = BirthRecord::where('gender', 'M')->get();
        $brides = BirthRecord::where('gender', 'F')->get();
        $birthRecords = BirthRecord::all();
        $offices = RegistrationOffice::all();
        $userOfficeId = auth()->user()->registration_office_id;
        return view('marriage_records.create', compact('grooms', 'brides', 'birthRecords', 'offices', 'userOfficeId'));
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
            'witness1_name' => 'required|string',
            'witness2_name' => 'required|string|different:witness1_name',
        ], [
            'witness1_name.required' => 'First witness is required.',
            'witness2_name.required' => 'Second witness is required.',
            'witness2_name.different' => 'The second witness must be different from the first witness.',
        ]);

        $validated['registration_date'] = now()->toDateString();
        $validated['status'] = 'pending';

        try {
            MarriageRecord::create($validated);
            return redirect()->route('marriage_records.index')->with('success', 'Marriage record registered successfully.');
        } catch (\Exception $e) {
            // Check if error is from age validation trigger
            if (strpos($e->getMessage(), 'Both spouses must be at least 18 years old') !== false) {
                return redirect()->back()->with('error', 'Marriage registration unsuccessful: Both individuals must have reached the legal age of 18 years.');
            }

            // Check for other trigger-related errors
            if (strpos($e->getMessage(), 'Marriage date cannot be in the future') !== false) {
                return redirect()->back()->with('error', 'Marriage registration unsuccessful: The marriage date cannot be in the future.');
            }

            // Re-throw if it's an unexpected error
            throw $e;
        }
    }

    public function show(MarriageRecord $marriageRecord)
    {
        $this->authorize('view', $marriageRecord);
        return view('marriage_records.show', compact('marriageRecord'));
    }

    public function edit(MarriageRecord $marriageRecord)
    {
        $this->authorize('update', $marriageRecord);
        $grooms = BirthRecord::where('gender', 'M')->get();
        $brides = BirthRecord::where('gender', 'F')->get();
        $birthRecords = BirthRecord::all();
        $offices = RegistrationOffice::all();
        return view('marriage_records.edit', compact('marriageRecord', 'grooms', 'brides', 'birthRecords', 'offices'));
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
            'witness1_name' => 'required|string',
            'witness2_name' => 'required|string|different:witness1_name',
            'status' => 'required|in:registered,pending,rejected',
        ], [
            'witness1_name.required' => 'First witness is required.',
            'witness2_name.required' => 'Second witness is required.',
            'witness2_name.different' => 'The second witness must be different from the first witness.',
        ]);

        try {
            $marriageRecord->update($validated);
            return redirect()->route('marriage_records.show', $marriageRecord)->with('success', 'Marriage record updated.');
        } catch (\Exception $e) {
            // Check if error is from age validation trigger
            if (strpos($e->getMessage(), 'Both spouses must be at least 18 years old') !== false) {
                return redirect()->back()->with('error', 'Marriage update unsuccessful: Both individuals must have reached the legal age of 18 years.');
            }

            // Check for other trigger-related errors
            if (strpos($e->getMessage(), 'Marriage date cannot be in the future') !== false) {
                return redirect()->back()->with('error', 'Marriage update unsuccessful: The marriage date cannot be in the future.');
            }

            // Re-throw if it's an unexpected error
            throw $e;
        }
    }

    public function destroy(MarriageRecord $marriageRecord)
    {
        $this->authorize('delete', $marriageRecord);
        $marriageRecord->delete();
        return redirect()->route('marriage_records.index')->with('success', 'Marriage record deleted.');
    }
}
