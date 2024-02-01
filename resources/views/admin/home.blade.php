@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(Session::has('status'))
                @if (Session::get('status') == 'OK')
                    <div class="alert alert-success text-center">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                        {{ Session::get('message') }}
                    </div>
                @else
                    <div class="alert alert-danger text-center">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                        {{ Session::get('message') }}
                    </div>
                @endif
            @endif
            <div class="row">
                <div class="col-md-6">
                    <a href="{{route('report.index')}}">
                        <div class="card">
                            <div class="card-header" style="text-align: center">@lang('label.report')</div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="{{route('user.data')}}">
                        <div class="card">
                            <div class="card-header" style="text-align: center">@lang('label.user_data')</div>
                        </div>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush
