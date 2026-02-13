@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Create New Account</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('register.post') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required>
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

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                id="password_confirmation" name="password_confirmation" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Your Regional Office</label>

                            <!-- Search Box -->
                            <div class="mb-2">
                                <input type="text" id="regionSearch" class="form-control form-control-sm"
                                    placeholder="🔍 Search region..." autocomplete="off">
                            </div>

                            <!-- Region Selection Panel -->
                            <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                                <div id="regionList" class="region-options">
                                    <!-- Will be populated by JavaScript -->
                                    <p class="text-muted text-center">Loading regions...</p>
                                </div>
                            </div>

                            <!-- Hidden input for selected region -->
                            <input type="hidden" id="registration_office_id" name="registration_office_id" value="{{ old('registration_office_id') }}">

                            <!-- Selected region display -->
                            <div class="mt-2">
                                <small class="text-muted">Selected: <span id="selectedRegion" class="badge bg-info">None</span></small>
                            </div>

                            @error('registration_office_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create Account</button>
                    </form>

                    <hr>

                    <p class="text-center mb-0">
                        Already have an account? <a href="{{ route('login') }}">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const offices = @json($offices ?? []);
    let selectedOffice = null;

    function renderRegions(filter = '') {
        const list = document.getElementById('regionList');
        const filtered = offices.filter(office =>
            office.region.toLowerCase().includes(filter.toLowerCase()) ||
            office.office_name.toLowerCase().includes(filter.toLowerCase())
        );

        if (filtered.length === 0) {
            list.innerHTML = '<p class="text-muted text-center">No regions found</p>';
            return;
        }

        list.innerHTML = filtered.map(office => `
            <div class="form-check p-2 border-bottom">
                <input class="form-check-input region-option" type="radio" name="region"
                    id="office_${office.id}" value="${office.id}"
                    data-region="${office.region}" data-office="${office.office_name}">
                <label class="form-check-label w-100" for="office_${office.id}">
                    <strong>${office.region}</strong><br>
                    <small class="text-muted">${office.office_name}</small>
                </label>
            </div>
        `).join('');

        // Add event listeners
        document.querySelectorAll('.region-option').forEach(option => {
            option.addEventListener('change', function() {
                document.getElementById('registration_office_id').value = this.value;
                document.getElementById('selectedRegion').textContent = this.dataset.region;
                selectedOffice = {id: this.value, region: this.dataset.region, name: this.dataset.office};
            });
        });

        // Restore previous selection if exists
        const savedId = document.getElementById('registration_office_id').value;
        if (savedId) {
            const option = document.getElementById('office_' + savedId);
            if (option) {
                option.checked = true;
                option.dispatchEvent(new Event('change'));
            }
        }
    }

    document.getElementById('regionSearch').addEventListener('input', function(e) {
        renderRegions(e.target.value);
    });

    // Initial render
    document.addEventListener('DOMContentLoaded', function() {
        renderRegions();
    });
</script>
@endpush

@endsection
