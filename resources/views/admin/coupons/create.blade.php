@extends('layouts.admin')

@section('title', __('messages.Create_Coupon'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Create_Coupon') }}</h1>
        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Coupon_Details') }}</h6>
        </div>
        <div class="card-body">
      

            <form action="{{ route('coupons.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="code">{{ __('messages.Coupon_Code') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
                            <small class="form-text text-muted">{{ __('messages.Coupon_Code_Info') }}</small>
                        </div>

           
                        
                        <div class="form-group">
                            <label for="title">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                        </div>

                    </div>
                    
                    <div class="col-md-6">
                        <!-- Discount Information -->
                        <div class="form-group">
                            <label for="discount">{{ __('messages.minimum_total') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="minimum_total" name="minimum_total" value="{{ old('minimum_total', 0) }}" required min="0">
                        </div>
                    

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">{{ __('messages.expired_at') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="expired_at" name="expired_at" value="{{ old('expired_at', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                </div>
                            </div>
                        </div>
                         
                        <div class="row">
                         <div class="col-md-6">
                         <div class="form-group">
                            <label for="activate">{{ __('messages.Type') }}</label>
                            <select class="form-control" id="type" name="type">
                                <option value="1" {{ old('type', 1) == 1 ? 'selected' : '' }}>{{ __('messages.Product') }}</option>
                                <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>{{ __('messages.Provider') }}</option>
                            </select>
                        </div>
                        </div>
                        </div>

                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('messages.Save') }}
                    </button>
                    <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
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
    $(document).ready(function() {
        
        // Trigger change on page load for initial state
        $('#coupon_type').trigger('change');
        
        // Generate random coupon code
        $('#code').on('click', function() {
            if (!$(this).val()) {
                var randomCode = Math.random().toString(36).substring(2, 8).toUpperCase();
                $(this).val(randomCode);
            }
        });
        
    
    });
</script>
@endsection