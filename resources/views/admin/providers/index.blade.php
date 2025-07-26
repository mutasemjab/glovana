@extends('layouts.admin')

@section('title', __('messages.providers'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.providers') }}</h1>
        <a href="{{ route('providers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_provider') }}
        </a>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filters') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('providers.index') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label for="status">{{ __('messages.Status') }}</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">{{ __('messages.All_Status') }}</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="balance_type">{{ __('messages.Balance_Type') }}</label>
                    <select name="balance_type" id="balance_type" class="form-control">
                        <option value="">{{ __('messages.All_Balances') }}</option>
                        <option value="positive" {{ request('balance_type') == 'positive' ? 'selected' : '' }}>{{ __('messages.Positive_Balance') }}</option>
                        <option value="negative" {{ request('balance_type') == 'negative' ? 'selected' : '' }}>{{ __('messages.Negative_Balance') }}</option>
                        <option value="zero" {{ request('balance_type') == 'zero' ? 'selected' : '' }}>{{ __('messages.Zero_Balance') }}</option>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="min_balance">{{ __('messages.Min_Balance') }}</label>
                    <input type="number" name="min_balance" id="min_balance" class="form-control" 
                           value="{{ request('min_balance') }}" step="0.01" placeholder="0.00">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="max_balance">{{ __('messages.Max_Balance') }}</label>
                    <input type="number" name="max_balance" id="max_balance" class="form-control" 
                           value="{{ request('max_balance') }}" step="0.01" placeholder="1000.00">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="search">{{ __('messages.Search_Name') }}</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="{{ __('messages.Manager_Name') }}">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> {{ __('messages.Filter') }}
                    </button>
                    <a href="{{ route('providers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> {{ __('messages.Clear_Filters') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.provider_List') }}</h6>
            <span class="badge badge-info">{{ count($providers) }} {{ __('messages.Total_Providers') }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Photo') }}</th>
                            <th>{{ __('messages.Name') }}</th>
                            <th>{{ __('messages.Phone') }}</th>
                            <th>{{ __('messages.Email') }}</th>
                            <th>{{ __('messages.Balance') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td>
                                @if($provider->photo_of_manager)
                                <img src="{{ asset('assets/admin/uploads/' . $provider->photo_of_manager) }}" alt="{{ $provider->name }}" width="50" class="rounded">
                                @else
                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image" width="50" class="rounded">
                                @endif
                            </td>
                            <td>{{ $provider->name_of_manager }}</td>
                            <td>{{ $provider->country_code }} {{ $provider->phone }}</td>
                            <td>{{ $provider->email }}</td>
                            <td>
                                <span class="badge {{ $provider->balance > 0 ? 'badge-success' : ($provider->balance < 0 ? 'badge-danger' : 'badge-warning') }}">
                                    {{ number_format($provider->balance, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($provider->activate == 1)
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.providerDetails.index', $provider->id) }}" class="btn btn-info btn-sm" title="{{ __('messages.View_Details') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('providers.show', $provider->id) }}" class="btn btn-info btn-sm" title="{{ __('messages.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('providers.edit', $provider->id) }}" class="btn btn-primary btn-sm" title="{{ __('messages.Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('messages.No_Providers_Found') }}</td>
                        </tr>
                        @endforelse
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
        $('#dataTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [1, 7] } // Disable sorting for Photo and Actions columns
            ]
        });
    });
</script>
@endsection