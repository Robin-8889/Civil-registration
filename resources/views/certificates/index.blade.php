@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Certificates</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('certificates.create') }}" class="btn btn-primary">
                Issue Certificate
            </a>
        </div>
    </div>

    @if($records->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Certificate ID</th>
                        <th>Record Type</th>
                        <th>Issue Date</th>
                        <th>Copies Issued</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        <tr>
                            <td><strong>#{{ $record->id }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($record->record_type) }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($record->issue_date)->format('d M Y') }}</td>
                            <td><span class="badge bg-info">{{ $record->copies_issued }}</span></td>
                            <td>
                                @if($record->status === 'issued')
                                    <span class="badge bg-success">Issued</span>
                                @elseif($record->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('certificates.show', $record) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('certificates.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $records->links() }}
        </div>
    @else
        <div class="alert alert-info">
            No certificates found. <a href="{{ route('certificates.create') }}">Issue one now</a>
        </div>
    @endif
</div>
@endsection
