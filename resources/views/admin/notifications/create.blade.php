@extends('layouts.admin')
@section('title')
notifications
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center">Add New Notification</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">

        @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('notifications.send')}}" method="post" id="notification-form">
                            @csrf
                            
                            <div class="form-group mt-0">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @if($errors->has('title')) is-invalid @endif" 
                                       id="title" 
                                       name="title" 
                                       value="{{old('title')}}"
                                       placeholder="Enter notification title"
                                       required>
                                @if($errors->has('title'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="body">Body <span class="text-danger">*</span></label>
                                <textarea name="body" 
                                          id="body" 
                                          class="form-control @if($errors->has('body')) is-invalid @endif"
                                          rows="4"
                                          placeholder="Enter notification message"
                                          required>{{old('body')}}</textarea>
                                @if($errors->has('body'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="type">Send To <span class="text-danger">*</span></label>
                                <select name="type" 
                                        id="type" 
                                        class="form-control @if($errors->has('type')) is-invalid @endif" 
                                        required>
                                    <option value="">-- Select Recipient --</option>
                                    <option value="0" {{ old('type') == '0' ? 'selected' : '' }}>
                                        📢 All (Users + Providers)
                                    </option>
                                    <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>
                                        👥 All Users
                                    </option>
                                    <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>
                                        🏪 All Providers
                                    </option>
                                    <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>
                                        👤 Specific User
                                    </option>
                                    <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>
                                        🏬 Specific Provider
                                    </option>
                                </select>
                                @if($errors->has('type'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('type') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- Specific User Selection -->
                            <div class="form-group" id="user-selection" style="display: none;">
                                <label for="user_id">Select User <span class="text-danger">*</span></label>
                                <select name="user_id" 
                                        id="user_id" 
                                        class="form-control select2 @if($errors->has('user_id')) is-invalid @endif">
                                    <option value="">-- Select User --</option>
                                    @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->phone }}) - {{ $user->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('user_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('user_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- Specific Provider Selection -->
                            <div class="form-group" id="provider-selection" style="display: none;">
                                <label for="provider_id">Select Provider <span class="text-danger">*</span></label>
                                <select name="provider_id" 
                                        id="provider_id" 
                                        class="form-control select2 @if($errors->has('provider_id')) is-invalid @endif">
                                    <option value="">-- Select Provider --</option>
                                    @foreach(\App\Models\Provider::with('providerTypes')->orderBy('phone')->get() as $provider)
                                        <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                            {{ $provider->providerTypes->first()?->name ?? 'N/A' }} ({{ $provider->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('provider_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('provider_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- Preview Card -->
                            <div class="card bg-light mt-3" id="preview-card" style="display: none;">
                                <div class="card-header">
                                    <h5 class="mb-0">📱 Notification Preview</h5>
                                </div>
                                <div class="card-body">
                                    <div class="notification-preview">
                                        <strong id="preview-title">Title will appear here</strong>
                                        <p id="preview-body" class="mb-0 mt-2">Body will appear here</p>
                                        <small class="text-muted d-block mt-2">
                                            <strong>Send to:</strong> <span id="preview-recipient">-</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    <i class="fas fa-paper-plane"></i> Send Notification
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<!-- Select2 CSS & JS (if not already included) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Search and select...',
        allowClear: true,
        width: '100%'
    });

    // Handle type selection change
    $('#type').on('change', function() {
        var type = $(this).val();
        
        // Hide all specific selections
        $('#user-selection').hide();
        $('#provider-selection').hide();
        
        // Clear selections
        $('#user_id').val('').trigger('change');
        $('#provider_id').val('').trigger('change');
        
        // Show relevant selection
        if (type == '3') { // Specific User
            $('#user-selection').show();
            $('#user_id').prop('required', true);
            $('#provider_id').prop('required', false);
        } else if (type == '4') { // Specific Provider
            $('#provider-selection').show();
            $('#provider_id').prop('required', true);
            $('#user_id').prop('required', false);
        } else {
            $('#user_id').prop('required', false);
            $('#provider_id').prop('required', false);
        }
        
        updatePreview();
    });

    // Trigger on page load if old value exists
    if ($('#type').val()) {
        $('#type').trigger('change');
    }

    // Live preview functionality
    $('#title, #body, #type, #user_id, #provider_id').on('change keyup', function() {
        updatePreview();
    });

    function updatePreview() {
        var title = $('#title').val();
        var body = $('#body').val();
        var type = $('#type').val();
        var recipient = '';

        if (title || body) {
            $('#preview-card').show();
            $('#preview-title').text(title || 'Title will appear here');
            $('#preview-body').text(body || 'Body will appear here');

            // Set recipient text
            switch(type) {
                case '0':
                    recipient = '📢 All Users and Providers';
                    break;
                case '1':
                    recipient = '👥 All Users';
                    break;
                case '2':
                    recipient = '🏪 All Providers';
                    break;
                case '3':
                    var userName = $('#user_id option:selected').text();
                    recipient = '👤 ' + (userName || 'Specific User');
                    break;
                case '4':
                    var providerName = $('#provider_id option:selected').text();
                    recipient = '🏬 ' + (providerName || 'Specific Provider');
                    break;
                default:
                    recipient = '-';
            }

            $('#preview-recipient').text(recipient);
        } else {
            $('#preview-card').hide();
        }
    }

    // Form validation before submit
    $('#notification-form').on('submit', function(e) {
        var type = $('#type').val();
        
        if (type == '3' && !$('#user_id').val()) {
            e.preventDefault();
            alert('Please select a user');
            return false;
        }
        
        if (type == '4' && !$('#provider_id').val()) {
            e.preventDefault();
            alert('Please select a provider');
            return false;
        }
        
        return true;
    });
});
</script>

<style>
.notification-preview {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #4e73df;
}

.select2-container .select2-selection--single {
    height: 38px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endsection