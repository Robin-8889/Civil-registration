<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RegistrationOffice;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Only system admin can access user management
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Only system admin
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized. Only system admin can manage users.');
        }

        $region = request('region');
        $status = request('status');

        $query = User::with('office');

        // Filter by region
        if ($region && $region !== 'all') {
            $query->whereHas('office', function ($q) use ($region) {
                $q->where('region', $region);
            });
        }

        // Filter by approval status
        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        }

        $users = $query->paginate(15);

        // Get all regions for filter dropdown
        $regions = \App\Models\RegistrationOffice::distinct('region')
            ->orderBy('region')
            ->pluck('region');

        return view('users.index', compact('users', 'regions', 'region', 'status'));
    }

    public function approve(User $user)
    {
        // Only system admin
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        $user->update(['is_approved' => true]);
        return redirect()->route('users.index')->with('success', "{$user->name} has been approved.");
    }

    public function disapprove(User $user)
    {
        // Only system admin
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        $user->update(['is_approved' => false]);
        return redirect()->route('users.index')->with('success', "{$user->name} has been disapproved.");
    }

    public function edit(User $user)
    {
        // Only system admin
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        $offices = RegistrationOffice::all();
        return view('users.edit', compact('user', 'offices'));
    }

    public function update(Request $request, User $user)
    {
        // Only system admin
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,registrar,clerk,citizen',
            'registration_office_id' => 'nullable|exists:registration_offices,id',
            'status' => 'required|in:active,inactive',
            'is_approved' => 'boolean',
        ]);

        // If assigning as registrar/clerk, must assign to an office
        if (in_array($validated['role'], ['registrar', 'clerk']) && !$validated['registration_office_id']) {
            return back()->withErrors(['registration_office_id' => 'Office is required for registrars and clerks.']);
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function setSystemAdmin(User $user)
    {
        // Only existing system admin
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        $user->update(['is_system_admin' => true, 'role' => 'admin']);
        return redirect()->route('users.index')->with('success', "{$user->name} is now a system admin.");
    }

    public function removeSystemAdmin(User $user)
    {
        // Prevent removing the last system admin
        if (User::where('is_system_admin', true)->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot remove the last system admin.']);
        }

        $user->update(['is_system_admin' => false]);
        return redirect()->route('users.index')->with('success', "{$user->name} is no longer a system admin.");
    }

    public function grantOfficeAdmin(Request $request, User $user)
    {
        // Only system admin can grant office admin roles
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'registration_office_id' => 'required|exists:registration_offices,id',
        ]);

        // Update user to be registrar with admin role and approve them
        $user->update([
            'role' => 'registrar',
            'registration_office_id' => $request->registration_office_id,
            'is_approved' => true,
        ]);

        return redirect()->route('dashboard')->with('success', "{$user->name} has been granted admin permission for the selected office and approved!");
    }

    public function revokeOfficeAdmin(User $user)
    {
        // Only system admin can revoke office admin roles
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Remove approval to move them back to pending users
        $user->update(['is_approved' => false]);

        return redirect()->route('dashboard')->with('success', "{$user->name}'s office admin permission has been revoked.");
    }

    public function destroy(User $user)
    {
        // Only system admin can delete users
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Prevent deleting the last system admin
        if ($user->isSystemAdmin() && User::where('is_system_admin', true)->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last system admin.']);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('dashboard')->with('success', "User '{$userName}' has been deleted successfully.");
    }
}
