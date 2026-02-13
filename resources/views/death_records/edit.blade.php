@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h2 class="mb-4">Edit Death Record</h2>

            <form action="{{ route('death_records.update', $deathRecord) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="alert alert-info">
                    <strong>Death Certificate Number:</strong> {{ $deathRecord->death_certificate_no }}
                </div>

                <!-- DECEASED PERSON SECTION -->
                <div class="mb-4">
                    <label class="form-label"><strong>Deceased Person (Search by Birth Certificate # or Name)</strong></label>
                    <input type="text" class="form-control mb-2" id="deceased_search" placeholder="Type birth cert # or name...">
                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                        <div id="deceased_list">
                            @foreach($birthRecords as $record)
                                <div class="form-check deceased-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <input class="form-check-input" type="radio" name="deceased_birth_id" id="deceased_{{ $record->id }}" value="{{ $record->id }}" {{ old('deceased_birth_id', $deathRecord->deceased_birth_id) == $record->id ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="deceased_{{ $record->id }}">
                                        <strong style="color: #dc3545;">{{ $record->birth_certificate_no }}</strong>
                                        <span style="color: #333;">- {{ $record->child_first_name }} {{ $record->child_last_name }}</span>
                                        <span style="color: #6c757d; font-size: 0.9em;">({{ $record->child->national_id ?? 'N/A' }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('deceased_birth_id')
                        <div class="alert alert-danger mt-2 mb-0">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="date_of_death" class="form-label">Date of Death</label>
                    <input type="date" class="form-control @error('date_of_death') is-invalid @enderror"
                        id="date_of_death" name="date_of_death" value="{{ old('date_of_death', $deathRecord->date_of_death) }}" required>
                    @error('date_of_death')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="place_of_death" class="form-label">Place of Death</label>
                    <input type="text" class="form-control @error('place_of_death') is-invalid @enderror"
                        id="place_of_death" name="place_of_death" value="{{ old('place_of_death', $deathRecord->place_of_death) }}" required>
                    @error('place_of_death')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cause_of_death" class="form-label">Cause of Death</label>
                    <input type="text" class="form-control @error('cause_of_death') is-invalid @enderror"
                        id="cause_of_death" name="cause_of_death" value="{{ old('cause_of_death', $deathRecord->cause_of_death) }}" required>
                    @error('cause_of_death')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>
                <h5>Informant Information (Optional - Select from Birth Records or Enter Manually)</h5>

                <!-- INFORMANT SECTION -->
                <div class="mb-4">
                    <label class="form-label"><strong>Informant (Search by Birth Certificate # or Name)</strong></label>
                    <input type="text" class="form-control mb-2" id="informant_search" placeholder="Type birth cert # or name...">
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                        <div id="informant_list">
                            @foreach($birthRecords as $record)
                                <div class="form-check informant-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                    <input class="form-check-input" type="radio" name="informant_birth_id" id="informant_{{ $record->id }}" value="{{ $record->id }}" data-informant-name="{{ $record->child_first_name }} {{ $record->child_last_name }}" {{ old('informant_birth_id', $deathRecord->informant_birth_id) == $record->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="informant_{{ $record->id }}">
                                        <strong style="color: #0d6efd;">{{ $record->birth_certificate_no }}</strong>
                                        <span style="color: #333;">- {{ $record->child_first_name }} {{ $record->child_last_name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Or enter informant details manually:</small>
                    <input type="text" class="form-control mt-2 @error('informant_name') is-invalid @enderror"
                        id="informant_name" name="informant_name" value="{{ old('informant_name', $deathRecord->informant_name) }}" placeholder="Enter informant name">
                    @error('informant_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="informant_relation" class="form-label">Informant Relationship to Deceased</label>
                    <input type="text" class="form-control @error('informant_relation') is-invalid @enderror"
                        id="informant_relation" name="informant_relation" value="{{ old('informant_relation', $deathRecord->informant_relation) }}" placeholder="e.g., Son, Daughter, Spouse, etc.">
                    @error('informant_relation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="registration_office_id" class="form-label">Registration Office</label>
                    <select class="form-select @error('registration_office_id') is-invalid @enderror"
                        id="registration_office_id" name="registration_office_id" required>
                        <option value="">-- Select Office --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ old('registration_office_id', $deathRecord->registration_office_id) == $office->id ? 'selected' : '' }}>
                                {{ $office->office_name }} ({{ $office->region }})
                            </option>
                        @endforeach
                    </select>
                    @error('registration_office_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="registered" {{ old('status', $deathRecord->status) == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="pending" {{ old('status', $deathRecord->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ old('status', $deathRecord->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Update Death Record</button>
                    <a href="{{ route('death_records.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Deceased Person Search
    document.getElementById('deceased_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.deceased-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Informant Search
    document.getElementById('informant_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.informant-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Auto-fill informant name from birth records
    document.querySelectorAll('input[name="informant_birth_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('informant_name').value = this.getAttribute('data-informant-name');
            }
        });
    });
</script>
@endsection
