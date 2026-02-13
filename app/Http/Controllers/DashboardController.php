<?php

namespace App\Http\Controllers;

use App\Models\BirthRecord;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'birth_records' => BirthRecord::count(),
            'marriage_records' => MarriageRecord::count(),
            'death_records' => DeathRecord::count(),
            'certificates' => Certificate::count(),
            'pending_births' => BirthRecord::where('status', 'pending')->count(),
            'pending_marriages' => MarriageRecord::where('status', 'pending')->count(),
            'pending_deaths' => DeathRecord::where('status', 'pending')->count(),
        ];

        // Get pending and granted users for system admin
        $pendingUsers = [];
        $grantedUsers = [];
        if (auth()->user()->isSystemAdmin()) {
            $pendingUsers = User::where('is_approved', false)
                ->with('office')
                ->latest()
                ->get();

            // Get users who have been granted office admin permission (approved registrars)
            $grantedUsers = User::where('is_approved', true)
                ->where('role', 'registrar')
                ->with('office')
                ->latest()
                ->get();
        }

        return view('dashboard', compact('stats', 'pendingUsers', 'grantedUsers'));
    }
}
