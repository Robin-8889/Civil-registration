@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Birth Records</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('birth_records.create') }}" class="btn btn-primary">Register Birth</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Certificate No</th>
                    <th>Child Name</th>
                    <th>Date of Birth</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>{{ $record->birth_certificate_no }}</td>
                    <td>{{ $record->child_first_name }} {{ $record->child_last_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->date_of_birth)->format('d/m/Y') }}</td>
                    <td><span class="badge bg-{{ $record->status == 'registered' ? 'success' : 'warning' }}">{{ ucfirst($record->status) }}</span></td>
                    <td>
                        <a href="{{ route('birth_records.show', $record) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('birth_records.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No birth records found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</div>
@endsection
