@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Event</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="datetime-local" class="form-control @error('event_date') is-invalid @enderror" 
                                       id="event_date" name="event_date" value="{{ old('event_date') }}" required>
                                @error('event_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="venue" class="form-label">Venue *</label>
                                <input type="text" class="form-control @error('venue') is-invalid @enderror" 
                                       id="venue" name="venue" value="{{ old('venue') }}" required>
                                @error('venue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="guest_count" class="form-label">Guest Count *</label>
                                <input type="number" class="form-control @error('guest_count') is-invalid @enderror" 
                                       id="guest_count" name="guest_count" value="{{ old('guest_count') }}" min="1" required>
                                @error('guest_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="budget" class="form-label">Budget ($) *</label>
                                <input type="number" class="form-control @error('budget') is-invalid @enderror" 
                                       id="budget" name="budget" value="{{ old('budget') }}" min="0" step="0.01" required>
                                @error('budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Event Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="catering" id="req_catering">
                                        <label class="form-check-label" for="req_catering">Catering</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="decoration" id="req_decoration">
                                        <label class="form-check-label" for="req_decoration">Decoration</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="photography" id="req_photography">
                                        <label class="form-check-label" for="req_photography">Photography</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="music" id="req_music">
                                        <label class="form-check-label" for="req_music">Music/DJ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="makeup" id="req_makeup">
                                        <label class="form-check-label" for="req_makeup">Makeup</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="transport" id="req_transport">
                                        <label class="form-check-label" for="req_transport">Transport</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="venue" id="req_venue">
                                        <label class="form-check-label" for="req_venue">Venue Booking</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('events.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Set minimum date to today
    document.getElementById('event_date').min = new Date().toISOString().slice(0, 16);
    
    // Budget formatting
    document.getElementById('budget').addEventListener('input', function(e) {
        let value = e.target.value;
        if (value && !isNaN(value)) {
            e.target.value = parseFloat(value).toFixed(2);
        }
    });
</script>
@endpush
@endsection
