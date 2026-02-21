<?php

namespace App\Http\Controllers;

use App\Models\BirthRecord;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use App\Models\RegistrationOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CitizenController extends Controller
{
    public function index(Request $request)
    {
        // Determine if user is system admin
        $isSystemAdmin = Auth::user()->is_system_admin;
        $userOfficeId = Auth::user()->registration_office_id;

        // Get all regions for filter dropdown
        $regions = RegistrationOffice::select('region')->distinct()->orderBy('region')->get();

        // Start query with birth records as base (include pending and registered - exclude rejected)
        $query = BirthRecord::with(['office', 'certificate', 'deathRecord'])
            ->whereIn('status', ['pending', 'registered']);

        // If not system admin, only show citizens from their office's region
        if (!$isSystemAdmin) {
            $userRegion = RegistrationOffice::find($userOfficeId)->region ?? null;
            if ($userRegion) {
                $query->whereHas('office', function ($q) use ($userRegion) {
                    $q->where('region', $userRegion);
                });
            }
        }

        // Apply filters
        // Filter by region (only if system admin)
        if ($isSystemAdmin && $request->filled('region')) {
            $query->whereHas('office', function ($q) use ($request) {
                $q->where('region', $request->region);
            });
        }

        // Filter by name
        if ($request->filled('name')) {
            $searchName = '%' . $request->name . '%';
            $query->where(function ($q) use ($searchName) {
                $q->where('child_first_name', 'like', $searchName)
                  ->orWhere('child_last_name', 'like', $searchName)
                  ->orWhere('child_middle_name', 'like', $searchName);
            });
        }

        // Filter by birth certificate number
        if ($request->filled('birth_certificate_no')) {
            $query->where('birth_certificate_no', 'like', '%' . $request->birth_certificate_no . '%');
        }

        // Filter by age
        if ($request->filled('age_from') || $request->filled('age_to')) {
            $ageFrom = (int)($request->age_from ?? 0);
            $ageTo = (int)($request->age_to ?? 150);

            $fromDate = Carbon::now()->subYears($ageTo)->format('Y-m-d');
            $toDate = Carbon::now()->subYears($ageFrom)->format('Y-m-d');

            $query->whereBetween('date_of_birth', [$fromDate, $toDate]);
        }

        // Filter by record status (pending, registered, approved)
        if ($request->filled('record_status')) {
            $query->where('status', $request->record_status);
        }

        // Filter by marital status
        if ($request->filled('marital_status')) {
            if ($request->marital_status === 'married') {
                // Show only people who have a marriage record (as groom or bride)
                $query->where(function ($q) {
                    $q->whereHas('marriageAsGroom')
                      ->orWhereHas('marriageAsBride');
                });
            } elseif ($request->marital_status === 'single') {
                // Show only people who don't have a marriage record
                $query->whereDoesntHave('marriageAsGroom')
                      ->whereDoesntHave('marriageAsBride');
            }
        }

        // Filter by status (alive or dead)
        if ($request->filled('status_filter')) {
            if ($request->status_filter === 'dead') {
                // Only show citizens who have a death record
                $query->whereHas('deathRecord');
            } elseif ($request->status_filter === 'alive') {
                // Only show citizens who don't have a death record
                $query->whereDoesntHave('deathRecord');
            }
        }

        // Get total count before pagination
        $totalCount = $query->count();

        // Get paginated results with additional data
        $citizens = $query->orderBy('created_at', 'desc')->paginate(20);

        // Enhance citizen data with marriage and death information
        foreach ($citizens as $citizen) {
            // Get marriage information (as groom or bride)
            $marriage = MarriageRecord::where(function ($q) use ($citizen) {
                $q->where('groom_id', $citizen->id)
                  ->orWhere('bride_id', $citizen->id);
            })->whereIn('status', ['pending', 'registered'])->first();

            $citizen->marriage = $marriage;

            // Death information is already loaded via deathRecord relationship
            $citizen->death = $citizen->deathRecord;

            // Calculate age
            $citizen->age = Carbon::parse($citizen->date_of_birth)->age;
        }

        return view('citizens.index', [
            'citizens' => $citizens,
            'totalCount' => $totalCount,
            'regions' => $regions,
            'isSystemAdmin' => $isSystemAdmin,
            'filters' => [
                'name' => $request->name,
                'birth_certificate_no' => $request->birth_certificate_no,
                'age_from' => $request->age_from,
                'age_to' => $request->age_to,
                'status_filter' => $request->status_filter,
                'record_status' => $request->record_status,
                'marital_status' => $request->marital_status,
                'region' => $request->region,
            ]
        ]);
    }
}
