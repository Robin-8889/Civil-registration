@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Death Record Details</h4>
                    <div>
                        <a href="{{ route('death_records.edit', $deathRecord) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('death_records.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Death Certificate Number</h6>
                            <p class="h5">{{ $deathRecord->death_certificate_no }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <p>
                                @if($deathRecord->status === 'registered')
                                    <span class="badge bg-success">Registered</span>
                                @elseif($deathRecord->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Deceased Person</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Name</h6>
                            <p>{{ $deathRecord->deceased->child_first_name }} {{ $deathRecord->deceased->child_last_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Birth Certificate #</h6>
                            <p>{{ $deathRecord->deceased->birth_certificate_no }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Nationality</h6>
                            <p>{{ $deathRecord->deceased->nationality ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Date of Birth</h6>
                            <p>{{ \Carbon\Carbon::parse($deathRecord->deceased->date_of_birth)->format('d M Y') }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Death Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Date of Death</h6>
                            <p>{{ \Carbon\Carbon::parse($deathRecord->date_of_death)->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Place of Death</h6>
                            <p>{{ $deathRecord->place_of_death }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Cause of Death</h6>
                            <p>{{ $deathRecord->cause_of_death ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Informant Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Informant Name</h6>
                            <p>{{ $deathRecord->informant_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Relationship</h6>
                            <p>{{ $deathRecord->informant_relation ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @if($deathRecord->informant)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Informant Birth Certificate #</h6>
                            <p>{{ $deathRecord->informant->birth_certificate_no }}</p>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <h5 class="mb-3">Registration Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Registration Office</h6>
                            <p>{{ $deathRecord->office->office_name ?? 'N/A' }}</p>
                            <p class="text-muted small">{{ $deathRecord->office->region ?? '' }}, {{ $deathRecord->office->district ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Registration Date</h6>
                            <p>{{ $deathRecord->registration_date }}</p>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted small">
                        <strong>Created:</strong> {{ $deathRecord->created_at->format('d M Y H:i') }}<br>
                        <strong>Updated:</strong> {{ $deathRecord->updated_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
