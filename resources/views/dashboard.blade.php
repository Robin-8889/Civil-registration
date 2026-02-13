@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Enhanced Header with Bootstrap utilities -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5 fw-bold text-primary">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <div class="text-muted">
            <small>Welcome, <strong>{{ auth()->user()->name }}</strong></small>
        </div>
    </div>

    <!-- Statistics Cards with Icons and Hover Effects -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary border-0 shadow-sm h-100 hover-lift">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Birth Records</h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="opacity-75" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        </svg>
                    </div>
                    <h2 class="display-4 fw-bold mb-0">{{ $stats['birth_records'] }}</h2>
                    <small class="mt-2 opacity-75">Total Registered</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success border-0 shadow-sm h-100 hover-lift">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Marriage Records</h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="opacity-75" viewBox="0 0 16 16">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                        </svg>
                    </div>
                    <h2 class="display-4 fw-bold mb-0">{{ $stats['marriage_records'] }}</h2>
                    <small class="mt-2 opacity-75">Total Registered</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-danger border-0 shadow-sm h-100 hover-lift">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Death Records</h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="opacity-75" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M4.285 12.433a.5.5 0 0 0 .683-.183A3.498 3.498 0 0 1 8 10.5c1.295 0 2.426.703 3.032 1.75a.5.5 0 0 0 .866-.5A4.498 4.498 0 0 0 8 9.5a4.5 4.5 0 0 0-3.898 2.25.5.5 0 0 0 .183.683z"/>
                        </svg>
                    </div>
                    <h2 class="display-4 fw-bold mb-0">{{ $stats['death_records'] }}</h2>
                    <small class="mt-2 opacity-75">Total Registered</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info border-0 shadow-sm h-100 hover-lift">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Certificates</h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="opacity-75" viewBox="0 0 16 16">
                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                        </svg>
                    </div>
                    <h2 class="display-4 fw-bold mb-0">{{ $stats['certificates'] }}</h2>
                    <small class="mt-2 opacity-75">Total Issued</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Registrations & Quick Actions Row -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Pending Registrations
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Births Pending</span>
                            <span class="badge bg-primary rounded-pill fs-6">{{ $stats['pending_births'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Marriages Pending</span>
                            <span class="badge bg-success rounded-pill fs-6">{{ $stats['pending_marriages'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom-0">
                            <span class="text-muted">Deaths Pending</span>
                            <span class="badge bg-danger rounded-pill fs-6">{{ $stats['pending_deaths'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('birth_records.create') }}" class="btn btn-outline-primary btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            <path fill-rule="evenodd" d="M5 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 5 8zm-.5 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                        Register Birth
                    </a>
                    <a href="{{ route('marriage_records.create') }}" class="btn btn-outline-success btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748z"/>
                        </svg>
                        Register Marriage
                    </a>
                    <a href="{{ route('death_records.create') }}" class="btn btn-outline-danger btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        </svg>
                        Register Death
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isSystemAdmin())
        <!-- Pending Users Section -->
        @if($pendingUsers->count() > 0)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">⚠️ Pending User Approvals - Grant Office Admin Permission ({{ $pendingUsers->count() }} pending)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Registered Office</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingUsers as $pendingUser)
                                            <tr>
                                                <td><strong>{{ $pendingUser->name }}</strong></td>
                                                <td>{{ $pendingUser->email }}</td>
                                                <td>
                                                    @if($pendingUser->office)
                                                        <span class="badge bg-info">{{ $pendingUser->office->region }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">No Office Selected</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pendingUser->registration_office_id)
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <form action="{{ route('users.grantOfficeAdmin', $pendingUser) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                <input type="hidden" name="registration_office_id" value="{{ $pendingUser->registration_office_id }}">
                                                                <button type="submit" class="btn btn-success" title="Grant Admin for {{ $pendingUser->office->region }} office">
                                                                    Grant Admin
                                                                </button>
                                                            </form>
                                                            <button type="button" class="btn btn-danger delete-btn"
                                                                data-delete-title="Delete User"
                                                                data-delete-message="Are you sure you want to delete {{ addslashes($pendingUser->name) }}? This action cannot be undone."
                                                                data-delete-action="{{ route('users.destroy', $pendingUser) }}"
                                                                title="Delete user">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    @else
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button class="btn btn-secondary" disabled title="User must select an office">
                                                                No Office to Assign
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                                data-delete-title="Delete User"
                                                                data-delete-message="Are you sure you want to delete {{ addslashes($pendingUser->name) }}? This action cannot be undone."
                                                                data-delete-action="{{ route('users.destroy', $pendingUser) }}"
                                                                title="Delete user">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Granted Users Section -->
        @if($grantedUsers->count() > 0)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">✓ Granted Office Admin Users ({{ $grantedUsers->count() }} active)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Office</th>
                                            <th>Role</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($grantedUsers as $grantedUser)
                                            <tr>
                                                <td><strong>{{ $grantedUser->name }}</strong></td>
                                                <td>{{ $grantedUser->email }}</td>
                                                <td>
                                                    @if($grantedUser->office)
                                                        <span class="badge bg-info">{{ $grantedUser->office->region }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">No Office</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ ucfirst($grantedUser->role) }}</span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger revoke-btn"
                                                        data-delete-title="Revoke Permission"
                                                        data-delete-message="Are you sure you want to revoke admin permission from {{ addslashes($grantedUser->name) }}? They will be moved back to pending users."
                                                        data-delete-action="{{ route('users.revokeOfficeAdmin', $grantedUser) }}"
                                                        data-is-revoke="true"
                                                        title="Revoke admin permission">
                                                        Revoke Permission
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Message when no pending or granted users -->
        @if($pendingUsers->count() === 0 && $grantedUsers->count() === 0)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center text-muted">
                            <p>No pending users to approve or granted users to manage.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<!-- Custom Confirmation Modal -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title" id="modalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
        let actionForm = null;
        let isRevoke = false;

        // Handle delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const title = this.getAttribute('data-delete-title');
                const message = this.getAttribute('data-delete-message');
                const action = this.getAttribute('data-delete-action');

                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalMessage').textContent = message;

                actionForm = action;
                isRevoke = false;

                confirmModal.show();
            });
        });

        // Handle revoke buttons
        document.querySelectorAll('.revoke-btn').forEach(button => {
            button.addEventListener('click', function() {
                const title = this.getAttribute('data-delete-title');
                const message = this.getAttribute('data-delete-message');
                const action = this.getAttribute('data-delete-action');

                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalMessage').textContent = message;

                actionForm = action;
                isRevoke = true;

                confirmModal.show();
            });
        });

        // Handle confirmation
        document.getElementById('confirmActionButton').addEventListener('click', function() {
            if (actionForm) {
                // Create a temporary form and submit it
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = actionForm;
                tempForm.style.display = 'none';

                const csrfTokenTag = document.querySelector('meta[name="csrf-token"]');
                const csrfTokenValue = csrfTokenTag ? csrfTokenTag.getAttribute('content') : '{{ csrf_token() }}';
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrfTokenValue;
                tempForm.appendChild(tokenInput);

                // Add DELETE method if not revoke
                if (!isRevoke) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    tempForm.appendChild(methodInput);
                }

                document.body.appendChild(tempForm);
                tempForm.submit();
            }
            confirmModal.hide();
        });
    });
</script>
@endpush

@endsection
