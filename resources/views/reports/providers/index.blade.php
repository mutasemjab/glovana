@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Providers Report</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Providers</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Manager</th>
                            <th>Phone</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Last 30 Days Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td>{{ $provider->name_of_manager }}</td>
                            <td>{{ $provider->country_code }} {{ $provider->phone }}</td>
                            <td>{{ number_format($provider->balance, 2) }} JOD</td>
                            <td>
                                @if($provider->activate == 1)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                  {{ $provider->completed_orders_last_30_days }} orders

                            </td>
                            <td>
                                <a href="{{ route('admin.providers.report.show', $provider->id) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Report
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $providers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection