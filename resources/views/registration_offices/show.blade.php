@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card office-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Registration Office Details</h4>
                    <div>
                        <a href="{{ route('registration_offices.edit', $office) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('registration_offices.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Office Name</h6>
                            <p class="h5">{{ $office->office_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <p>
                                @if($office->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Location Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Location</h6>
                            <p>{{ $office->location }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Region</h6>
                            <p>{{ $office->region }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">District</h6>
                            <p>{{ $office->district }}</p>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Address</h6>
                            <p>{{ $office->address ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Contact Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Phone</h6>
                            <p>
                                @if($office->phone)
                                    <a href="tel:{{ $office->phone }}">{{ $office->phone }}</a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Email</h6>
                            <p>
                                @if($office->email)
                                    <a href="mailto:{{ $office->email }}">{{ $office->email }}</a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted small">
                        <strong>Created:</strong> {{ $office->created_at->format('d M Y H:i') }}<br>
                        <strong>Updated:</strong> {{ $office->updated_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
