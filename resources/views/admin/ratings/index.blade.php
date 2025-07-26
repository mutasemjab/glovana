@extends('layouts.admin')
@section('css')
<style>
    .rating-stars {
        display: inline-flex;
        align-items: center;
    }
    .rating-stars .fa-star {
        font-size: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">{{ __('messages.ratings_and_reviews_management') }}</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.all_ratings') }}</h6>
            <div>
                <form action="{{ route('admin.ratings.index') }}" method="GET" class="form-inline">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search') }}" 
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.id') }}</th>
                            <th>{{ __('messages.provider') }}</th>
                            <th>{{ __('messages.user') }}</th>
                            <th>{{ __('messages.rating') }}</th>
                            <th>{{ __('messages.review') }}</th>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ratings as $rating)
                        <tr>
                            <td>{{ $rating->id }}</td>
                            <td>
                                @if($rating->providerType && $rating->providerType->provider)
                                    {{ $rating->providerType->provider->name_of_manager }}
                                    <br>
                                    <small>{{ $rating->providerType->name }}</small>
                                @else
                                    {{ __('messages.na') }}
                                @endif
                            </td>
                            <td>
                                @if($rating->user)
                                    {{ $rating->user->name }}
                                    <br>
                                    <small>{{ $rating->user->email }}</small>
                                @else
                                    {{ __('messages.na') }}
                                @endif
                            </td>
                            <td>
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $rating->rating ? 'text-warning' : 'text-secondary' }}"></i>
                                    @endfor
                                    <span class="ml-1">{{ number_format($rating->rating, 1) }}</span>
                                </div>
                            </td>
                            <td>{{ $rating->review ? Str::limit($rating->review, 50) : __('messages.no_review') }}</td>
                            <td>{{ $rating->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.ratings.destroy', $rating->id) }}" method="POST" 
                                      onsubmit="return confirm('{{ __('messages.confirm_delete_rating') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('messages.no_ratings_found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $ratings->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
