@extends('layouts.admin')

@section('title', __('messages.Coupons'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Coupons') }}</h1>
        <a href="{{ route('coupons.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Coupon') }}
        </a>
    </div>

  

    <!-- Coupons Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Coupons_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
              
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Code') }}</th>
                            <th>{{ __('messages.amount') }}</th>
                            <th>{{ __('messages.minimum_total') }}</th>
                            <th>{{ __('messages.expired_at') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $coupon->code }}</span>
                            </td>
                            <td>{{ $coupon->amount }}</td>
                            <td>{{ $coupon->minimum_total }}</td>
                            <td>{{ $coupon->expired_at }}</td>
                           
                            <td>
                                <div class="btn-group">
                                  
                                    <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
@endsection