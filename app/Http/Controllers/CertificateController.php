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

            if ($user && ($user->isSystemAdmin() || $user->isAdmin())) {
                return $next($request);
            }

            return response()->view('errors.forbidden', [], 403);
        });
    }

    public function index()
    {
        $records = Certificate::paginate(15);
        return view('certificates.index', compact('records'));
    }

    public function create()
    {
        $birthRecords = BirthRecord::where('status', 'registered')->get();
        $marriageRecords = MarriageRecord::with(['groom', 'bride'])->where('status', 'registered')->get();
        $deathRecords = DeathRecord::with('deceased')->where('status', 'registered')->get();

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
        return view('certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        $birthRecords = BirthRecord::where('status', 'registered')->get();
        $marriageRecords = MarriageRecord::with(['groom', 'bride'])->where('status', 'registered')->get();
        $deathRecords = DeathRecord::with('deceased')->where('status', 'registered')->get();

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
}
