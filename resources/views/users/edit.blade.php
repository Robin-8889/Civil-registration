@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8 offset-md-2">
            <h2>Edit User: {{ $user->name }}</h2>
            <p class="text-muted">System Admin Only - Update user role, office, and approval status</p>
        </div>
    </div>

    @if($errors->any())
        <div class="row mb-3">
            <div class="col-md-8 offset-md-2">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" value="{{ $user->name }}" disabled>
                            <small class="text-muted">Name cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="">Select Role</option>
                                <option value="registrar" {{ $user->role === 'registrar' ? 'selected' : '' }}>Registrar</option>
                                <option value="clerk" {{ $user->role === 'clerk' ? 'selected' : '' }}>Clerk</option>
                                <option value="citizen" {{ $user->role === 'citizen' ? 'selected' : '' }}>Citizen</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Registrar can create/edit/delete records; Clerk can view/edit records; Citizen has no system access</small>
                        </div>

                        <div class="mb-3">
                            <label for="registration_office_id" class="form-label">Assign Office</label>
                            <select name="registration_office_id" id="registration_office_id" class="form-select @error('registration_office_id') is-invalid @enderror">
                                <option value="">Select Office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ $user->registration_office_id == $office->id ? 'selected' : '' }}>
                                        {{ $office->office_name }} - {{ $office->region }}
                                    </option>
                                @endforeach
                            </select>
                            @error('registration_office_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">User will only see records from their assigned office</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_approved" id="is_approved" value="1" {{ $user->is_approved ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_approved">
                                    <strong>User is Approved</strong>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Uncheck to block user from system access until re-approved</small>
                        </div>

                        <div class="mb-3 p-3 bg-light rounded">
                            <p class="mb-2"><strong>System Admin Status:</strong></p>
                            @if($user->is_system_admin)
                                <span class="badge bg-danger">This user is a System Admin</span>
                                <small class="d-block mt-2 text-muted">System admins have full access to all offices and all records. Use the "Remove Admin" button on the user list to change this.</small>
                            @else
                                <span class="badge bg-secondary">This user is NOT a System Admin</span>
                                <small class="d-block mt-2 text-muted">Regular users can only access their assigned office. Use the "Make Admin" button on the user list to promote this user.</small>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
