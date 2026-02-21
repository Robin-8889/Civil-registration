@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-4">Add Registration Office</h2>

            <form action="{{ route('registration_offices.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="office_name" class="form-label">Office Name</label>
                    <input type="text" class="form-control @error('office_name') is-invalid @enderror"
                        id="office_name" name="office_name" value="{{ old('office_name') }}" required>
                    @error('office_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control @error('location') is-invalid @enderror"
                        id="location" name="location" value="{{ old('location') }}" required>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="region" class="form-label">Region <small class="text-muted">(Search or select)</small></label>
                    <select class="form-select @error('region') is-invalid @enderror"
                        id="region" name="region" required>
                        <option value="">-- Select Region --</option>
                        <option value="Arusha" {{ old('region') === 'Arusha' ? 'selected' : '' }}>Arusha</option>
                        <option value="Dar es Salaam" {{ old('region') === 'Dar es Salaam' ? 'selected' : '' }}>Dar es Salaam</option>
                        <option value="Dodoma" {{ old('region') === 'Dodoma' ? 'selected' : '' }}>Dodoma</option>
                        <option value="Geita" {{ old('region') === 'Geita' ? 'selected' : '' }}>Geita</option>
                        <option value="Iringa" {{ old('region') === 'Iringa' ? 'selected' : '' }}>Iringa</option>
                        <option value="Kagera" {{ old('region') === 'Kagera' ? 'selected' : '' }}>Kagera</option>
                        <option value="Katavi" {{ old('region') === 'Katavi' ? 'selected' : '' }}>Katavi</option>
                        <option value="Kigoma" {{ old('region') === 'Kigoma' ? 'selected' : '' }}>Kigoma</option>
                        <option value="Kilimanjaro" {{ old('region') === 'Kilimanjaro' ? 'selected' : '' }}>Kilimanjaro</option>
                        <option value="Lindi" {{ old('region') === 'Lindi' ? 'selected' : '' }}>Lindi</option>
                        <option value="Manyara" {{ old('region') === 'Manyara' ? 'selected' : '' }}>Manyara</option>
                        <option value="Mara" {{ old('region') === 'Mara' ? 'selected' : '' }}>Mara</option>
                        <option value="Mbeya" {{ old('region') === 'Mbeya' ? 'selected' : '' }}>Mbeya</option>
                        <option value="Morogoro" {{ old('region') === 'Morogoro' ? 'selected' : '' }}>Morogoro</option>
                        <option value="Mtwara" {{ old('region') === 'Mtwara' ? 'selected' : '' }}>Mtwara</option>
                        <option value="Mwanza" {{ old('region') === 'Mwanza' ? 'selected' : '' }}>Mwanza</option>
                        <option value="Njombe" {{ old('region') === 'Njombe' ? 'selected' : '' }}>Njombe</option>
                        <option value="Pemba North" {{ old('region') === 'Pemba North' ? 'selected' : '' }}>Pemba North</option>
                        <option value="Pemba South" {{ old('region') === 'Pemba South' ? 'selected' : '' }}>Pemba South</option>
                        <option value="Pwani" {{ old('region') === 'Pwani' ? 'selected' : '' }}>Pwani (Coast)</option>
                        <option value="Rukwa" {{ old('region') === 'Rukwa' ? 'selected' : '' }}>Rukwa</option>
                        <option value="Ruvuma" {{ old('region') === 'Ruvuma' ? 'selected' : '' }}>Ruvuma</option>
                        <option value="Shinyanga" {{ old('region') === 'Shinyanga' ? 'selected' : '' }}>Shinyanga</option>
                        <option value="Simiyu" {{ old('region') === 'Simiyu' ? 'selected' : '' }}>Simiyu</option>
                        <option value="Singida" {{ old('region') === 'Singida' ? 'selected' : '' }}>Singida</option>
                        <option value="Songwe" {{ old('region') === 'Songwe' ? 'selected' : '' }}>Songwe</option>
                        <option value="Tabora" {{ old('region') === 'Tabora' ? 'selected' : '' }}>Tabora</option>
                        <option value="Tanga" {{ old('region') === 'Tanga' ? 'selected' : '' }}>Tanga</option>
                        <option value="Zanzibar North" {{ old('region') === 'Zanzibar North' ? 'selected' : '' }}>Zanzibar North (Unguja North)</option>
                        <option value="Zanzibar South" {{ old('region') === 'Zanzibar South' ? 'selected' : '' }}>Zanzibar South (Unguja South)</option>
                        <option value="Zanzibar West" {{ old('region') === 'Zanzibar West' ? 'selected' : '' }}>Zanzibar West (Unguja West)</option>
                    </select>
                    @error('region')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="district" class="form-label">District</label>
                    <input type="text" class="form-control @error('district') is-invalid @enderror"
                        id="district" name="district" value="{{ old('district') }}" required>
                    @error('district')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror"
                        id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                        id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Office</button>
                    <a href="{{ route('registration_offices.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 with search functionality
        $('#region').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search or select a region',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
