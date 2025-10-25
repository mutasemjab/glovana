@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ __('messages.Discounts') }} - {{ $providerType->name }}</h4>
                        <small class="text-muted">{{ __('messages.Provider') }}: {{ $providerType->provider->name_of_manager }}</small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('discounts.create', [$providerId, $providerType->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('messages.Add_Discount') }}
                        </a>
                        <a href="{{ route('admin.providerDetails.index', $providerId) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Provider') }}
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($discounts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Name') }}</th>
                                        <th>{{ __('messages.Discount_Type') }}</th>
                                        <th>{{ __('messages.Percentage') }}</th>
                                        <th>{{ __('messages.Date_Range') }}</th>
                                        <th>{{ __('messages.Services') }}</th>
                                        <th>{{ __('messages.Status') }}</th>
                                        <th>{{ __('messages.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($discounts as $discount)
                                        <tr class="{{ !$discount->isCurrentlyActive() ? 'table-secondary' : '' }}">
                                            <td>
                                                <strong>{{ $discount->name }}</strong>
                                                @if($discount->description)
                                                    <br><small class="text-muted">{{ $discount->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst($discount->discount_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success" style="font-size: 14px;">
                                                    {{ $discount->percentage }}%
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>{{ __('messages.From') }}:</strong> {{ $discount->start_date->format('Y-m-d') }}<br>
                                                    <strong>{{ __('messages.To') }}:</strong> {{ $discount->end_date->format('Y-m-d') }}
                                                </small>
                                                @if($discount->isCurrentlyActive())
                                                    <br><span class="badge badge-success">{{ __('messages.Active_Now') }}</span>
                                                @elseif($discount->start_date > now())
                                                    <br><span class="badge badge-warning">{{ __('messages.Upcoming') }}</span>
                                                @else
                                                    <br><span class="badge badge-secondary">{{ __('messages.Expired') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($discount->services->count() > 0)
                                                    <small>
                                                        @foreach($discount->services->take(3) as $service)
                                                            <span class="badge badge-outline-primary">{{ app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en }}</span>
                                                        @endforeach
                                                        @if($discount->services->count() > 3)
                                                            <span class="text-muted">+{{ $discount->services->count() - 3 }} {{ __('messages.More') }}</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="badge badge-info">{{ __('messages.All_Services') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($discount->is_active)
                                                    <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('discounts.edit', [$providerId, $providerType->id, $discount->id]) }}" 
                                                       class="btn btn-outline-primary" title="{{ __('messages.Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form action="{{ route('discounts.toggleStatus', [$providerId, $providerType->id, $discount->id]) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-warning" 
                                                                title="{{ $discount->is_active ? __('messages.Deactivate') : __('messages.Activate') }}">
                                                            <i class="fas fa-{{ $discount->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('discounts.destroy', [$providerId, $providerType->id, $discount->id]) }}" 
                                                          method="POST" style="display: inline;"
                                                          onsubmit="return confirm('{{ __('messages.Confirm_Delete_Discount') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                title="{{ __('messages.Delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-percentage fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('messages.No_Discounts_Found') }}</h5>
                            <p class="text-muted">{{ __('messages.Create_First_Discount_Message') }}</p>
                            <a href="{{ route('discounts.create', [$providerId, $providerType->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.Add_First_Discount') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection