@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Birth Record Details</h4>
                    <div>
                        <a href="{{ route('birth_records.edit', $birthRecord) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('birth_records.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Birth Certificate Number</h6>
                            <p class="h5">{{ $birthRecord->birth_certificate_no }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <p>
                                @if($birthRecord->status === 'registered')
                                    <span class="badge bg-success">Registered</span>
                                @elseif($birthRecord->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Child Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <h6 class="text-muted">First Name</h6>
                            <p>{{ $birthRecord->child_first_name }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Middle Name</h6>
                            <p>{{ $birthRecord->child_middle_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Last Name</h6>
                            <p>{{ $birthRecord->child_last_name }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <h6 class="text-muted">Nationality</h6>
                            <p>{{ $birthRecord->nationality ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Date of Birth</h6>
                            <p>{{ \Carbon\Carbon::parse($birthRecord->date_of_birth)->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Gender</h6>
                            <p>{{ $birthRecord->gender === 'M' ? 'Male' : 'Female' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Place of Birth</h6>
                            <p>{{ $birthRecord->place_of_birth }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Parent Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Father's Name</h6>
                            <p>{{ $birthRecord->father_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Mother's Name</h6>
                            <p>{{ $birthRecord->mother_name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Registration Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Registration Office</h6>
                            <p>{{ $birthRecord->office->office_name ?? 'N/A' }}</p>
                            <p class="text-muted small">{{ $birthRecord->office->region ?? '' }}, {{ $birthRecord->office->district ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Registration Date</h6>
                            <p>{{ $birthRecord->registration_date }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Created At</h6>
                            <p>{{ $birthRecord->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Last Updated</h6>
                            <p>{{ $birthRecord->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <form action="{{ route('birth_records.destroy', $birthRecord->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this birth record?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Record</button>
                    </form>
                    <a href="{{ route('birth_records.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
