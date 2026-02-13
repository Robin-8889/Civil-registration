@extends('layouts.app')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <hr>

                    <p class="text-center mb-0">
                        Don't have an account? <a href="{{ route('register') }}">Register here</a>
                    </p>
                </div>
            </div>

            <!-- Default test credentials info -->
            <div class="alert alert-info mt-4">
                <strong>Test Credentials:</strong>
                <ul class="mb-0">
                    <li>Email: <code>admin@civilreg.tz</code> | Password: <code>password123</code></li>
                    <li>Email: <code>registrar@dar.tz</code> | Password: <code>password123</code></li>
                    <li>Email: <code>clerk@arusha.tz</code> | Password: <code>password123</code></li>
                    <li>Email: <code>citizen@example.tz</code> | Password: <code>password123</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
