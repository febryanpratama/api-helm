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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="text-align: center">@lang('label.propose')</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3"><strong>@lang('label.company')</strong></div>
                                <div class="col-md-9">
                                    <span>{{ $company->Name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3"><strong>@lang('label.status')</strong></div>
                                <div class="col-md-9">
                                    <span>{{auth()->user()->is_propose == 'y' ? 'waiting for approve' : 'approved'}}</span>
                                </div>
                            </div>
                            <hr>
                            
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush
