@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ __('messages.Provider_Services') }}: {{ $provider->name_of_manager }}</h4>
                        <small class="text-muted">{{ __('messages.Phone') }}: {{ $provider->country_code }}{{ $provider->phone }}</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.providerDetails.create', $provider->id) }}" class="btn btn-primary">
                            {{ __('messages.Add_Service') }}
                        </a>
                        <a href="{{ route('providers.index') }}" class="btn btn-secondary">
                            {{ __('messages.Back_to_Providers') }}
                        </a>
                    </div>
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

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Image') }}</th>
                                    <th>{{ __('messages.Service_Name') }}</th>
                                    <th>{{ __('messages.Service') }}</th>
                                    <th>{{ __('messages.Type') }}</th>
                                    <th>{{ __('messages.Price_Per_Hour') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.VIP') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($providerServiceTypes as $serviceType)
                                    <tr>
                                        <td>
                                            @if($serviceType->images->first())
                                                <img src="{{ $serviceType->images->first()->photo_url }}" 
                                                     alt="{{ $serviceType->name }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <small class="text-muted">{{ __('messages.No_Image') }}</small>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $serviceType->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($serviceType->description, 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ app()->getLocale() == 'ar' ? $serviceType->service->name_ar : $serviceType->service->name_en }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ app()->getLocale() == 'ar' ? $serviceType->type->name_ar : $serviceType->type->name_en }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($serviceType->price_per_hour, 2) }} {{ __('messages.Currency') }}</td>
                                        <td>
                                            <span class="badge {{ $serviceType->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $serviceType->status_text }}
                                            </span>
                                            <br>
                                            <span class="badge {{ $serviceType->activate == 1 ? 'bg-success' : 'bg-warning' }}">
                                                {{ $serviceType->activate_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $serviceType->is_vip == 1 ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                                {{ $serviceType->is_vip_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="{{ route('admin.providerDetails.edit', [$provider->id, $serviceType->id]) }}" 
                                                   class="btn btn-warning btn-sm mb-1">
                                                    {{ __('messages.Edit') }}
                                                </a>
                                                <a href="{{ route('admin.providerDetails.availabilities', [$provider->id, $serviceType->id]) }}" 
                                                   class="btn btn-info btn-sm mb-1">
                                                    {{ __('messages.Availability') }}
                                                </a>
                                                <a href="{{ route('admin.providerDetails.unavailabilities', [$provider->id, $serviceType->id]) }}" 
                                                   class="btn btn-secondary btn-sm mb-1">
                                                    {{ __('messages.Unavailability') }}
                                                </a>
                                                <form action="{{ route('admin.providerDetails.destroy', [$provider->id, $serviceType->id]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('{{ __('messages.Confirm_Delete') }}')">
                                                        {{ __('messages.Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            {{ __('messages.No_Services_Found') }}
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