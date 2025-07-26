@extends('layouts.admin')
@section('title')
    {{ __('messages.banners') }}
@endsection



@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center"> {{ __('messages.banners') }} </h3>
        <a href="{{ route('banners.create') }}" class="btn btn-sm btn-success">
            {{ __('messages.New') }} {{ __('messages.banners') }}
        </a>
    </div>

    <div class="card-body">
        <div class="clearfix mb-3"></div>

        <div id="ajax_responce_serarchDiv" class="col-md-12">
            @can('banner-table')
                @if(isset($data) && count($data) > 0)
                    <table class="table table-bordered table-hover">
                        <thead class="custom_thead">
                            <tr>
                                <th>{{ __('messages.Photo') }}</th>
                                <th>{{ __('messages.Type') }}</th>
                                <th>{{ __('messages.Product') }}</th>
                                <th>{{ __('messages.Provider Type') }}</th>
                                <th>{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $info)
                                <tr>
                                    <td>
                                        <img class="custom_img"
                                             src="{{ asset('assets/admin/uploads/' . $info->photo) }}"
                                             alt="Banner Image" height="50">
                                    </td>
                                    <td>
                                        {{ $info->type == 1 ? __('messages.Store') : __('messages.Provider Type') }}
                                    </td>
                                    <td>
                                        {{ $info->product->name ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $info->providerType->name ?? '-' }}
                                    </td>
                                    <td>
                                        @can('banner-delete')
                                            <a href="{{ route('banners.edit', $info->id) }}"
                                               class="btn btn-sm btn-primary">{{ __('messages.Edit') }}</a>
                                        @endcan

                                        @can('banner-delete')
                                            <form action="{{ route('banners.destroy', $info->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirmDelete(event)"
                                                  style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-danger">{{ __('messages.Delete') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    {{ $data->links() }}
                @else
                    <div class="alert alert-danger">
                        {{ __('messages.No_data') }}
                    </div>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function confirmDelete(event) {
        event.preventDefault();
        if (confirm("Are you sure you want to delete this banner?")) {
            event.target.submit();
        }
    }
</script>
@endsection
