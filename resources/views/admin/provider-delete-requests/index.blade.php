@extends('layouts.admin')

@section('title', __('messages.delete_account_requests'))

@section('css')
<style>
    .bg-gold {
        background-color: #ffd700 !important;
        color: #000 !important;
    }
    
    .modal-xl {
        max-width: 1200px;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .progress {
        background-color: #e9ecef;
    }
    
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 95%;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>
<!-- Add SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.provider_delete_requests') }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="deleteRequestsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.provider_info') }}</th>
                                    <th>{{ __('messages.contact_info') }}</th>
                                    <th>{{ __('messages.business_stats') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.request_date') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deleteRequests as $request)
                                <tr>
                                    <td>{{ $request->provider->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($request->provider->photo_url)
                                                <img src="{{ $request->provider->photo_url }}" 
                                                     alt="{{ $request->provider->name_of_manager }}" 
                                                     class="rounded-circle me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $request->provider->name_of_manager }}</strong>
                                                <br>
                                                <small class="text-muted">{{ __('messages.member_since') }}: {{ $request->provider->created_at->format('M Y') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-phone text-primary"></i> {{ $request->provider->country_code }}{{ $request->provider->phone }}
                                        </div>
                                        @if($request->provider->email)
                                            <div class="mt-1">
                                                <i class="fas fa-envelope text-info"></i> {{ $request->provider->email }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted">{{ __('messages.balance') }}:</small> 
                                            <strong class="text-success">JD {{ number_format($request->provider->balance, 2) }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">{{ __('messages.points') }}:</small> 
                                            <strong class="text-warning">{{ $request->provider->total_points }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">{{ __('messages.services') }}:</small> 
                                            <strong>{{ $request->provider->providerTypes->count() }}</strong>
                                        </div>
                                        <div>
                                            <small class="text-muted">{{ __('messages.appointments') }}:</small> 
                                            <strong>{{ $request->provider->appointments->count() }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = $request->provider->activate == 1 ? 'success' : ($request->provider->activate == 2 ? 'danger' : 'warning');
                                            $statusText = $request->provider->activate == 1 ? __('messages.active') : ($request->provider->activate == 2 ? __('messages.inactive') : __('messages.waiting_approval'));
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        {{ $request->created_at->format('Y-m-d H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-success btn-sm me-1" onclick="approveDelete({{ $request->id }})">
                                                <i class="fas fa-check"></i> {{ __('messages.approve') }}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectDelete({{ $request->id }})">
                                                <i class="fas fa-times"></i> {{ __('messages.reject') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Detailed View Modal -->
                                <div class="modal fade" id="viewDetailsModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('messages.provider_deletion_details') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <!-- Basic Information -->
                                                    <div class="col-md-6">
                                                        <div class="card h-100">
                                                            <div class="card-header">
                                                                <h6 class="mb-0">{{ __('messages.basic_information') }}</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="text-center mb-3">
                                                                    @if($request->provider->photo_url)
                                                                        <img src="{{ $request->provider->photo_url }}" 
                                                                             alt="{{ $request->provider->name_of_manager }}" 
                                                                             class="rounded-circle" 
                                                                             style="width: 100px; height: 100px; object-fit: cover;">
                                                                    @else
                                                                        <div class="bg-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                                                             style="width: 100px; height: 100px;">
                                                                            <i class="fas fa-user fa-3x text-white"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <table class="table table-sm">
                                                                    <tr>
                                                                        <td><strong>{{ __('messages.name') }}:</strong></td>
                                                                        <td>{{ $request->provider->name_of_manager }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>{{ __('messages.phone') }}:</strong></td>
                                                                        <td>{{ $request->provider->country_code }}{{ $request->provider->phone }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>{{ __('messages.email') }}:</strong></td>
                                                                        <td>{{ $request->provider->email ?: __('messages.not_provided') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>{{ __('messages.joined_date') }}:</strong></td>
                                                                        <td>{{ $request->provider->created_at->format('Y-m-d') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>{{ __('messages.status') }}:</strong></td>
                                                                        <td>
                                                                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Financial Information -->
                                                    <div class="col-md-6">
                                                        <div class="card h-100">
                                                            <div class="card-header">
                                                                <h6 class="mb-0">{{ __('messages.financial_information') }}</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row text-center">
                                                                    <div class="col-6">
                                                                        <div class="border rounded p-3 mb-3">
                                                                            <h4 class="text-success">JD {{ number_format($request->provider->balance, 2) }}</h4>
                                                                            <small class="text-muted">{{ __('messages.current_balance') }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="border rounded p-3 mb-3">
                                                                            <h4 class="text-warning">{{ number_format($request->provider->total_points) }}</h4>
                                                                            <small class="text-muted">{{ __('messages.total_points') }}</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                @if($request->provider->walletTransactions->isNotEmpty())
                                                                    <h6>{{ __('messages.recent_wallet_transactions') }}</h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>{{ __('messages.date') }}</th>
                                                                                    <th>{{ __('messages.amount') }}</th>
                                                                                    <th>{{ __('messages.type') }}</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($request->provider->walletTransactions->take(5) as $transaction)
                                                                                    <tr>
                                                                                        <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                                                                                        <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                                                                            JD {{ number_format($transaction->amount, 2) }}
                                                                                        </td>
                                                                                        <td>{{ $transaction->type }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Services Information -->
                                                    <div class="col-md-12 mt-3">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h6 class="mb-0">{{ __('messages.services_information') }}</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                @if($request->provider->providerTypes->isNotEmpty())
                                                                    <div class="row">
                                                                        @foreach($request->provider->providerTypes as $providerType)
                                                                            <div class="col-md-6 mb-3">
                                                                                <div class="border rounded p-3">
                                                                                    <h6>{{ $providerType->name }}</h6>
                                                                                    <p class="text-muted small">{{ Str::limit($providerType->description, 100) }}</p>
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <small><strong>{{ __('messages.status') }}:</strong> 
                                                                                                <span class="badge badge-sm bg-{{ $providerType->status == 1 ? 'success' : 'danger' }}">
                                                                                                    {{ $providerType->status == 1 ? __('messages.active') : __('messages.inactive') }}
                                                                                                </span>
                                                                                            </small>
                                                                                        </div>
                                                                                    </div>
                                                                                    @if($providerType->is_vip == 1)
                                                                                        <span class="badge bg-gold mt-2">{{ __('messages.vip_service') }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <p class="text-muted">{{ __('messages.no_services_registered') }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Appointments History -->
                                                    <div class="col-md-12 mt-3">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h6 class="mb-0">{{ __('messages.appointments_history') }}</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                @if($request->provider->appointments->isNotEmpty())
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-primary">{{ $request->provider->appointments->count() }}</h5>
                                                                                <small>{{ __('messages.total_appointments') }}</small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-success">{{ $request->provider->appointments->where('appointment_status', 4)->count() }}</h5>
                                                                                <small>{{ __('messages.completed') }}</small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-warning">{{ $request->provider->appointments->where('appointment_status', 1)->count() }}</h5>
                                                                                <small>{{ __('messages.pending') }}</small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-danger">{{ $request->provider->appointments->where('appointment_status', 5)->count() }}</h5>
                                                                                <small>{{ __('messages.cancelled') }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <h6>{{ __('messages.recent_appointments') }}</h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>{{ __('messages.appointment_number') }}</th>
                                                                                    <th>{{ __('messages.date') }}</th>
                                                                                    <th>{{ __('messages.total_price') }}</th>
                                                                                    <th>{{ __('messages.status') }}</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($request->provider->appointments->take(5) as $appointment)
                                                                                    <tr>
                                                                                        <td>{{ $appointment->number }}</td>
                                                                                        <td>{{ $appointment->date ? \Carbon\Carbon::parse($appointment->date)->format('Y-m-d H:i') : '-' }}</td>
                                                                                        <td>JD {{ number_format($appointment->total_prices, 2) }}</td>
                                                                                        <td>
                                                                                            @php
                                                                                                $appointmentStatuses = [
                                                                                                    1 => ['text' => __('messages.pending'), 'class' => 'warning'],
                                                                                                    2 => ['text' => __('messages.accepted'), 'class' => 'info'],
                                                                                                    3 => ['text' => __('messages.on_the_way'), 'class' => 'primary'],
                                                                                                    4 => ['text' => __('messages.delivered'), 'class' => 'success'],
                                                                                                    5 => ['text' => __('messages.cancelled'), 'class' => 'danger'],
                                                                                                    6 => ['text' => __('messages.started'), 'class' => 'secondary'],
                                                                                                    7 => ['text' => __('messages.arrived'), 'class' => 'dark']
                                                                                                ];
                                                                                                $status = $appointmentStatuses[$appointment->appointment_status] ?? ['text' => 'Unknown', 'class' => 'secondary'];
                                                                                            @endphp
                                                                                            <span class="badge bg-{{ $status['class'] }}">{{ $status['text'] }}</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @else
                                                                    <p class="text-muted">{{ __('messages.no_appointments_found') }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Ratings Information -->
                                                    @php
                                                        $allRatings = collect();
                                                        foreach($request->provider->providerTypes as $providerType) {
                                                            $allRatings = $allRatings->merge($providerType->ratings);
                                                        }
                                                        $averageRating = $allRatings->isNotEmpty() ? $allRatings->avg('rating') : 0;
                                                    @endphp
                                                    @if($allRatings->isNotEmpty())
                                                        <div class="col-md-12 mt-3">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h6 class="mb-0">{{ __('messages.ratings_reviews') }}</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-4 text-center">
                                                                            <h3 class="text-warning">{{ number_format($averageRating, 1) }}</h3>
                                                                            <div class="mb-2">
                                                                                @for($i = 1; $i <= 5; $i++)
                                                                                    <i class="fas fa-star {{ $i <= $averageRating ? 'text-warning' : 'text-muted' }}"></i>
                                                                                @endfor
                                                                            </div>
                                                                            <small>{{ __('messages.from') }} {{ $allRatings->count() }} {{ __('messages.reviews') }}</small>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            @for($i = 5; $i >= 1; $i--)
                                                                                @php
                                                                                    $count = $allRatings->where('rating', $i)->count();
                                                                                    $percentage = $allRatings->count() > 0 ? ($count / $allRatings->count()) * 100 : 0;
                                                                                @endphp
                                                                                <div class="d-flex align-items-center mb-1">
                                                                                    <span style="width: 20px;">{{ $i }}</span>
                                                                                    <i class="fas fa-star text-warning mx-1"></i>
                                                                                    <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                                                                                        <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                                                                    </div>
                                                                                    <span style="width: 40px; font-size: 12px;">{{ $count }}</span>
                                                                                </div>
                                                                            @endfor
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    @if($allRatings->where('review', '!=', null)->isNotEmpty())
                                                                        <h6>{{ __('messages.recent_reviews') }}</h6>
                                                                        @foreach($allRatings->where('review', '!=', null)->take(3) as $rating)
                                                                            <div class="border-bottom pb-2 mb-2">
                                                                                <div class="d-flex justify-content-between">
                                                                                    <div>
                                                                                        @for($i = 1; $i <= 5; $i++)
                                                                                            <i class="fas fa-star {{ $i <= $rating->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                                                        @endfor
                                                                                    </div>
                                                                                    <small class="text-muted">{{ $rating->created_at->format('Y-m-d') }}</small>
                                                                                </div>
                                                                                <p class="mb-0 mt-1">{{ $rating->review }}</p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                                                <button type="button" class="btn btn-success me-2" onclick="approveDelete({{ $request->id }})">
                                                    <i class="fas fa-check"></i> {{ __('messages.approve_deletion') }}
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="rejectDelete({{ $request->id }})">
                                                    <i class="fas fa-times"></i> {{ __('messages.reject_deletion') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('messages.no_delete_requests_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(method_exists($deleteRequests, 'links'))
                        <div class="d-flex justify-content-center mt-3">
                            {{ $deleteRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Add SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
    // Check if Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
    }

    function approveDelete(requestId) {
        Swal.fire({
            title: '{{ __("messages.are_you_sure") }}',
            text: '{{ __("messages.approve_delete_warning") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("messages.yes_approve") }}',
            cancelButtonText: '{{ __("messages.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.provider-delete-requests.approve", ":requestId") }}'.replace(':requestId', requestId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '{{ __("messages.approved") }}',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '{{ __("messages.error") }}',
                            '{{ __("messages.something_went_wrong") }}',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function rejectDelete(requestId) {
        Swal.fire({
            title: '{{ __("messages.reject_deletion_request") }}',
            input: 'textarea',
            inputLabel: '{{ __("messages.reason_for_rejection") }}',
            inputPlaceholder: '{{ __("messages.enter_reason") }}...',
            inputAttributes: {
                'aria-label': '{{ __("messages.enter_reason") }}'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("messages.yes_reject") }}',
            cancelButtonText: '{{ __("messages.cancel") }}',
            inputValidator: (value) => {
                if (!value) {
                    return '{{ __("messages.reason_required") }}'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.provider-delete-requests.reject", ":requestId") }}'.replace(':requestId', requestId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '{{ __("messages.rejected") }}',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '{{ __("messages.error") }}',
                            '{{ __("messages.something_went_wrong") }}',
                            'error'
                        );
                    }
                });
            }
        });
    }

    // Initialize DataTable if available
    $(document).ready(function() {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#deleteRequestsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[5, 'desc']], // Sort by request date
                language: {
                    @if(app()->getLocale() == 'ar')
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
                    @endif
                }
            });
        }
    });
</script>
@endpush