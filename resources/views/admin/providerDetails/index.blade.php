@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ __('messages.Provider_Types') }}: {{ $provider->name_of_manager }}</h4>
                        <small class="text-muted">{{ __('messages.Phone') }}: {{ $provider->country_code }}{{ $provider->phone }}</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.providerDetails.create', $provider->id) }}" class="btn btn-primary">
                            {{ __('messages.Add_Type') }}
                        </a>
                        <a href="{{ route('providers.index') }}" class="btn btn-secondary">
                            {{ __('messages.Back_to_Providers') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Image') }}</th>
                                    <th>{{ __('messages.Type_Name') }}</th>
                                    <th>{{ __('messages.Type') }}</th>
                                    <th>{{ __('messages.Services') }}</th>
                                    <th>{{ __('messages.Price_Per_Hour') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.VIP') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($providerTypes as $providerType)
                                    <tr>
                                        <td>
                                            @if($providerType->images->first())
                                                <img src="{{ $providerType->images->first()->photo_url }}" 
                                                     alt="{{ $providerType->name }}" 
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
                                            <strong>{{ $providerType->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($providerType->description, 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ app()->getLocale() == 'ar' ? $providerType->type->name_ar : $providerType->type->name_en }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                @if(isset($providerType->type->booking_type))
                                                    ({{ $providerType->type->booking_type == 'hourly' ? __('messages.Hourly') : __('messages.Service_Based') }})
                                                @else
                                                    ({{ __('messages.Hourly') }})
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if(isset($providerType->type->booking_type) && $providerType->type->booking_type == 'service')
                                                @php
                                                    $serviceCount = DB::table('provider_services')
                                                        ->where('provider_type_id', $providerType->id)
                                                        ->count();
                                                @endphp
                                                <span class="badge bg-success">
                                                    {{ $serviceCount }} {{ __('messages.Services') }}
                                                </span>
                                                @if($serviceCount > 0)
                                                    <br>
                                                    <small class="text-muted">{{ __('messages.Service_Based_Pricing') }}</small>
                                                @endif
                                            @else
                                                @foreach($providerType->services as $service)
                                                    <span class="badge bg-info me-1 mb-1">
                                                        {{ app()->getLocale() == 'ar' ? $service->service->name_ar : $service->service->name_en }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if(!isset($providerType->type->booking_type) || $providerType->type->booking_type == 'hourly')
                                                {{ number_format($providerType->price_per_hour, 2) }} {{ __('messages.Currency') }}
                                            @else
                                                <span class="text-muted">{{ __('messages.Service_Based_Pricing') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $providerType->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $providerType->status == 1 ? __('messages.On') : __('messages.Off') }}
                                            </span>
                                            <br>
                                            <span class="badge {{ $providerType->activate == 1 ? 'bg-success' : 'bg-warning' }}">
                                                {{ $providerType->activate == 1 ? __('messages.Active') : __('messages.Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $providerType->is_vip == 1 ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                                {{ $providerType->is_vip == 1 ? __('messages.VIP') : __('messages.Regular') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="{{ route('admin.providerDetails.edit', [$provider->id, $providerType->id]) }}" 
                                                   class="btn btn-warning btn-sm mb-1">
                                                    {{ __('messages.Edit') }}
                                                </a>
                                                
                                                <a href="{{ route('admin.providerDetails.availabilities', [$provider->id, $providerType->id]) }}" 
                                                   class="btn btn-info btn-sm mb-1">
                                                    {{ __('messages.Availability') }}
                                                </a>
                                                
                                                <form action="{{ route('admin.providerDetails.destroy', [$provider->id, $providerType->id]) }}" 
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
                                            {{ __('messages.No_Types_Found') }}
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