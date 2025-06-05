@extends('layouts.admin')

@section('title', __('messages.Edit_provider'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Edit_provider') }}</h1>
        <a href="{{ route('providers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.provider_Details') }}</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('providers.update', $provider->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name">{{ __('messages.Name of manager') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name_of_manager" name="name_of_manager" value="{{ old('name_of_manager', $provider->name_of_manager) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">{{ __('messages.Phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $provider->phone) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">{{ __('messages.Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $provider->email) }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">{{ __('messages.Password') }}</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">{{ __('messages.Leave_blank_to_keep_current_password') }}</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Additional Information -->
                        <div class="form-group">
                            <label for="balance">{{ __('messages.Balance') }}</label>
                            <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="{{ old('balance', $provider->balance) }}">
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="activate">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="activate" name="activate">
                                <option value="1" {{ old('activate', $provider->activate) == 1 ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                                <option value="2" {{ old('activate', $provider->activate) == 2 ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="photo">{{ __('messages.Photo') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo" name="photo_of_manager">
                                <label class="custom-file-label" for="photo_of_manager">{{ __('messages.Choose_file') }}</label>
                            </div>
                            <div class="mt-3" id="image-preview">
                                @if($provider->photo_of_manager)
                                <img src="{{ asset('assets/admin/uploads/' . $provider->photo_of_manager) }}" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('messages.Update') }}
                    </button>
                    <a href="{{ route('providers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Show image preview
    $(document).ready(function() {
        // Show filename on file select
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Image preview
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').html('<img src="' + e.target.result + '" class="img-fluid img-thumbnail" style="max-height: 200px;">');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection