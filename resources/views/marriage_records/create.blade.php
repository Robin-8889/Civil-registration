@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h2 class="mb-4">Register Marriage</h2>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Error:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('marriage_records.store') }}" method="POST">
                @csrf

                <div class="alert alert-info">
                    <strong>Marriage Certificate Number:</strong> Will be auto-generated in format MA-YYYY-XXXXX
                </div>

                <!-- GROOM SECTION -->
                <div class="mb-4">
                    <label class="form-label"><strong>Groom (Search by Birth Certificate # or Name)</strong></label>
                    <input type="text" class="form-control mb-2" id="groom_search" placeholder="Type birth cert # or name...">
                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                        <div id="groom_list">
                            @foreach($grooms as $record)
                                <div class="form-check groom-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <input class="form-check-input" type="radio" name="groom_id" id="groom_{{ $record->id }}" value="{{ $record->id }}" {{ old('groom_id') == $record->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="groom_{{ $record->id }}">
                                        <strong style="color: #0d6efd;">{{ $record->birth_certificate_no }}</strong>
                                        <span style="color: #333;">- {{ $record->child_first_name }} {{ $record->child_last_name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('groom_id')
                        <div class="alert alert-danger mt-2 mb-0">{{ $message }}</div>
                    @enderror
                </div>

                <!-- BRIDE SECTION -->
                <div class="mb-4">
                    <label class="form-label"><strong>Bride (Search by Birth Certificate # or Name)</strong></label>
                    <input type="text" class="form-control mb-2" id="bride_search" placeholder="Type birth cert # or name...">
                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                        <div id="bride_list">
                            @foreach($brides as $record)
                                <div class="form-check bride-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <input class="form-check-input" type="radio" name="bride_id" id="bride_{{ $record->id }}" value="{{ $record->id }}" {{ old('bride_id') == $record->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bride_{{ $record->id }}">
                                        <strong style="color: #0d6efd;">{{ $record->birth_certificate_no }}</strong>
                                        <span style="color: #333;">- {{ $record->child_first_name }} {{ $record->child_last_name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('bride_id')
                        <div class="alert alert-danger mt-2 mb-0">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="date_of_marriage" class="form-label">Date of Marriage</label>
                    <input type="date" class="form-control @error('date_of_marriage') is-invalid @enderror"
                        id="date_of_marriage" name="date_of_marriage" value="{{ old('date_of_marriage') }}" required>
                    @error('date_of_marriage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="place_of_marriage" class="form-label">Place of Marriage</label>
                    <input type="text" class="form-control @error('place_of_marriage') is-invalid @enderror"
                        id="place_of_marriage" name="place_of_marriage" value="{{ old('place_of_marriage') }}" required>
                    @error('place_of_marriage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="registration_office_id" class="form-label">Registration Office</label>
                    <select class="form-select @error('registration_office_id') is-invalid @enderror"
                        id="registration_office_id" name="registration_office_id" required>
                        <option value="">-- Select Office --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ old('registration_office_id', $userOfficeId) == $office->id ? 'selected' : '' }}>
                                {{ $office->office_name }} ({{ $office->region }})
                            </option>
                        @endforeach
                    </select>
                    @error('registration_office_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>
                <h5>Witnesses (Optional - Select from Birth Records)</h5>

                <!-- WITNESS 1 SECTION -->
                <div class="mb-4">
                    <label class="form-label"><strong>Witness 1 (Search by Birth Certificate # or Name)</strong></label>
                    <input type="text" class="form-control mb-2" id="witness1_search" placeholder="Type birth cert # or name...">
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                        <div id="witness1_list">
                            @foreach($birthRecords as $record)
                                <div class="form-check witness1-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <input class="form-check-input" type="radio" name="witness1_birth_id" id="witness1_{{ $record->id }}" value="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <label class="form-check-label" for="witness1_{{ $record->id }}">
                                        <strong style="color: #0d6efd;">{{ $record->birth_certificate_no }}</strong> - {{ $record->child_first_name }} {{ $record->child_last_name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="witness1_name" name="witness1_name" value="{{ old('witness1_name') }}">
                </div>

                <!-- WITNESS 2 SECTION -->
                <div class="mb-4">
                    <label class="form-label"><strong>Witness 2 (Search by Birth Certificate # or Name)</strong></label>
                    <input type="text" class="form-control mb-2" id="witness2_search" placeholder="Type birth cert # or name...">
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                        <div id="witness2_list">
                            @foreach($birthRecords as $record)
                                <div class="form-check witness2-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <input class="form-check-input" type="radio" name="witness2_birth_id" id="witness2_{{ $record->id }}" value="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <label class="form-check-label" for="witness2_{{ $record->id }}">
                                        <strong style="color: #0d6efd;">{{ $record->birth_certificate_no }}</strong> - {{ $record->child_first_name }} {{ $record->child_last_name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="witness2_name" name="witness2_name" value="{{ old('witness2_name') }}">
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Register Marriage</button>
                    <a href="{{ route('marriage_records.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Groom Search
    document.getElementById('groom_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.groom-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Bride Search
    document.getElementById('bride_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.bride-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Get selected groom and bride names
    function getSelectedGroomName() {
        const checkedGroom = document.querySelector('input[name="groom_id"]:checked');
        if (checkedGroom) {
            const groomOption = document.querySelector(`.groom-option input[value="${checkedGroom.value}"]`).closest('.groom-option');
            return groomOption.getAttribute('data-name');
        }
        return null;
    }

    function getSelectedBrideName() {
        const checkedBride = document.querySelector('input[name="bride_id"]:checked');
        if (checkedBride) {
            const brideOption = document.querySelector(`.bride-option input[value="${checkedBride.value}"]`).closest('.bride-option');
            return brideOption.getAttribute('data-name');
        }
        return null;
    }

    // Witness 1 Search
    document.getElementById('witness1_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.witness1-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Witness 2 Search
    document.getElementById('witness2_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.witness2-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Auto-fill witness names and prevent duplicate selection and groom/bride selection
    document.querySelectorAll('input[name="witness1_birth_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('witness1_name').value = this.value;
                updateWitnessAvailability();
            }
        });
    });

    document.querySelectorAll('input[name="witness2_birth_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('witness2_name').value = this.value;
            }
        });
    });

    // Listen for groom and bride selection changes
    document.querySelectorAll('input[name="groom_id"]').forEach(radio => {
        radio.addEventListener('change', updateWitnessAvailability);
    });

    document.querySelectorAll('input[name="bride_id"]').forEach(radio => {
        radio.addEventListener('change', updateWitnessAvailability);
    });

    // Function to disable witnesses that are the groom, bride, or matching the other witness
    function updateWitnessAvailability() {
        const groomName = getSelectedGroomName();
        const brideName = getSelectedBrideName();
        const witness1Value = document.getElementById('witness1_name').value;

        // Update witness1 options - disable if groom or bride
        document.querySelectorAll('.witness1-option').forEach(option => {
            const optionName = option.getAttribute('data-name');
            const isGroom = groomName && optionName === groomName;
            const isBride = brideName && optionName === brideName;

            if (isGroom || isBride) {
                option.style.opacity = '0.5';
                option.style.pointerEvents = 'none';
                option.querySelector('input[type="radio"]').disabled = true;
            } else {
                option.style.opacity = '1';
                option.style.pointerEvents = 'auto';
                option.querySelector('input[type="radio"]').disabled = false;
            }
        });

        // Update witness2 options - disable if groom, bride, or matches witness1
        document.querySelectorAll('.witness2-option').forEach(option => {
            const optionName = option.getAttribute('data-name');
            const isGroom = groomName && optionName === groomName;
            const isBride = brideName && optionName === brideName;
            const isDuplicate = witness1Value && optionName === witness1Value;

            if (isGroom || isBride || isDuplicate) {
                option.style.opacity = '0.5';
                option.style.pointerEvents = 'none';
                option.querySelector('input[type="radio"]').disabled = true;
            } else {
                option.style.opacity = '1';
                option.style.pointerEvents = 'auto';
                option.querySelector('input[type="radio"]').disabled = false;
            }
        });
    }

    // Initialize on page load
    updateWitnessAvailability();
</script>

@endsection
