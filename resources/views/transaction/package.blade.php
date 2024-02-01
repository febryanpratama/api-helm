@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-2">
        <div class="col-md-12">
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
            @php
                $agency = null;
                if (request()->has('agency')) {
                    $agency = request()->get('agency');

                    $placeholder = "placeholder.search_" . $agency;
                    $label_check = "label.check_" . $agency;
                }
            @endphp
            @if ($agency)
                <form action="{{ route('transaction.package', ['agency' => request()->get('agency'), 'status' => request()->get('status')]) }}" method="get">
                    @csrf
                    <input type="hidden" name="agency" value="{{$agency}}">
                    <input type="hidden" value="1" name="status">
                    <div class="row">
                        <div class="col-md-6">
                            <label>@lang($label_check)</label>
                            <input type="text" placeholder="@lang($placeholder)" class="form-control" name="search" value="{{ request()->get('search') }}">
                        </div>
                        <div class="col-md-2" style="margin-top:30px;">
                            <button class="btn btn-primary">
                                @lang('button.search')
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
            @if (request()->has('status'))
                @if (count($company) > 0)
                    <div class="row" style="margin-top:10px;">
                        @foreach ($company as $item)
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header" style="text-align: center">{{$item->Name}}</div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3"><strong>@lang('label.address')</strong></div>
                                            <div class="col-md-9">
                                                <span>{{$item->Address}}</span>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3"><strong>@lang('label.email')</strong></div>
                                            <div class="col-md-9">
                                                <span>{{$item->Email}}</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3"><strong>@lang('label.phone')</strong></div>
                                            <div class="col-md-9">
                                                <span>{{$item->Phone}}</span>
                                            </div>
                                        </div>

                                        {{-- <div class="row">
                                            <div class="col-md-3"><strong>@lang('label.type')</strong></div>
                                            <div class="col-md-9">
                                                <span>{{$item->Type}}</span>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header" style="text-align: center">@lang('label.not_found')</div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="row" style="margin-top:10px;">
                @foreach ($package as $item)
                    <div class="col-md-6" style="margin-top:5px;">
                        <a href="{{ route('transaction.payment', ['package' => $item->ID]) }}" style="color:black">
                            <div class="card">
                                <div class="card-header" style="text-align: center">{{ $item->Name }}</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3"><strong>@lang('label.price')</strong></div>
                                        <div class="col-md-9">
                                            <span>Rp. {{ number_format($item->DesktopPriceFinal,0,',','.') }}</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3"><strong>@lang('label.active_period')</strong></div>
                                        <div class="col-md-9">
                                            <span>3 months</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush
