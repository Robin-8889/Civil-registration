@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Marriage Record Details</h4>
                    <div>
                        <a href="{{ route('marriage_records.edit', $marriageRecord) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('marriage_records.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Marriage Certificate Number</h6>
                            <p class="h5">{{ $marriageRecord->marriage_certificate_no }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <p>
                                @if($marriageRecord->status === 'registered')
                                    <span class="badge bg-success">Registered</span>
                                @elseif($marriageRecord->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Groom Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Name</h6>
                            <p>{{ $marriageRecord->groom->child_first_name }} {{ $marriageRecord->groom->child_last_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Birth Certificate #</h6>
                            <p>{{ $marriageRecord->groom->birth_certificate_no }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">National ID</h6>
                            <p>{{ $marriageRecord->groom->child->national_id ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Bride Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Name</h6>
                            <p>{{ $marriageRecord->bride->child_first_name }} {{ $marriageRecord->bride->child_last_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Birth Certificate #</h6>
                            <p>{{ $marriageRecord->bride->birth_certificate_no }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">National ID</h6>
                            <p>{{ $marriageRecord->bride->child->national_id ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Marriage Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Date of Marriage</h6>
                            <p>{{ \Carbon\Carbon::parse($marriageRecord->date_of_marriage)->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Place of Marriage</h6>
                            <p>{{ $marriageRecord->place_of_marriage }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Witnesses</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Witness 1</h6>
                            <p>{{ $marriageRecord->witness1_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Witness 2</h6>
                            <p>{{ $marriageRecord->witness2_name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Registration Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Registration Office</h6>
                            <p>{{ $marriageRecord->office->office_name ?? 'N/A' }}</p>
                            <p class="text-muted small">{{ $marriageRecord->office->region ?? '' }}, {{ $marriageRecord->office->district ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Registration Date</h6>
                            <p>{{ $marriageRecord->registration_date }}</p>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted small">
                        <strong>Created:</strong> {{ $marriageRecord->created_at->format('d M Y H:i') }}<br>
                        <strong>Updated:</strong> {{ $marriageRecord->updated_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
