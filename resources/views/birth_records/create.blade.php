@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Birth Record</h2>

    <form action="{{ route('birth_records.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label"><strong>Nationality (Search by Country Name)</strong></label>
            <input type="text" class="form-control mb-2" id="nationality_search" placeholder="Type country name...">
            <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                <div id="nationality_list">
                    @php
                        $nationalities = [
                            'Tanzanian', 'Kenyan', 'Zambian', 'Ugandan', 'Rwandan', 'Burundian', 'Somali',
                            'Ethiopian', 'South African', 'Nigerian', 'Ghanaian', 'Egyptian', 'Moroccan',
                            'Algerian', 'Sudanese', 'Kenyan', 'Malawian', 'Zimbabwean', 'Botswanan', 'Namibian',
                            'Lesothan', 'Swazi', 'Angolan', 'Mozambican', 'Madagascar', 'Mauritian', 'Seychellois',
                            'Congolese', 'Cameroonese', 'Ivorian', 'Senegalese', 'Malian', 'Burkinese', 'Liberian',
                            'Sierra Leonean', 'Gambian', 'Guinean', 'Cape Verdean', 'American', 'British', 'Canadian',
                            'Australian', 'Indian', 'Chinese', 'Japanese', 'German', 'French', 'Italian', 'Spanish',
                            'Dutch', 'Belgian', 'Swedish', 'Norwegian', 'Danish', 'Finnish', 'Polish', 'Russian',
                            'Brazilian', 'Mexican', 'Colombian', 'Argentine', 'Chilean', 'Peruvian', 'Venezuelan'
                        ];
                        sort($nationalities);
                    @endphp
                    @foreach($nationalities as $nationality)
                        <div class="form-check nationality-option mb-2" data-nationality="{{ strtolower($nationality) }}">
                            <input class="form-check-input" type="radio" name="nationality" id="nat_{{ str_replace(' ', '_', strtolower($nationality)) }}" value="{{ $nationality }}" {{ old('nationality') == $nationality ? 'checked' : '' }}>
                            <label class="form-check-label" for="nat_{{ str_replace(' ', '_', strtolower($nationality)) }}">
                                <span style="color: #333;">{{ $nationality }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            @error('nationality')
                <div class="alert alert-danger mt-2 mb-0">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="child_first_name" class="form-label">Child First Name</label>
            <input type="text" name="child_first_name" class="form-control @error('child_first_name') is-invalid @enderror" required>
            @error('child_first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="child_middle_name" class="form-label">Child Middle Name (Optional)</label>
            <input type="text" name="child_middle_name" class="form-control @error('child_middle_name') is-invalid @enderror">
            @error('child_middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="child_last_name" class="form-label">Child Last Name</label>
            <input type="text" name="child_last_name" class="form-control @error('child_last_name') is-invalid @enderror" required>
            @error('child_last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" required>
            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                <option value="">Select...</option>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>
            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="place_of_birth" class="form-label">Place of Birth</label>
            <input type="text" name="place_of_birth" class="form-control @error('place_of_birth') is-invalid @enderror" required>
            @error('place_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="registration_office_id" class="form-label">Registration Office</label>
            <select name="registration_office_id" class="form-control @error('registration_office_id') is-invalid @enderror" required>
                <option value="">Select an office</option>
                @foreach($offices as $office)
                    <option value="{{ $office->id }}">{{ $office->office_name }} - {{ $office->region }}</option>
                @endforeach
            </select>
            @error('registration_office_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="father_name" class="form-label">Father Name (Optional)</label>
            <input type="text" name="father_name" class="form-control">
        </div>

        <div class="mb-3">
            <label for="mother_name" class="form-label">Mother Name (Optional)</label>
            <input type="text" name="mother_name" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Register Birth</button>
        <a href="{{ route('birth_records.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    // Nationality Search
    document.getElementById('nationality_search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.nationality-option').forEach(option => {
            const nationality = option.getAttribute('data-nationality');
            option.style.display = (nationality.includes(searchTerm) || searchTerm === '') ? 'block' : 'none';
        });
    });
</script>

@endsection
