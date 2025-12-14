@extends('layouts.admin')

@section('title', __('messages.ban_history'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-ban"></i> {{ __('messages.ban_history') }}
        </h1>
        <div>
            <a href="{{ route('providers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_providers') }}
            </a>
            @if(!$provider->isBanned())
            <button type="button" class="btn btn-danger" onclick="openBanModal()">
                <i class="fas fa-ban"></i> {{ __('messages.ban_provider') }}
            </button>
            @endif
        </div>
    </div>

    <!-- Provider Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.provider_information') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    @if($provider->photo_of_manager)
                    <img src="{{ asset('assets/admin/uploads/' . $provider->photo_of_manager) }}" 
                         alt="{{ $provider->name_of_manager }}" 
                         class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                    <img src="{{ asset('assets/admin/img/no-image.png') }}" 
                         alt="No Image" 
                         class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    @endif
                </div>
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('messages.ID') }}:</strong> #{{ $provider->id }}</p>
                            <p><strong>{{ __('messages.Name') }}:</strong> {{ $provider->name_of_manager }}</p>
                            <p><strong>{{ __('messages.Phone') }}:</strong> {{ $provider->country_code }} {{ $provider->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('messages.Email') }}:</strong> {{ $provider->email ?? __('messages.not_available') }}</p>
                            <p><strong>{{ __('messages.Balance') }}:</strong> 
                                <span class="badge {{ $provider->balance > 0 ? 'badge-success' : ($provider->balance < 0 ? 'badge-danger' : 'badge-warning') }}">
                                    {{ number_format($provider->balance, 2) }} JD
                                </span>
                            </p>
                            <p><strong>{{ __('messages.Status') }}:</strong>
                                @if($provider->isBanned())
                                    <span class="badge badge-danger">{{ __('messages.banned') }}</span>
                                @elseif($provider->activate == 1)
                                    <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('messages.Inactive') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Ban Alert -->
    @if($provider->activeBan)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5><i class="fas fa-exclamation-triangle"></i> {{ __('messages.active_ban') }}</h5>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <p><strong>{{ __('messages.ban_reason') }}:</strong> {{ $provider->activeBan->getReasonText(app()->getLocale()) }}</p>
                <p><strong>{{ __('messages.ban_type') }}:</strong> 
                    <span class="badge badge-{{ $provider->activeBan->is_permanent ? 'danger' : 'warning' }}">
                        {{ $provider->activeBan->is_permanent ? __('messages.permanent') : __('messages.temporary') }}
                    </span>
                </p>
                @if(!$provider->activeBan->is_permanent)
                <p><strong>{{ __('messages.expires_at') }}:</strong> {{ $provider->activeBan->ban_until->format('Y-m-d H:i') }}</p>
                <p><strong>{{ __('messages.remaining_time') }}:</strong> {{ $provider->activeBan->getRemainingTime(app()->getLocale()) }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <p><strong>{{ __('messages.banned_by') }}:</strong> {{ $provider->activeBan->admin->name ?? __('messages.system') }}</p>
                <p><strong>{{ __('messages.banned_at') }}:</strong> {{ $provider->activeBan->banned_at->format('Y-m-d H:i') }}</p>
                @if($provider->activeBan->ban_description)
                <p><strong>{{ __('messages.description') }}:</strong><br>{{ $provider->activeBan->ban_description }}</p>
                @endif
            </div>
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-success" onclick="openUnbanModal({{ $provider->activeBan->id }})">
                <i class="fas fa-unlock"></i> {{ __('messages.unban_provider') }}
            </button>
        </div>
    </div>
    @endif

    <!-- Ban History Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ __('messages.ban_history_records') }}
                <span class="badge badge-info">{{ $provider->bans->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.ban_reason') }}</th>
                            <th>{{ __('messages.ban_type') }}</th>
                            <th>{{ __('messages.banned_at') }}</th>
                            <th>{{ __('messages.ban_until') }}</th>
                            <th>{{ __('messages.banned_by') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($provider->bans as $ban)
                        <tr class="{{ $ban->is_active ? 'table-danger' : '' }}">
                            <td>{{ $ban->id }}</td>
                            <td>
                                <strong>{{ $ban->getReasonText(app()->getLocale()) }}</strong>
                                @if($ban->ban_description)
                                <br><small class="text-muted">{{ Str::limit($ban->ban_description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $ban->is_permanent ? 'danger' : 'warning' }}">
                                    {{ $ban->is_permanent ? __('messages.permanent') : __('messages.temporary') }}
                                </span>
                            </td>
                            <td>{{ $ban->banned_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($ban->is_permanent)
                                    <span class="text-danger">{{ __('messages.permanent') }}</span>
                                @elseif($ban->ban_until)
                                    {{ $ban->ban_until->format('Y-m-d H:i') }}
                                    <br><small class="text-muted">{{ $ban->getRemainingTime(app()->getLocale()) }}</small>
                                @else
                                    <span class="text-muted">{{ __('messages.not_available') }}</span>
                                @endif
                            </td>
                            <td>{{ $ban->admin->name ?? __('messages.system') }}</td>
                            <td>
                                <span class="badge badge-{{ $ban->is_active ? 'danger' : 'success' }}">
                                    {{ $ban->getStatusText(app()->getLocale()) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" onclick="viewBanDetails({{ $ban->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($ban->is_active)
                                <button type="button" class="btn btn-success btn-sm" onclick="openUnbanModal({{ $ban->id }})">
                                    <i class="fas fa-unlock"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('messages.no_ban_records') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ban Provider Modal -->
<div class="modal fade" id="banModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ban"></i> {{ __('messages.ban_provider') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('providers.ban', $provider->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('messages.ban_warning') }}
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.ban_reason') }} <span class="text-danger">*</span></label>
                        <select name="ban_reason" class="form-control" required>
                            <option value="">{{ __('messages.select_reason') }}</option>
                            @foreach(\App\Models\ProviderBan::BAN_REASONS as $key => $reason)
                            <option value="{{ $key }}">{{ $reason[app()->getLocale()] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.ban_description') }}</label>
                        <textarea name="ban_description" class="form-control" rows="3" 
                                  placeholder="{{ __('messages.ban_description_placeholder') }}"></textarea>
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.ban_type') }} <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ban_type" 
                                       id="temporary" value="temporary" checked onchange="toggleDuration()">
                                <label class="form-check-label" for="temporary">
                                    {{ __('messages.temporary') }}
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ban_type" 
                                       id="permanent" value="permanent" onchange="toggleDuration()">
                                <label class="form-check-label" for="permanent">
                                    {{ __('messages.permanent') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="durationFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('messages.ban_duration') }}</label>
                                    <input type="number" name="ban_duration" class="form-control" 
                                           min="1" value="7" placeholder="7">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('messages.duration_unit') }}</label>
                                    <select name="ban_duration_unit" class="form-control">
                                        <option value="hours">{{ __('messages.hours') }}</option>
                                        <option value="days" selected>{{ __('messages.days') }}</option>
                                        <option value="weeks">{{ __('messages.weeks') }}</option>
                                        <option value="months">{{ __('messages.months') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> {{ __('messages.ban_provider') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unban Modal -->
<div class="modal fade" id="unbanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-unlock"></i> {{ __('messages.unban_provider') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="unbanForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ __('messages.unban_info') }}
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.unban_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="unban_reason" class="form-control" rows="3" required
                                  placeholder="{{ __('messages.unban_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-unlock"></i> {{ __('messages.unban_provider') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ban Details Modal -->
<div class="modal fade" id="banDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> {{ __('messages.ban_details') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="banDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openBanModal() {
    $('#banModal').modal('show');
}

function toggleDuration() {
    const isPermanent = $('input[name="ban_type"]:checked').val() === 'permanent';
    $('#durationFields').toggle(!isPermanent);
    
    if (isPermanent) {
        $('input[name="ban_duration"]').removeAttr('required');
        $('select[name="ban_duration_unit"]').removeAttr('required');
    } else {
        $('input[name="ban_duration"]').attr('required', 'required');
        $('select[name="ban_duration_unit"]').attr('required', 'required');
    }
}

function openUnbanModal(banId) {
    const formAction = '{{ route("providers.unban", [$provider->id, "BAN_ID"]) }}'.replace('BAN_ID', banId);
    $('#unbanForm').attr('action', formAction);
    $('#unbanModal').modal('show');
}

function viewBanDetails(banId) {
    // Find ban data from the table
    const ban = @json($provider->bans);
    const banData = ban.find(b => b.id === banId);
    
    if (banData) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>{{ __('messages.ban_reason') }}:</strong> ${banData.ban_reason}</p>
                    <p><strong>{{ __('messages.ban_type') }}:</strong> 
                        <span class="badge badge-${banData.is_permanent ? 'danger' : 'warning'}">
                            ${banData.is_permanent ? '{{ __("messages.permanent") }}' : '{{ __("messages.temporary") }}'}
                        </span>
                    </p>
                    <p><strong>{{ __('messages.banned_at') }}:</strong> ${new Date(banData.banned_at).toLocaleString()}</p>
                    ${!banData.is_permanent && banData.ban_until ? `<p><strong>{{ __('messages.ban_until') }}:</strong> ${new Date(banData.ban_until).toLocaleString()}</p>` : ''}
                </div>
                <div class="col-md-6">
                    <p><strong>{{ __('messages.Status') }}:</strong> 
                        <span class="badge badge-${banData.is_active ? 'danger' : 'success'}">
                            ${banData.is_active ? '{{ __("messages.Active") }}' : '{{ __("messages.Lifted") }}'}
                        </span>
                    </p>
                    ${banData.unbanned_at ? `<p><strong>{{ __('messages.unbanned_at') }}:</strong> ${new Date(banData.unbanned_at).toLocaleString()}</p>` : ''}
                </div>
            </div>
            ${banData.ban_description ? `<hr><p><strong>{{ __('messages.description') }}:</strong><br>${banData.ban_description}</p>` : ''}
            ${banData.unban_reason ? `<hr><p><strong>{{ __('messages.unban_reason') }}:</strong><br>${banData.unban_reason}</p>` : ''}
        `;
        
        $('#banDetailsContent').html(html);
        $('#banDetailsModal').modal('show');
    }
}

$(document).ready(function() {
    toggleDuration(); // Initialize on page load
});
</script>
@endsection