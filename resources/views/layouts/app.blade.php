<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tanzania Civil Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #BFC9D1;
        }
        nav {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
        }
        .main-content {
            min-height: calc(100vh - 120px);
        }
        footer {
            background-color: #BFC9D1;
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Card Hover Effect */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Button Animations */
        .btn {
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Gradient Background Option */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Card Header Enhancement */
        .card-header {
            font-weight: 600;
        }

        /* Login Card Header Custom Color */
        .login-card-header {
            background-color: #DCE4C9 !important;
            color: #2d3436 !important;
        }

        /* Login Card Background Custom Color */
        .login-card {
            background-color: #DCE4C9 !important;
            border: none;
        }

        .login-card .card-body {
            background-color: #DCE4C9;
        }

        /* Keep form elements with default styling */
        .login-card .form-control,
        .login-card .form-control:focus {
            background-color: #ffffff;
            color: #2d3436;
        }

        .login-card .btn-primary {
            background-color: #0b7285;
            border-color: #0b7285;
        }

        .login-card .btn-primary:hover {
            background-color: #094d60;
            border-color: #094d60;
        }

        .login-card .form-check-input {
            background-color: #ffffff;
            border-color: #dee2e6;
        }

        .login-card hr {
            border-color: rgba(45, 52, 54, 0.15);
        }

        .login-card .text-center {
            color: #2d3436;
        }

        .login-card a {
            color: #0b7285;
            text-decoration: none;
        }

        .login-card a:hover {
            text-decoration: underline;
        }

        /* Office card styling */
        .office-card {
            background-color: #EAEFEF;
        }

        /* Dashboard box styling */
        .dashboard-box {
            background-color: #E1D9BC;
        }

        /* Responsive font sizing */
        @media (max-width: 768px) {
            .display-4 {
                font-size: 2rem;
            }
        }

        /* Pill-style navbar links */
        .nav-pill-link {
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            margin: 0 0.2rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Light navbar pill links */
        .navbar-light-bg .nav-pill-link {
            color: #1A4D2E !important;
            background-color: rgba(26, 77, 46, 0.12);
        }
        .navbar-light-bg .nav-pill-link:hover,
        .navbar-light-bg .nav-pill-link:focus {
            color: #1A4D2E;
            background-color: rgba(26, 77, 46, 0.22);
        }
        .navbar-light-bg .nav-pill-link.active {
            color: #1A4D2E;
            background-color: rgba(26, 77, 46, 0.3);
        }

        /* Dark navbar pill links (fallback) */
        .navbar-dark .nav-pill-link {
            color: #1f2a37;
            background-color: #FFF5E0;
        }
        .navbar-dark .nav-pill-link:hover,
        .navbar-dark .nav-pill-link:focus {
            color: #1f2a37;
            background-color: #ffe8bd;
        }
        .navbar-dark .nav-pill-link.active {
            color: #1f2a37;
            background-color: #ffe1a3;
        }

        /* Navbar custom background */
        .navbar-light-bg {
            background-color: #F6F0D7 !important;
        }

        .navbar-light-bg .navbar-brand {
            color: #1A4D2E !important;
            font-weight: bold;
        }

        .navbar-light-bg .navbar-toggler {
            border-color: rgba(26, 77, 46, 0.3);
        }

        .navbar-light-bg .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%231A4D2E' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Dropdown styling for light navbar */
        .navbar-light-bg .dropdown-menu {
            border-color: #1A4D2E;
        }

        .navbar-light-bg .dropdown-item {
            color: #1A4D2E;
        }

        .navbar-light-bg .dropdown-item:hover,
        .navbar-light-bg .dropdown-item:focus {
            background-color: rgba(26, 77, 46, 0.1);
            color: #1A4D2E;
        }

        /* Login/Register pill buttons */
        .nav-pill-button {
            border-radius: 999px;
            padding: 0.4rem 0.95rem;
            margin: 0 0.2rem;
            transition: background-color 0.2s ease, color 0.2s ease;
            background-color: #1A4D2E;
            color: #F6F0D7 !important;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }

        .nav-pill-button:hover,
        .nav-pill-button:focus {
            background-color: #0f3620;
            color: #F6F0D7 !important;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light-bg">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                📋 Civil Registration System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link" href="{{ route('citizens.index') }}">Citizens</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-pill-link dropdown-toggle" href="#" id="recordsDropdown" role="button" data-bs-toggle="dropdown">
                                Records
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('birth_records.index') }}">Birth Records</a></li>
                                <li><a class="dropdown-item" href="{{ route('marriage_records.index') }}">Marriage Records</a></li>
                                <li><a class="dropdown-item" href="{{ route('death_records.index') }}">Death Records</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('certificates.index') }}">Certificates</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link" href="{{ route('registration_offices.index') }}">Offices</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-pill-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.change-password') }}">Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link nav-pill-button" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill-button" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">


        @if(session('error'))
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Tanzania Civil Registration System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>
                        <a href="#">Terms</a> |
                        <a href="#">Privacy</a> |
                        <a href="#">Support</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
