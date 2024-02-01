@extends('layouts.app')

@push('style')
<style>
    footer {
        display: none;
    }
    
    .web-nav {
        display: none !important;
    }

    a:hover,a:focus{
      text-decoration: none;
      outline: none;
    }

  #accordion:before{
      content: "";
      width: 1px;
      height: 80%;
      background: #fa5600;
      position: absolute;
      top: 20px;
      left: 24px;
      bottom: 20px;
  }

  #accordion .panel{
      border: none;
      border-radius: 0;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
      margin: 0 0 12px 50px;
      position: relative;
  }

  #accordion .panel:before{
      content: "";
      width: 2px;
      height: 100%;
      background: #fa5600;
      position: absolute;
      top: 0;
      left: -2px;
  }

  #accordion .panel-heading{
      padding: 0;
      background: #fff;
      position: relative;
  }

  #accordion .panel-heading:before{
      content: "";
      width: 15px;
      height: 15px;
      border-radius: 50px;
      background: #fff;
      border: 1px solid #fa5600;
      position: absolute;
      top: 50%;
      left: -48px;
      transform: translateY(-50%);
  }

  #accordion .panel-title a{
      display: block;
      padding: 15px 55px 15px 30px;
      /*font-size: 20px;*/
      /*font-weight: 600;*/
      color: black;
      border: none;
      margin: 0;
      position: relative;
  }

  #accordion .panel-body{
      padding: 0 30px 1px;
      border: none;
      font-size: 14px;
      color: #305275;
      line-height: 28px;
  }

  .border-left-element {
    border-left: 2px solid #fa5600;
    padding-left: 10px;
    box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
  }

  .hover-faq:hover {
    background-color: #fa5600;
    color: white !important;
    cursor: pointer;
  }

  .hover-faq:hover a {
    color: white !important;
  }

  .btn-primarys {
    color: #fff !important;
    background-color: #f95700 !important;
    border-color: #f95700 !important;
  }
</style>
@endpush

@section('content')
<div class="container">
    <div class="clearfix border-left-element bg-white mt-4">
        <h5 class="p-2">@lang('label.help')</h5>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-sm-12 col-md-12 col-12">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="help-tab-1">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#help-tab-c-1" aria-expanded="true" aria-controls="help-tab-c-1">
                                <b>@lang('label.faq')</b>
                            </a>
                        </div>
                    </div>

                    <div id="help-tab-c-1" class="panel-collapse collapse in {{ (request('tags') == 'faq') ? 'show' : '' }}" role="tabpanel" aria-labelledby="help-tab-1">
                        <div class="panel-body">
                            <div class="row mb-2">
                                <!-- @foreach($categoryFaq as $val)
                                    <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-4">
                                        <a href="{{ route('faq', $val->id) }}" class="text-dark">
                                            <div class="card hover-faq">
                                                <div class="card-body text-center">
                                                    <b>{{ $val->name }}</b>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach -->

                                <div class="mt-4 col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-12 text-center">
                                    <a href="{{ route('faq', 1) }}" class="btn btn-md btn-primarys" target="_blank">Lihat FAQ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="help-tab-2">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#help-tab-c-2" aria-expanded="true" aria-controls="help-tab-c-2">
                                <b>@lang('label.privasi_policy')</b>
                            </a>
                        </div>
                    </div>

                    <div id="help-tab-c-2" class="panel-collapse collapse in {{ (request('tags') == 'kebijakan-privasi') ? 'show' : '' }}" role="tabpanel" aria-labelledby="help-tab-2">
                        <div class="panel-body">
                            <h5 class="text-center mt-3"><b>@lang('label.privasi_policy')</b></h5>

                            @lang('label.privasi_policy_description')
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="help-tab-3">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#help-tab-c-3" aria-expanded="true" aria-controls="help-tab-c-3">
                                <b>@lang('label.term_and_conditions')</b>
                            </a>
                        </div>
                    </div>

                    <div id="help-tab-c-3" class="panel-collapse collapse in {{ (request('tags') == 'syarat-dan-ketentuan') ? 'show' : '' }}" role="tabpanel" aria-labelledby="help-tab-3">
                        <div class="panel-body">
                            <h5 class="text-center mt-3"><b>@lang('label.term_and_conditions')</b></h5>

                            @include('landing.syarat-ketentuan')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include File --}}
@include('landing.footer')
@endsection
