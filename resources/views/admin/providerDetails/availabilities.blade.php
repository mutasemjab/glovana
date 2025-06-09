@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ __('messages.Availability_Schedule') }}</h4>
                        <small class="text-muted">
                            {{ __('messages.Provider') }}: {{ $provider->name_of_manager }} | 
                            {{ __('messages.Type') }}: {{ $providerType->name }}
                        </small>
                    </div>
                    <a href="{{ route('admin.providerDetails.index', $provider->id) }}" class="btn btn-secondary">
                        {{ __('messages.Back_to_Types') }}
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

                    <!-- Add Availability Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>{{ __('messages.Add_Availability') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.providerDetails.availabilities.store', [$provider->id, $providerType->id]) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="day_of_week" class="form-label">{{ __('messages.Day_of_Week') }}</label>
                                            <select class="form-control @error('day_of_week') is-invalid @enderror" 
                                                    id="day_of_week" name="day_of_week" required>
                                                <option value="">{{ __('messages.Select_Day') }}</option>
                                                <option value="Sunday" {{ old('day_of_week') == 'Sunday' ? 'selected' : '' }}>{{ __('messages.Sunday') }}</option>
                                                <option value="Monday" {{ old('day_of_week') == 'Monday' ? 'selected' : '' }}>{{ __('messages.Monday') }}</option>
                                                <option value="Tuesday" {{ old('day_of_week') == 'Tuesday' ? 'selected' : '' }}>{{ __('messages.Tuesday') }}</option>
                                                <option value="Wednesday" {{ old('day_of_week') == 'Wednesday' ? 'selected' : '' }}>{{ __('messages.Wednesday') }}</option>
                                                <option value="Thursday" {{ old('day_of_week') == 'Thursday' ? 'selected' : '' }}>{{ __('messages.Thursday') }}</option>
                                                <option value="Friday" {{ old('day_of_week') == 'Friday' ? 'selected' : '' }}>{{ __('messages.Friday') }}</option>
                                                <option value="Saturday" {{ old('day_of_week') == 'Saturday' ? 'selected' : '' }}>{{ __('messages.Saturday') }}</option>
                                            </select>
                                            @error('day_of_week')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="start_time" class="form-label">{{ __('messages.Start_Time') }}</label>
                                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                                   id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="end_time" class="form-label">{{ __('messages.End_Time') }}</label>
                                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                                   id="end_time" name="end_time" value="{{ old('end_time') }}" required>
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
                            </form>
                        </div>
                    </div>

                    <!-- Availability List -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Day_of_Week') }}</th>
                                    <th>{{ __('messages.Start_Time') }}</th>
                                    <th>{{ __('messages.End_Time') }}</th>
                                    <th>{{ __('messages.Duration') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availabilities as $availability)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ __('messages.' . $availability->day_of_week) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($availability->start_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($availability->end_time)->format('h:i A') }}</td>
                                        <td>
                                            @php
                                                $start = \Carbon\Carbon::parse($availability->start_time);
                                                $end = \Carbon\Carbon::parse($availability->end_time);
                                                $duration = $start->diffInHours($end);
                                                $minutes = $start->diffInMinutes($end) % 60;
                                            @endphp
                                            {{ $duration }}h {{ $minutes }}m
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.providerDetails.availabilities.destroy', [$provider->id, $providerType->id, $availability->id]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('{{ __('messages.Confirm_Delete') }}')">
                                                    {{ __('messages.Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            {{ __('messages.No_Availability_Found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection