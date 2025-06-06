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


    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.provider_List') }}</h6>
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
                        @foreach($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td>
                                @if($provider->photo_of_manager)
                                <img src="{{ asset('assets/admin/uploads/' . $provider->photo_of_manager) }}" alt="{{ $provider->name }}" width="50">
                                @else
                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image" width="50">
                                @endif
                            </td>
                            <td>{{ $provider->name_of_manager }}</td>
                            <td>{{ $provider->country_code }} {{ $provider->phone }}</td>
                            <td>{{ $provider->email }}</td>
                            <td>{{ $provider->balance }}</td>
                            <td>
                                @if($provider->activate == 1)
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.providerDetails.index', $provider->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('providers.show', $provider->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('providers.edit', $provider->id) }}" class="btn btn-primary btn-sm">
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