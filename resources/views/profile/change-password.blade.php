@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header" style="background-color: #DCE4C9;">
                    <h4 class="mb-0">Change Password</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update-password') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                id="current_password"
                                name="current_password"
                                required>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password"
                                class="form-control @error('new_password') is-invalid @enderror"
                                id="new_password"
                                name="new_password"
                                required>
                            @error('new_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                Password should be at least 8 characters with a mix of letters, numbers, and symbols.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password"
                                class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                id="new_password_confirmation"
                                name="new_password_confirmation"
                                required>
                            @error('new_password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Requirements Box -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Password Requirements</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>At least 8 characters long</li>
                        <li>Contains uppercase letters (A-Z)</li>
                        <li>Contains lowercase letters (a-z)</li>
                        <li>Contains numbers (0-9)</li>
                        <li>Contains special characters (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
