@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Registration Offices</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('registration_offices.create') }}" class="btn btn-primary">
                Add Office
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('registration_offices.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" class="form-control" placeholder="Search office, region, district, location" value="{{ $q ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Region</label>
                    <select name="region" class="form-select">
                        <option value="">All Regions</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg }}" {{ ($region ?? '') === $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('registration_offices.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if($records->count())
        <div class="row">
            @foreach($records as $office)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $office->office_name }}</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Location:</strong> {{ $office->location }}</p>
                            <p><strong>Region:</strong> {{ $office->region }}</p>
                            <p><strong>District:</strong> {{ $office->district }}</p>
                            <p><strong>Address:</strong> {{ $office->address }}</p>
                            @if($office->phone)
                                <p><strong>Phone:</strong> <a href="tel:{{ $office->phone }}">{{ $office->phone }}</a></p>
                            @endif
                            @if($office->email)
                                <p><strong>Email:</strong> <a href="mailto:{{ $office->email }}">{{ $office->email }}</a></p>
                            @endif
                            <p>
                                <strong>Status:</strong>
                                @if($office->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('registration_offices.show', $office) }}" class="btn btn-sm btn-info">View Details</a>
                            <a href="{{ route('registration_offices.edit', $office) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('registration_offices.destroy', $office) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $records->appends(request()->query())->links() }}
        </div>
    @else
        <div class="alert alert-info">
            No registration offices found. <a href="{{ route('registration_offices.create') }}">Create one now</a>
        </div>
    @endif
</div>
@endsection
