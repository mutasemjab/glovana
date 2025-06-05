@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4>{{ __('messages.Types') }}</h4>
                    <a href="{{ route('types.create') }}" class="btn btn-primary">
                        {{ __('messages.Add_Type') }}
                    </a>
                </div>
                <div class="card-body">
                 

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.ID') }}</th>
                                    <th>{{ __('messages.Name_English') }}</th>
                                    <th>{{ __('messages.Name_Arabic') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                    <tr>
                                        <td>{{ $type->id }}</td>
                                        <td>{{ $type->name_en }}</td>
                                        <td>{{ $type->name_ar }}</td>
                                        <td>
                                            <a href="{{ route('types.edit', $type->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                {{ __('messages.Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
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