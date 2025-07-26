@extends('layouts.admin')




@section('content')
<div class="container">
    <h1>Activity Logs</h1>
    
    <!-- Filters -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <select name="model" class="form-control">
                    <option value="">All Models</option>
                    <option value="App\Models\User" {{ request('model') == 'App\Models\User' ? 'selected' : '' }}>Users</option>
                    <option value="App\Models\Admin" {{ request('model') == 'App\Models\Admin' ? 'selected' : '' }}>Admins</option>
                    <!-- Add other models -->
                </select>
            </div>
            <div class="col-md-3">
                <select name="event" class="form-control">
                    <option value="">All Events</option>
                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Activity Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Event</th>
                <th>Model</th>
                <th>User</th>
                <th>Changes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr>
                <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                <td>
                    <span class="badge badge-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : 'danger') }}">
                        {{ ucfirst($activity->event) }}
                    </span>
                </td>
                <td>{{ class_basename($activity->subject_type) }}</td>
                <td>
                    @if($activity->causer)
                        {{ $activity->causer->name ?? $activity->causer->username }}
                    @else
                        System
                    @endif
                </td>
                <td>
                    @if($activity->properties->has('attributes'))
                        {{ count($activity->properties['attributes']) }} fields changed
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.activity-logs.show', $activity->id) }}" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $activities->links() }}
</div>

@endsection