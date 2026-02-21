@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h2 class="mb-4">Edit Certificate</h2>

            <form action="{{ route('certificates.update', $certificate) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Category Tabs -->
                <ul class="nav nav-tabs mb-4" id="recordTypeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $certificate->record_type === 'birth' ? 'active' : '' }}" id="birth-tab" data-bs-toggle="tab" data-bs-target="#birth-panel" type="button" role="tab">
                            Birth Records
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $certificate->record_type === 'marriage' ? 'active' : '' }}" id="marriage-tab" data-bs-toggle="tab" data-bs-target="#marriage-panel" type="button" role="tab">
                            Marriage Records
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $certificate->record_type === 'death' ? 'active' : '' }}" id="death-tab" data-bs-toggle="tab" data-bs-target="#death-panel" type="button" role="tab">
                            Death Records
                        </button>
                    </li>
                </ul>

                <!-- Hidden field for record_type -->
                <input type="hidden" name="record_type" id="record_type" value="{{ old('record_type', $certificate->record_type) }}">
                <input type="hidden" name="record_id" id="record_id" value="{{ old('record_id', $certificate->record_id) }}" required>

                <div class="tab-content" id="recordTypeTabContent">
                    <!-- BIRTH RECORDS TAB -->
                    <div class="tab-pane fade {{ $certificate->record_type === 'birth' ? 'show active' : '' }}" id="birth-panel" role="tabpanel">
                        <div class="mb-4">
                            <label class="form-label"><strong>Select Birth Record (Search by Certificate # or Name)</strong></label>
                            <input type="text" class="form-control mb-2" id="birth_search" placeholder="Type certificate # or name...">
                            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                                <div id="birth_list">
                                    @forelse($birthRecords as $record)
                                        <div class="form-check birth-option mb-2" data-cert="{{ $record->birth_certificate_no }}" data-name="{{ $record->child_first_name }} {{ $record->child_last_name }}">
                                            <input class="form-check-input record-radio" type="radio" name="birth_record_id" id="birth_{{ $record->id }}" value="{{ $record->id }}" data-cert-no="{{ $record->birth_certificate_no }}" data-type="birth" {{ old('record_id', $certificate->record_id) == $record->id && $certificate->record_type === 'birth' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="birth_{{ $record->id }}">
                                                <strong style="color: #198754;">{{ $record->birth_certificate_no }}</strong>
                                                <span style="color: #333;">- {{ $record->child_first_name }} {{ $record->child_last_name }}</span>
                                                <span style="color: #6c757d; font-size: 0.9em;">(DOB: {{ \Carbon\Carbon::parse($record->date_of_birth)->format('d/m/Y') }})</span>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted">No registered birth records found.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MARRIAGE RECORDS TAB -->
                    <div class="tab-pane fade {{ $certificate->record_type === 'marriage' ? 'show active' : '' }}" id="marriage-panel" role="tabpanel">
                        <div class="mb-4">
                            <label class="form-label"><strong>Select Marriage Record (Search by Certificate # or Names)</strong></label>
                            <input type="text" class="form-control mb-2" id="marriage_search" placeholder="Type certificate # or names...">
                            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                                <div id="marriage_list">
                                    @forelse($marriageRecords as $record)
                                        <div class="form-check marriage-option mb-2"
                                             data-cert="{{ $record->marriage_certificate_no }}"
                                             data-name="{{ $record->groom->child_first_name ?? '' }} {{ $record->groom->child_last_name ?? '' }} {{ $record->bride->child_first_name ?? '' }} {{ $record->bride->child_last_name ?? '' }}">
                                            <input class="form-check-input record-radio" type="radio" name="marriage_record_id" id="marriage_{{ $record->id }}" value="{{ $record->id }}" data-cert-no="{{ $record->marriage_certificate_no }}" data-type="marriage" {{ old('record_id', $certificate->record_id) == $record->id && $certificate->record_type === 'marriage' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="marriage_{{ $record->id }}">
                                                <strong style="color: #0d6efd;">{{ $record->marriage_certificate_no }}</strong>
                                                <span style="color: #333;">- {{ $record->groom->child_first_name ?? 'N/A' }} {{ $record->groom->child_last_name ?? '' }} & {{ $record->bride->child_first_name ?? 'N/A' }} {{ $record->bride->child_last_name ?? '' }}</span>
                                                <span style="color: #6c757d; font-size: 0.9em;">({{ \Carbon\Carbon::parse($record->date_of_marriage)->format('d/m/Y') }})</span>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted">No registered marriage records found.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DEATH RECORDS TAB -->
                    <div class="tab-pane fade {{ $certificate->record_type === 'death' ? 'show active' : '' }}" id="death-panel" role="tabpanel">
                        <div class="mb-4">
                            <label class="form-label"><strong>Select Death Record (Search by Certificate # or Name)</strong></label>
                            <input type="text" class="form-control mb-2" id="death_search" placeholder="Type certificate # or name...">
                            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                                <div id="death_list">
                                    @forelse($deathRecords as $record)
                                        <div class="form-check death-option mb-2" data-cert="{{ $record->death_certificate_no }}" data-name="{{ $record->deceased->child_first_name ?? '' }} {{ $record->deceased->child_last_name ?? '' }}">
                                            <input class="form-check-input record-radio" type="radio" name="death_record_id" id="death_{{ $record->id }}" value="{{ $record->id }}" data-cert-no="{{ $record->death_certificate_no }}" data-type="death" {{ old('record_id', $certificate->record_id) == $record->id && $certificate->record_type === 'death' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="death_{{ $record->id }}">
                                                <strong style="color: #dc3545;">{{ $record->death_certificate_no }}</strong>
                                                <span style="color: #333;">- {{ $record->deceased->child_first_name ?? 'N/A' }} {{ $record->deceased->child_last_name ?? '' }}</span>
                                                <span style="color: #6c757d; font-size: 0.9em;">({{ \Carbon\Carbon::parse($record->date_of_death)->format('d/m/Y') }})</span>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted">No registered death records found.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Certificate Number (Auto-filled from selection) -->
                <div class="mb-3">
                    <label for="certificate_number" class="form-label">Certificate Number</label>
                    <input type="text" class="form-control @error('certificate_number') is-invalid @enderror"
                        id="certificate_number" name="certificate_number" value="{{ old('certificate_number', $certificate->certificate_number) }}" readonly required>
                    <small class="text-muted">Auto-filled from selected record</small>
                    @error('certificate_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="issued_by" class="form-label">Issued By</label>
                    <input type="text" class="form-control @error('issued_by') is-invalid @enderror"
                        id="issued_by" name="issued_by" value="{{ old('issued_by', $certificate->issued_by) }}" required>
                    @error('issued_by')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="issue_date" class="form-label">Issue Date</label>
                    <input type="date" class="form-control @error('issue_date') is-invalid @enderror"
                        id="issue_date" name="issue_date" value="{{ old('issue_date', $certificate->issue_date ? \Carbon\Carbon::parse($certificate->issue_date)->format('Y-m-d') : '') }}" required>
                    @error('issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="copies_issued" class="form-label">Copies Issued</label>
                    <input type="number" class="form-control @error('copies_issued') is-invalid @enderror"
                        id="copies_issued" name="copies_issued" value="{{ old('copies_issued', $certificate->copies_issued) }}" min="1" required>
                    @error('copies_issued')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="issued" {{ old('status', $certificate->status) === 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="cancelled" {{ old('status', $certificate->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="renewed" {{ old('status', $certificate->status) === 'renewed' ? 'selected' : '' }}>Renewed</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Update Certificate</button>
                    <a href="{{ route('certificates.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Birth Search
    document.getElementById('birth_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.birth-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Marriage Search
    document.getElementById('marriage_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.marriage-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Death Search
    document.getElementById('death_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.death-option').forEach(option => {
            const cert = option.getAttribute('data-cert').toLowerCase();
            const name = option.getAttribute('data-name').toLowerCase();
            option.style.display = (cert.includes(searchTerm) || name.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });

    // Handle record selection - auto-fill certificate number and set record_id/record_type
    document.querySelectorAll('.record-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const recordId = this.value;
                const certNo = this.getAttribute('data-cert-no');
                const recordType = this.getAttribute('data-type');

                // Set hidden fields
                document.getElementById('record_id').value = recordId;
                document.getElementById('record_type').value = recordType;

                // Auto-fill certificate number
                document.getElementById('certificate_number').value = certNo;
            }
        });
    });

    // Update record_type when switching tabs
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetPanel = event.target.getAttribute('data-bs-target');

            // Clear previous selections (except current one)
            document.querySelectorAll('.record-radio').forEach(r => {
                if (!r.checked || r.getAttribute('data-type') !== document.getElementById('record_type').value) {
                    r.checked = false;
                }
            });

            // Set record type based on active tab
            if (targetPanel === '#birth-panel') {
                document.getElementById('record_type').value = 'birth';
            } else if (targetPanel === '#marriage-panel') {
                document.getElementById('record_type').value = 'marriage';
            } else if (targetPanel === '#death-panel') {
                document.getElementById('record_type').value = 'death';
            }
        });
    });
</script>
@endsection
