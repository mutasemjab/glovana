@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i> {{ __('messages.Inventory_Report') }}
                    </h3>
                </div>

                <!-- Filter Form -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('reports.inventory.generate') }}" class="row g-3">
                        <!-- Report Type -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Report_Type') }} <span class="text-danger">*</span></label>
                            <select name="report_type" class="form-control" required onchange="toggleDateFields()">
                                <option value="summary" {{ request('report_type', 'summary') == 'summary' ? 'selected' : '' }}>
                                    {{ __('messages.Summary_Report') }}
                                </option>
                                <option value="detailed" {{ request('report_type') == 'detailed' ? 'selected' : '' }}>
                                    {{ __('messages.Detailed_Report') }}
                                </option>
                                <option value="movements" {{ request('report_type') == 'movements' ? 'selected' : '' }}>
                                    {{ __('messages.Stock_Movements') }}
                                </option>
                            </select>
                        </div>

                        <!-- Product Filter -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Product') }}</label>
                            <select name="product_id" class="form-control">
                                <option value="">{{ __('messages.All_Products') }}</option>
                                @if(isset($products))
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Warehouse Filter -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Warehouse') }}</label>
                            <select name="warehouse_id" class="form-control">
                                <option value="">{{ __('messages.All_Warehouses') }}</option>
                                @if(isset($warehouses))
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Date Range (for detailed and movements reports) -->
                        <div class="col-md-3" id="date-fields">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">{{ __('messages.Start_Date') }}</label>
                                    <input type="date" 
                                           name="start_date" 
                                           class="form-control" 
                                           value="{{ request('start_date') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">{{ __('messages.End_Date') }}</label>
                                    <input type="date" 
                                           name="end_date" 
                                           class="form-control" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ __('messages.Generate_Report') }}
                            </button>
                            <a href="{{ route('reports.inventory.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('messages.Clear') }}
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Summary Statistics (for summary reports) -->
                @if(isset($summary) && !empty($summary))
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-bar"></i> {{ __('messages.Inventory_Summary') }}
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ number_format($summary['total_in']) }}</h3>
                                        <p>{{ __('messages.Total_Stock_In') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ number_format($summary['total_out']) }}</h3>
                                        <p>{{ __('messages.Total_Stock_Out') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ number_format($summary['current_total_stock']) }}</h3>
                                        <p>{{ __('messages.Current_Total_Stock') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>{{ $summary['low_stock_products'] }}</h3>
                                        <p>{{ __('messages.Low_Stock_Products') }}</p>
                                        <small>(≤{{ $summary['minimum_quantity_threshold'] }})</small>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-cube"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ __('messages.Total_Products') }}</span>
                                        <span class="info-box-number">{{ $summary['total_products'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-warehouse"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ __('messages.Total_Warehouses') }}</span>
                                        <span class="info-box-number">{{ $summary['total_warehouses'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Summary Report Table -->
                @if(isset($inventoryData) && $inventoryData->isNotEmpty())
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('messages.Inventory_Details') }}</h5>
                            <form method="GET" action="{{ route('reports.inventory.export') }}" class="d-inline">
                                <input type="hidden" name="report_type" value="{{ request('report_type') }}">
                                <input type="hidden" name="product_id" value="{{ request('product_id') }}">
                                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> {{ __('messages.Export_CSV') }}
                                </button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Product') }}</th>
                                        <th>{{ __('messages.Warehouse') }}</th>
                                        <th class="text-center">{{ __('messages.Stock_In') }}</th>
                                        <th class="text-center">{{ __('messages.Stock_Out') }}</th>
                                        <th class="text-center">{{ __('messages.Current_Stock') }}</th>
                                        <th class="text-center">{{ __('messages.Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventoryData as $item)
                                        <tr class="{{ $item->current_stock <= 2 ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}</strong>
                                                @if(app()->getLocale() == 'en' && $item->name_ar)
                                                    <br><small class="text-muted">{{ $item->name_ar }}</small>
                                                @elseif(app()->getLocale() == 'ar' && $item->name_en)
                                                    <br><small class="text-muted">{{ $item->name_en }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $item->warehouse_name ?? __('messages.All_Warehouses') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ number_format($item->total_in) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ number_format($item->total_out) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $item->current_stock > 2 ? 'bg-primary' : ($item->current_stock == 0 ? 'bg-danger' : 'bg-warning') }}">
                                                    {{ number_format($item->current_stock) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($item->current_stock == 0)
                                                    <span class="badge bg-danger">{{ __('messages.out_of_stock') }}</span>
                                                @elseif($item->current_stock <= 2)
                                                    <span class="badge bg-warning">{{ __('messages.low_stock') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ __('messages.in_stock') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $inventoryData->links() }}
                        </div>
                    </div>
                @endif

                <!-- Movements Report Table -->
                @if(isset($movements) && $movements->isNotEmpty())
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('messages.Stock_Movements') }}</h5>
                            <form method="GET" action="{{ route('reports.inventory.export') }}" class="d-inline">
                                <input type="hidden" name="report_type" value="{{ request('report_type') }}">
                                <input type="hidden" name="product_id" value="{{ request('product_id') }}">
                                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> {{ __('messages.Export_CSV') }}
                                </button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Date') }}</th>
                                        <th>{{ __('messages.Product') }}</th>
                                        <th>{{ __('messages.Warehouse') }}</th>
                                        <th class="text-center">{{ __('messages.Movement_Type') }}</th>
                                        <th class="text-center">{{ __('messages.Quantity') }}</th>
                                        <th>{{ __('messages.Description') }}</th>
                                        <th class="text-center">{{ __('messages.Reference') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                        <tr>
                                            <td>{{ Carbon\Carbon::parse($movement->date_note_voucher)->format('Y-m-d') }}</td>
                                            <td>
                                                <strong>{{ app()->getLocale() == 'ar' ? $movement->name_ar : $movement->name_en }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $movement->warehouse_name ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($movement->movement_type == 1)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-arrow-down"></i> {{ __('messages.Stock_In') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-arrow-up"></i> {{ __('messages.Stock_Out') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $movement->movement_type == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $movement->movement_type == 1 ? '+' : '-' }}{{ number_format($movement->quantity) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $movement->description }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div>
                                                    <small>
                                                        <strong>{{ __('messages.Voucher') }}:</strong> #{{ $movement->voucher_number }}
                                                        @if($movement->order_number)
                                                            <br><strong>{{ __('messages.Order') }}:</strong> #{{ $movement->order_number }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $movements->links() }}
                        </div>
                    </div>
                @endif

                <!-- No Data Message -->
                @if((isset($inventoryData) && $inventoryData->isEmpty()) || (isset($movements) && $movements->isEmpty()))
                    @if(request()->has('report_type'))
                        <div class="card-body text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>{{ __('messages.No_Data_Found') }}</h5>
                                <p>{{ __('messages.No_Inventory_Data_Available') }}</p>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Help Section -->
                @if(!request()->has('report_type'))
                    <div class="card-body text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h5>{{ __('messages.Inventory_Reporting') }}</h5>
                            <p>{{ __('messages.Select_Report_Type_And_Filters') }}</p>
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card border">
                                        <div class="card-body">
                                            <i class="fas fa-list-alt fa-2x text-primary mb-2"></i>
                                            <h6>{{ __('messages.Summary_Report') }}</h6>
                                            <small class="text-muted">{{ __('messages.Summary_Report_Description') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border">
                                        <div class="card-body">
                                            <i class="fas fa-table fa-2x text-info mb-2"></i>
                                            <h6>{{ __('messages.Detailed_Report') }}</h6>
                                            <small class="text-muted">{{ __('messages.Detailed_Report_Description') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border">
                                        <div class="card-body">
                                            <i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i>
                                            <h6>{{ __('messages.Stock_Movements') }}</h6>
                                            <small class="text-muted">{{ __('messages.Movements_Report_Description') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function toggleDateFields() {
    const reportType = document.querySelector('select[name="report_type"]').value;
    const dateFields = document.getElementById('date-fields');
    
    if (reportType === 'summary') {
        dateFields.style.display = 'none';
        // Clear date values for summary report
        document.querySelector('input[name="start_date"]').value = '';
        document.querySelector('input[name="end_date"]').value = '';
    } else {
        dateFields.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize date field visibility
    toggleDateFields();
    
    // Date validation
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
        
        endDate.addEventListener('change', function() {
            startDate.max = this.value;
        });
    }
});
</script>
@endsection