@extends('layouts.admin')

@section('title', __('messages.Points_History'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star text-warning"></i> {{ __('messages.Points_History') }}
        </h1>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Users') }}
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPointsModal">
                <i class="fas fa-plus"></i> {{ __('messages.Add_Points') }}
            </button>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @if($user->photo)
                                <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}" 
                                     alt="{{ $user->name }}" class="rounded-circle" width="80" height="80">
                            @else
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-user text-white fa-2x"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb-1">{{ $user->name }}</h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope"></i> {{ $user->email }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone"></i> {{ $user->country_code }} {{ $user->phone }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h3 class="mb-0">{{ number_format($user->total_points) }}</h3>
                                            <small>{{ __('messages.Total_Points') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h3 class="mb-0">{{ number_format($user->balance, 2) }}</h3>
                                            <small>{{ __('messages.Balance') }} (JD)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Total_Earned') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                +{{ number_format($totalEarned) }} {{ __('messages.pts') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.Total_Deducted') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                -{{ number_format($totalDeducted) }} {{ __('messages.pts') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-minus-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Total_Transactions') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($transactions->total()) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Current_Balance') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($user->total_points) }} {{ __('messages.pts') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Transaction_History') }}</h6>
            
            <!-- Filter Form -->
            <form method="GET" action="{{ route('users.points.history', $user->id) }}" class="form-inline">
                <select name="type" class="form-control form-control-sm mr-2">
                    <option value="">{{ __('messages.All_Types') }}</option>
                    <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>{{ __('messages.Added') }}</option>
                    <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>{{ __('messages.Deducted') }}</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-filter"></i> {{ __('messages.Filter') }}
                </button>
                @if(request()->hasAny(['type']))
                    <a href="{{ route('users.points.history', $user->id) }}" class="btn btn-sm btn-outline-secondary ml-1">
                        <i class="fas fa-times"></i> {{ __('messages.Clear') }}
                    </a>
                @endif
            </form>
        </div>
        
        <div class="card-body">
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('messages.ID') }}</th>
                                <th>{{ __('messages.Date') }}</th>
                                <th>{{ __('messages.Type') }}</th>
                                <th>{{ __('messages.Points') }}</th>
                                <th>{{ __('messages.Performed_By') }}</th>
                                <th>{{ __('messages.Note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->id }}</td>
                                <td>
                                    <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($transaction->type_of_transaction == 1)
                                        <span class="badge badge-success">
                                            <i class="fas fa-plus"></i> {{ __('messages.Added') }}
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-minus"></i> {{ __('messages.Deducted') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->type_of_transaction == 1)
                                        <span class="text-success font-weight-bold">
                                            +{{ number_format(abs($transaction->points)) }} {{ __('messages.pts') }}
                                        </span>
                                    @else
                                        <span class="text-warning font-weight-bold">
                                            -{{ number_format(abs($transaction->points)) }} {{ __('messages.pts') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->admin_id)
                                        <div>
                                            <i class="fas fa-user-shield text-primary"></i>
                                            <small>{{ __('messages.Admin') }}: {{ $transaction->admin->name ?? 'N/A' }}</small>
                                        </div>
                                    @elseif($transaction->provider_id)
                                        <div>
                                            <i class="fas fa-store text-info"></i>
                                            <small>{{ __('messages.Provider') }}: {{ $transaction->provider->name ?? 'N/A' }}</small>
                                        </div>
                                    @else
                                        <div>
                                            <i class="fas fa-cogs text-secondary"></i>
                                            <small>{{ __('messages.System') }}</small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->note)
                                        <span class="text-muted">{{ $transaction->note }}</span>
                                    @else
                                        <span class="text-muted">{{ __('messages.No_Note') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('messages.No_Transactions_Found') }}</h5>
                    <p class="text-muted">{{ __('messages.No_Point_Transactions_Message') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Points Modal -->
<div class="modal fade" id="addPointsModal" tabindex="-1" role="dialog" aria-labelledby="addPointsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPointsModalLabel">
                    <i class="fas fa-star"></i> {{ __('messages.Manage_Points') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('points.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    
                    <!-- User Info Display -->
                    <div class="alert alert-info">
                        <strong>{{ __('messages.User') }}:</strong> {{ $user->name }}<br>
                        <strong>{{ __('messages.Current_Points') }}:</strong> {{ number_format($user->total_points) }} {{ __('messages.pts') }}
                    </div>
                    
                    <!-- Transaction Type -->
                    <div class="form-group">
                        <label for="transactionType">{{ __('messages.Transaction_Type') }}</label>
                        <select class="form-control" id="transactionType" name="type_of_transaction" required>
                            <option value="">{{ __('messages.Select_Transaction_Type') }}</option>
                            <option value="1">{{ __('messages.Add_Points') }}</option>
                            <option value="2">{{ __('messages.Deduct_Points') }}</option>
                        </select>
                    </div>
                    
                    <!-- Points Amount -->
                    <div class="form-group">
                        <label for="pointsAmount">{{ __('messages.Points_Amount') }}</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="pointsAmount" name="points" 
                                   placeholder="0" min="1" required>
                            <div class="input-group-append">
                                <span class="input-group-text">{{ __('messages.pts') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Note -->
                    <div class="form-group">
                        <label for="note">{{ __('messages.Note') }} ({{ __('messages.Optional') }})</label>
                        <textarea class="form-control" id="note" name="note" rows="3" 
                                  placeholder="{{ __('messages.Add_Note_Placeholder') }}"></textarea>
                    </div>
                    
                    <!-- Preview -->
                    <div id="transactionPreview" class="alert" style="display: none;">
                        <strong>{{ __('messages.Preview') }}:</strong><br>
                        <span id="previewText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> {{ __('messages.Update_Points') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Handle form changes for preview
        $('#transactionType, #pointsAmount').on('change input', function() {
            updatePreview();
        });
        
        function updatePreview() {
            var type = $('#transactionType').val();
            var points = parseInt($('#pointsAmount').val()) || 0;
            var currentPoints = {{ $user->total_points }};
            
            if (type && points > 0) {
                var newPoints;
                var actionText;
                var alertClass;
                
                if (type == '1') { // Add
                    newPoints = currentPoints + points;
                    actionText = "{{ __('messages.ADD') }}" + ' ' + points + ' {{ __('messages.pts') }}';
                    alertClass = 'alert-success';
                } else { // Deduct
                    newPoints = currentPoints - points;
                    actionText = "{{ __('messages.DEDUCT') }}" + ' ' + points + ' {{ __('messages.pts') }}';
                    alertClass = 'alert-warning';
                    
                    if (newPoints < 0) {
                        alertClass = 'alert-danger';
                    }
                }
                
                $('#previewText').html(
                    actionText + '<br>' +
                    "{{ __('messages.New_Points_Total') }}" + ': ' + newPoints + ' {{ __('messages.pts') }}' +
                    (newPoints < 0 ? ' <strong>({{ __('messages.NEGATIVE_POINTS') }})</strong>' : '')
                );
                
                $('#transactionPreview')
                    .removeClass('alert-success alert-warning alert-danger')
                    .addClass(alertClass)
                    .show();
                
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#transactionPreview').hide();
                $('#submitBtn').prop('disabled', true);
            }
        }
        
        // Handle form submission
        $('form').on('submit', function(e) {
            var points = parseInt($('#pointsAmount').val());
            var type = $('#transactionType').val();
            var currentPoints = {{ $user->total_points }};
            
            if (type == '2' && points > currentPoints) {
                if (!confirm("{{ __('messages.Negative_Points_Confirmation') }}")) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endsection