@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Marriage Records</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('marriage_records.create') }}" class="btn btn-primary">
                Register Marriage
            </a>
        </div>
    </div>

    @if($records->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Certificate #</th>
                        <th>Groom Name</th>
                        <th>Bride Name</th>
                        <th>Marriage Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        <tr>
                            <td><strong>{{ $record->marriage_certificate_no }}</strong></td>
                            <td>{{ $record->groom->child_first_name ?? 'N/A' }} {{ $record->groom->child_last_name ?? '' }}</td>
                            <td>{{ $record->bride->child_first_name ?? 'N/A' }} {{ $record->bride->child_last_name ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->date_of_marriage)->format('d M Y') }}</td>
                            <td>
                                @if($record->status === 'registered')
                                    <span class="badge bg-success">Registered</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('marriage_records.show', $record) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('marriage_records.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
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
            No marriage records found. <a href="{{ route('marriage_records.create') }}">Create one now</a>
        </div>
    @endif
</div>
@endsection
