@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Certificate Details</h4>
                    <div>
                        <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-success btn-sm" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            Download
                        </a>
                        <a href="{{ route('certificates.edit', $certificate) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('certificates.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Certificate Number</h6>
                            <p class="h5">{{ $certificate->certificate_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <p>
                                @if($certificate->status === 'issued')
                                    <span class="badge bg-success">Issued</span>
                                @elseif($certificate->status === 'renewed')
                                    <span class="badge bg-info">Renewed</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Related Record</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Record Type</h6>
                            <p class="text-capitalize">{{ $certificate->record_type }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Record ID</h6>
                            <p>{{ $certificate->record_id }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Issuance Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Issued By</h6>
                            <p>{{ $certificate->issued_by }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Issue Date</h6>
                            <p>{{ \Carbon\Carbon::parse($certificate->issue_date)->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Copies Issued</h6>
                            <p>{{ $certificate->copies_issued }}</p>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted small">
                        <strong>Created:</strong> {{ $certificate->created_at->format('d M Y H:i') }}<br>
                        <strong>Updated:</strong> {{ $certificate->updated_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
