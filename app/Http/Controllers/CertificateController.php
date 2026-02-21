<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\BirthRecord;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();

            // Allow system admin, office admin, and registrars
            if ($user && ($user->isSystemAdmin() || $user->isAdmin() || $user->isRegistrar())) {
                return $next($request);
            }

            return response()->view('errors.forbidden', [], 403);
        });
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            $records = Certificate::paginate(15);
        } elseif ($user->isRegistrar() && $user->office) {
            // Registrars see certificates for records from their region
            $records = Certificate::where(function($query) use ($user) {
                $query->whereIn('record_id', function($subQuery) use ($user) {
                    $subQuery->select('id')
                        ->from('birth_records')
                        ->whereIn('registration_office_id', function($officeQuery) use ($user) {
                            $officeQuery->select('id')
                                ->from('registration_offices')
                                ->where('region', $user->office->region);
                        });
                })->where('record_type', 'birth')
                ->orWhereIn('record_id', function($subQuery) use ($user) {
                    $subQuery->select('id')
                        ->from('marriage_records')
                        ->whereIn('registration_office_id', function($officeQuery) use ($user) {
                            $officeQuery->select('id')
                                ->from('registration_offices')
                                ->where('region', $user->office->region);
                        });
                })->where('record_type', 'marriage')
                ->orWhereIn('record_id', function($subQuery) use ($user) {
                    $subQuery->select('id')
                        ->from('death_records')
                        ->whereIn('registration_office_id', function($officeQuery) use ($user) {
                            $officeQuery->select('id')
                                ->from('registration_offices')
                                ->where('region', $user->office->region);
                        });
                })->where('record_type', 'death');
            })->paginate(15);
        } else {
            $records = Certificate::paginate(15);
        }

        return view('certificates.index', compact('records'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            $birthRecords = BirthRecord::where('status', 'registered')->get();
            $marriageRecords = MarriageRecord::with(['groom', 'bride'])->where('status', 'registered')->get();
            $deathRecords = DeathRecord::with('deceased')->where('status', 'registered')->get();
        } elseif ($user->isRegistrar() && $user->office) {
            // Registrars see registered records from their region
            $birthRecords = BirthRecord::where('status', 'registered')
                ->whereHas('office', function($query) use ($user) {
                    $query->where('region', $user->office->region);
                })->get();
            $marriageRecords = MarriageRecord::with(['groom', 'bride'])
                ->where('status', 'registered')
                ->whereHas('office', function($query) use ($user) {
                    $query->where('region', $user->office->region);
                })->get();
            $deathRecords = DeathRecord::with('deceased')
                ->where('status', 'registered')
                ->whereHas('office', function($query) use ($user) {
                    $query->where('region', $user->office->region);
                })->get();
        } else {
            $birthRecords = BirthRecord::where('status', 'registered')->get();
            $marriageRecords = MarriageRecord::with(['groom', 'bride'])->where('status', 'registered')->get();
            $deathRecords = DeathRecord::with('deceased')->where('status', 'registered')->get();
        }

        return view('certificates.create', compact('birthRecords', 'marriageRecords', 'deathRecords'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'record_id' => 'required|integer',
            'record_type' => 'required|in:birth,marriage,death',
            'certificate_number' => 'required|unique:certificates',
            'issue_date' => 'required|date',
            'issued_by' => 'required|string',
            'copies_issued' => 'required|integer|min:1',
            'status' => 'required|in:issued,cancelled,renewed',
        ]);

        Certificate::create($validated);

        return redirect()->route('certificates.index')->with('success', 'Certificate issued successfully.');
    }

    public function show(Certificate $certificate)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return view('certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            $birthRecords = BirthRecord::where('status', 'registered')->get();
            $marriageRecords = MarriageRecord::with(['groom', 'bride'])->where('status', 'registered')->get();
            $deathRecords = DeathRecord::with('deceased')->where('status', 'registered')->get();
        } elseif ($user->isRegistrar() && $user->office) {
            // Registrars see registered records from their region
            $birthRecords = BirthRecord::where('status', 'registered')
                ->whereHas('office', function($query) use ($user) {
                    $query->where('region', $user->office->region);
                })->get();
            $marriageRecords = MarriageRecord::with(['groom', 'bride'])
                ->where('status', 'registered')
                ->whereHas('office', function($query) use ($user) {
                    $query->where('region', $user->office->region);
                })->get();
            $deathRecords = DeathRecord::with('deceased')
                ->where('status', 'registered')
                ->whereHas('office', function($query) use ($user) {
                    $query->where('region', $user->office->region);
                })->get();
        } else {
            $birthRecords = BirthRecord::where('status', 'registered')->get();
            $marriageRecords = MarriageRecord::with(['groom', 'bride'])->where('status', 'registered')->get();
            $deathRecords = DeathRecord::with('deceased')->where('status', 'registered')->get();
        }

        return view('certificates.edit', compact('certificate', 'birthRecords', 'marriageRecords', 'deathRecords'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'record_id' => 'required|integer',
            'record_type' => 'required|in:birth,marriage,death',
            'certificate_number' => 'required|string',
            'issued_by' => 'required|string',
            'issue_date' => 'required|date',
            'copies_issued' => 'required|integer|min:1',
            'status' => 'required|in:issued,cancelled,renewed',
        ]);

        $certificate->update($validated);

        return redirect()->route('certificates.show', $certificate)->with('success', 'Certificate updated.');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted.');
    }

    public function download(Certificate $certificate)
    {
        // Get the related record details
        $recordData = null;

        if ($certificate->record_type === 'birth') {
            $recordData = BirthRecord::find($certificate->record_id);
        } elseif ($certificate->record_type === 'marriage') {
            $recordData = MarriageRecord::with(['groom', 'bride'])->find($certificate->record_id);
        } elseif ($certificate->record_type === 'death') {
            $recordData = DeathRecord::with('deceased')->find($certificate->record_id);
        }

        return view('certificates.download', compact('certificate', 'recordData'));
    }
}
