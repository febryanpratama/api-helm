@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-2">
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

            <form action="{{ route('transaction.agency', ['agency' => request()->get('agency'), 'status' => request()->get('status')]) }}" method="get">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="text-align: center">@lang('label.choose_agency')</div>
                        <div class="card-body">
                            {{-- <form action="{{ route('transaction.package') }}" method="get"> --}}
                                <div class="row">
                                    {{-- <div class="col-md-3">@lang('label.agency')</div> --}}
                                    <div class="col-md-12">
                                        {{-- <select name="agency" id="" class="form-control">
                                            <option value="public_office">@lang('label.public_office')</option>
                                            <option value="private_office">@lang('label.private_office')</option>
                                            <option value="school">@lang('label.school')</option>
                                            <option value="government_agency">@lang('label.government_agency')</option>
                                        </select> --}}
                                        <div id="radio-button-wrapper" style="text-align: center">
                                            <span class="image-radio" style="margin-left:3px;">
                                                <input name="agency" type="radio" required {{ request()->get('agency') == 'college' ? 'checked' : ''}} value="college" />
                                                <img src="{{ asset('img/college1.jpg') }}" style="width:130px;height:130px;" title="@lang('label.college')">
                                            </span>
                                            <span class="image-radio" style="margin-left:3px;">
                                                <input name="agency" type="radio" required {{ request()->get('agency') == 'private_office' ? 'checked' : ''}} value="private_office" />
                                                <img src="{{ asset('img/office1.jpg') }}" style="width:130px;height:130px;" title="@lang('label.private_office')">
                                            </span>
                                            <hr>
                                            <span class="image-radio" style="margin-left:3px;">
                                                <input name="agency" type="radio" required {{ request()->get('agency') == 'school' ? 'checked' : ''}} value="school" />
                                                <img src="{{ asset('img/school1.jpg') }}" style="width:130px;height:130px;" title="@lang('label.school')">
                                            </span>
                                            <span class="image-radio" style="margin-left:3px;">
                                                <input name="agency" type="radio" required {{ request()->get('agency') == 'government_agency' ? 'checked' : ''}} value="government_agency" />
                                                <img src="{{ asset('img/goverment building1.jpg') }}" style="width:130px;height:130px;" title="@lang('label.government_agency')">
                                            </span>
                                        </div>
                                        {{-- <input type="radio" name="agency"><img src="" alt="" srcset=""> --}}
                                    </div>
                                </div>
                                <div style="text-align: right; margin-top:10px;">
                                    {{-- <button class="btn btn-primary">@lang('button.save')</button> --}}
                                </div>
                            {{-- </form> --}}
                        </div>
                    </div>
                </div>
            </div>

            @php
                $agency = null;
                if (request()->has('agency')) {
                    $agency = request()->get('agency');

                    $placeholder = "placeholder.search_" . $agency;
                    $label_check = "label.check_" . $agency;
                }
            @endphp
                @csrf
                <input type="hidden" value="1" name="status">
                <div class="row mt-4">
                    <div class="col-md-10">
                        <label>@lang('label.search_work_place')</label>
                        <input type="text" placeholder="@lang('label.search_work_place')" class="form-control" name="search" required value="{{ request()->get('search') }}">
                    </div>
                    <div class="col-md-2" style="margin-top:30px;">
                        <button class="btn btn-primary">
                            @lang('button.search')
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            @if (request()->has('status'))
                @if (count($company) > 0)
                    <div class="row" style="margin-top:10px;">
                        @foreach ($company as $item)
                            <div class="col-md-12">
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

                                        <div style="text-align:right">

                                            <a href="{{ route('transaction.propose', ['company' => $item->ID]) }}" class="btn btn-primary">@lang('label.propose')</a>
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
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header" style="text-align: center">@lang('label.not_found')</div>
                                <div class="card-body">
                                    <form action="{{route('transaction.company')}}" method="post">
                                        <input type="hidden" name="agency" value="{{ request()->get('agency') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label for="">@lang('label.register_your_company')</label>
                                            <input type="text" class="form-control" name="name" placeholder="@lang('label.company_name')">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">@lang('button.save')</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            
        </div>
    </div>
</div>
@endsection

@push('style')
    <style>
        span.image-radio input[type="radio"]:checked + img{
            border:1px solid red;
        }
    </style>
@endpush
@push('script')
<script>
    $(document).ready(function () {
        $(".image-radio img").click(function(){
           $(this).prev().attr('checked',true);
       });
    });
</script>
@endpush
