@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Death Records</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('death_records.create') }}" class="btn btn-primary">
                Record Death
            </a>
        </div>
    </div>

    @if($records->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Certificate #</th>
                        <th>Deceased Name</th>
                        <th>Date of Death</th>
                        <th>Informant</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        <tr>
                            <td><strong>{{ $record->death_certificate_no }}</strong></td>
                            <td>{{ $record->deceased->child_first_name ?? 'N/A' }} {{ $record->deceased->child_last_name ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->date_of_death)->format('d M Y') }}</td>
                            <td>{{ $record->informant_name ?? 'N/A' }}</td>
                            <td>
                                @if($record->status === 'registered')
                                    <span class="badge bg-success">Registered</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('death_records.show', $record) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('death_records.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
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
            No death records found. <a href="{{ route('death_records.create') }}">Create one now</a>
        </div>
    @endif
</div>
@endsection
