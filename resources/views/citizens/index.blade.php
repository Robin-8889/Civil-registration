@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Citizens Panel</h3>
                </div>
                <div class="card-body">

                    <!-- FILTER SECTION -->
                    <form method="GET" action="{{ route('citizens.index') }}" class="mb-4">
                        <div class="row g-3 mb-3">
                            <!-- Name Filter -->
                            <div class="col-md-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="First or Last Name" value="{{ $filters['name'] ?? '' }}">
                            </div>

                            <!-- Birth Certificate Number Filter -->
                            <div class="col-md-3">
                                <label for="birth_certificate_no" class="form-label">Birth Cert #</label>
                                <input type="text" class="form-control" id="birth_certificate_no" name="birth_certificate_no"
                                    placeholder="E.g., BIR-2026-00001" value="{{ $filters['birth_certificate_no'] ?? '' }}">
                            </div>

                            <!-- Age Range Filter -->
                            <div class="col-md-2">
                                <label for="age_from" class="form-label">Age From</label>
                                <input type="number" class="form-control" id="age_from" name="age_from" min="0" max="150"
                                    placeholder="Min age" value="{{ $filters['age_from'] ?? '' }}">
                            </div>

                            <div class="col-md-2">
                                <label for="age_to" class="form-label">Age To</label>
                                <input type="number" class="form-control" id="age_to" name="age_to" min="0" max="150"
                                    placeholder="Max age" value="{{ $filters['age_to'] ?? '' }}">
                            </div>

                            <!-- Record Status Filter -->
                            <div class="col-md-2">
                                <label for="record_status" class="form-label">Record Status</label>
                                <select class="form-select" id="record_status" name="record_status">
                                    <option value="">-- All --</option>
                                    <option value="pending" {{ $filters['record_status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="registered" {{ $filters['record_status'] === 'registered' ? 'selected' : '' }}>Registered</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Status Filter (Vital Status) -->
                            <div class="col-md-2">
                                <label for="status_filter" class="form-label">Vital Status</label>
                                <select class="form-select" id="status_filter" name="status_filter">
                                    <option value="">-- All --</option>
                                    <option value="alive" {{ $filters['status_filter'] === 'alive' ? 'selected' : '' }}>Alive</option>
                                    <option value="dead" {{ $filters['status_filter'] === 'dead' ? 'selected' : '' }}>Dead</option>
                                </select>
                            </div>

                            <!-- Marital Status Filter -->
                            <div class="col-md-2">
                                <label for="marital_status" class="form-label">Marital Status</label>
                                <select class="form-select" id="marital_status" name="marital_status">
                                    <option value="">-- All --</option>
                                    <option value="single" {{ $filters['marital_status'] === 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="married" {{ $filters['marital_status'] === 'married' ? 'selected' : '' }}>Married</option>
                                </select>
                            </div>
                        </div>

                        <!-- Region Filter (System Admin Only) -->
                        @if($isSystemAdmin)
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="region" class="form-label">Region</label>
                                    <select class="form-select" id="region" name="region">
                                        <option value="">-- All Regions --</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->region }}" {{ $filters['region'] === $region->region ? 'selected' : '' }}>
                                                {{ $region->region }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('citizens.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- RESULTS COUNT -->
                    <div class="alert alert-info">
                        <strong>📊 Total Citizens Found:</strong> <span class="badge bg-info">{{ $totalCount }}</span> individual(s)
                        @if($filters['name'] || $filters['birth_certificate_no'] || $filters['age_from'] || $filters['age_to'] || $filters['status_filter'] || $filters['record_status'] || $filters['marital_status'] || $filters['region'])
                            match your filters
                        @else
                            with valid records (Pending/Registered - Rejected records excluded)
                        @endif
                    </div>

                    <!-- CITIZENS TABLE -->
                    @if($citizens->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th style="width: 18%;">Name</th>
                                        <th style="width: 12%;">Birth Cert #</th>
                                        <th style="width: 8%;">Age</th>
                                        <th style="width: 10%;">Region</th>
                                        <th style="width: 9%;">Record Status</th>
                                        <th style="width: 10%;">Marital Status</th>
                                        <th style="width: 12%;">Marriage Cert #</th>
                                        <th style="width: 10%;">Vital Status</th>
                                        <th style="width: 11%;">Death Cert #</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($citizens as $citizen)
                                        <tr>
                                            <!-- Name -->
                                            <td>
                                                <strong>{{ $citizen->child_first_name }}
                                                {{ $citizen->child_middle_name ? $citizen->child_middle_name . ' ' : '' }}
                                                {{ $citizen->child_last_name }}</strong>
                                                <br>
                                                <small class="text-muted">DOB: {{ \Carbon\Carbon::parse($citizen->date_of_birth)->format('M d, Y') }}</small>
                                            </td>

                                            <!-- Birth Certificate Number -->
                                            <td>
                                                <code>{{ $citizen->birth_certificate_no }}</code>
                                            </td>

                                            <!-- Age -->
                                            <td>
                                                <span class="badge bg-info">{{ $citizen->age }} yrs</span>
                                            </td>

                                            <!-- Region -->
                                            <td>
                                                {{ $citizen->office->region ?? 'N/A' }}
                                            </td>

                                            <!-- Record Status -->
                                            <td>
                                                @if($citizen->status === 'registered')
                                                    <span class="badge bg-success">✓ Registered</span>
                                                @elseif($citizen->status === 'pending')
                                                    <span class="badge bg-warning text-dark">⏳ Pending</span>
                                                @else
                                                    <span class="badge bg-danger">✗ Rejected</span>
                                                @endif
                                            </td>

                                            <!-- Marital Status -->
                                            <td>
                                                @if($citizen->marriage)
                                                    <span class="badge bg-success">Married</span>
                                                @else
                                                    <span class="badge bg-secondary">Single</span>
                                                @endif
                                            </td>

                                            <!-- Marriage Certificate Number -->
                                            <td>
                                                @if($citizen->marriage)
                                                    <code>{{ $citizen->marriage->marriage_certificate_no }}</code>
                                                    <br>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($citizen->marriage->date_of_marriage)->format('M d, Y') }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <!-- Vital Status -->
                                            <td>
                                                @if($citizen->death)
                                                    <span class="badge bg-danger">Deceased</span>
                                                @else
                                                    <span class="badge bg-success">Active</span>
                                                @endif
                                            </td>

                                            <!-- Death Certificate Number -->
                                            <td>
                                                @if($citizen->death)
                                                    <code>{{ $citizen->death->death_certificate_no }}</code>
                                                    <br>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($citizen->death->date_of_death)->format('M d, Y') }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $citizens->appends(request()->query())->links() }}
                        </div>

                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-search"></i> No citizens found matching your filters.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 12px 8px;
    }

    code {
        background-color: #f8f9fa;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
        color: #0d47a1;
    }

    .card {
        border: none;
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0;
        padding: 1.5rem;
    }

    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        font-size: 0.8rem;
    }
</style>
@endsection
