@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>User Management Dashboard</h2>
            <p class="text-muted">System Admin - Manage user roles, approvals, and regional office assignments</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Region</label>
                    <select name="region" class="form-select">
                        <option value="">All Regions</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg }}" {{ $region === $reg ? 'selected' : '' }}>
                                {{ $reg }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Users</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved Only</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Users Table -->
    @if($users->count())
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Region</th>
                            <th>Role</th>
                            <th>Approval Status</th>
                            <th>System Admin</th>
                            <th>Account Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->office)
                                        <span class="badge bg-info">{{ $user->office->region }}</span>
                                    @else
                                        <span class="badge bg-secondary">No Office</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'registrar' ? 'primary' : ($user->role === 'clerk' ? 'warning' : 'secondary')) }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_approved)
                                        <span class="badge bg-success">✓ Approved</span>
                                    @else
                                        <span class="badge bg-warning text-dark">⚠ Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_system_admin)
                                        <span class="badge bg-danger">System Admin</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-info" title="Edit User">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>

                                        @if($user->is_approved)
                                            <form action="{{ route('users.disapprove', $user) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Revoke Approval" onclick="return confirm('Revoke approval?')">
                                                    <i class="bi bi-x-circle"></i> Deny
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('users.approve', $user) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Grant Approval">
                                                    <i class="bi bi-check-circle"></i> Approve
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
            <p class="mt-2">No users found matching your filters.</p>
@endsection
