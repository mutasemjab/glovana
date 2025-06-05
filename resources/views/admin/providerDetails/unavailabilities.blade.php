@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ __('messages.Unavailability_Schedule') }}</h4>
                        <small class="text-muted">
                            {{ __('messages.Provider') }}: {{ $provider->name_of_manager }} | 
                            {{ __('messages.Service') }}: {{ $providerServiceType->name }}
                        </small>
                    </div>
                    <a href="{{ route('admin.providerDetails.index', $provider->id) }}" class="btn btn-secondary">
                        {{ __('messages.Back_to_Services') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Add Unavailability Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>{{ __('messages.Add_Unavailability') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.providerDetails.unavailabilities.store', [$provider->id, $providerServiceType->id]) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="unavailable_date" class="form-label">{{ __('messages.Unavailable_Date') }}</label>
                                            <input type="date" class="form-control @error('unavailable_date') is-invalid @enderror" 
                                                   id="unavailable_date" name="unavailable_date" value="{{ old('unavailable_date') }}" required>
                                            @error('unavailable_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="start_time" class="form-label">{{ __('messages.Start_Time') }}</label>
                                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                                   id="start_time" name="start_time" value="{{ old('start_time') }}">
                                            <div class="form-text">{{ __('messages.Leave_Empty_Full_Day') }}</div>
                                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="end_time" class="form-label">{{ __('messages.End_Time') }}</label>
                                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                                   id="end_time" name="end_time" value="{{ old('end_time') }}">
                                            <div class="form-text">{{ __('messages.Leave_Empty_Full_Day') }}</div>
                                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-100">{{ __('messages.Add') }}</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <strong>{{ __('messages.Note') }}:</strong> {{ __('messages.Unavailability_Note') }}
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Unavailability List -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Date') }}</th>
                                    <th>{{ __('messages.Day') }}</th>
                                    <th>{{ __('messages.Type') }}</th>
                                    <th>{{ __('messages.Time_Range') }}</th>
                                    <th>{{ __('messages.Duration') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unavailabilities->sortBy('unavailable_date') as $unavailability)
                                    <tr>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ \Carbon\Carbon::parse($unavailability->unavailable_date)->format('M d, Y') }}
                                            </span>
                                            @if(\Carbon\Carbon::parse($unavailability->unavailable_date)->isPast())
                                                <br><small class="text-muted">({{ __('messages.Past') }})</small>
                                            @elseif(\Carbon\Carbon::parse($unavailability->unavailable_date)->isToday())
                                                <br><small class="text-warning">({{ __('messages.Today') }})</small>
                                            @elseif(\Carbon\Carbon::parse($unavailability->unavailable_date)->isTomorrow())
                                                <br><small class="text-success">({{ __('messages.Tomorrow') }})</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ __('messages.' . \Carbon\Carbon::parse($unavailability->unavailable_date)->format('l')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $unavailability->unavailable_type == 'Full Day' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                {{ __('messages.' . str_replace(' ', '_', $unavailability->unavailable_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($unavailability->start_time && $unavailability->end_time)
                                                <strong>{{ \Carbon\Carbon::parse($unavailability->start_time)->format('h:i A') }}</strong> - 
                                                <strong>{{ \Carbon\Carbon::parse($unavailability->end_time)->format('h:i A') }}</strong>
                                            @else
                                                <span class="text-muted">{{ __('messages.Full_Day') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($unavailability->start_time && $unavailability->end_time)
                                                @php
                                                    $start = \Carbon\Carbon::parse($unavailability->start_time);
                                                    $end = \Carbon\Carbon::parse($unavailability->end_time);
                                                    $duration = $start->diffInHours($end);
                                                    $minutes = $start->diffInMinutes($end) % 60;
                                                @endphp
                                                {{ $duration }}h {{ $minutes }}m
                                            @else
                                                <span class="text-muted">24h</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.providerDetails.unavailabilities.destroy', [$provider->id, $providerServiceType->id, $unavailability->id]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('{{ __('messages.Confirm_Delete') }}')">
                                                    <i class="fas fa-trash"></i> {{ __('messages.Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <br>
                                                <h5 class="text-muted">{{ __('messages.No_Unavailability_Found') }}</h5>
                                                <p class="text-muted">{{ __('messages.No_Unavailability_Description') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($unavailabilities->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>{{ __('messages.Summary') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-primary">{{ $unavailabilities->count() }}</h4>
                                                    <small class="text-muted">{{ __('messages.Total_Unavailable_Days') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-danger">{{ $unavailabilities->where('start_time', null)->where('end_time', null)->count() }}</h4>
                                                    <small class="text-muted">{{ __('messages.Full_Day_Blocks') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-warning">{{ $unavailabilities->where('start_time', '!=', null)->where('end_time', '!=', null)->count() }}</h4>
                                                    <small class="text-muted">{{ __('messages.Partial_Day_Blocks') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-info">{{ $unavailabilities->filter(function($item) { return \Carbon\Carbon::parse($item->unavailable_date)->isFuture(); })->count() }}</h4>
                                                    <small class="text-muted">{{ __('messages.Future_Blocks') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill end time when start time is selected
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = this.value;
    const endTimeInput = document.getElementById('end_time');
    
    if (startTime && !endTimeInput.value) {
        // Auto-suggest end time (1 hour after start time)
        const start = new Date('2000-01-01 ' + startTime);
        start.setHours(start.getHours() + 1);
        const endTime = start.toTimeString().slice(0, 5);
        endTimeInput.value = endTime;
    }
});

// Clear both time fields when one is cleared (for full day option)
document.getElementById('start_time').addEventListener('input', function() {
    if (!this.value) {
        document.getElementById('end_time').value = '';
    }
});

document.getElementById('end_time').addEventListener('input', function() {
    if (!this.value) {
        document.getElementById('start_time').value = '';
    }
});

// Validate that end time is after start time
document.getElementById('end_time').addEventListener('change', function() {
    const startTime = document.getElementById('start_time').value;
    const endTime = this.value;
    
    if (startTime && endTime && startTime >= endTime) {
        alert('{{ __("messages.End_Time_Must_Be_After_Start_Time") }}');
        this.value = '';
    }
});
</script>
@endsection