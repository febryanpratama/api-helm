@extends('layouts.app')

@push('style')
<style>
  a:hover,a:focus{
      text-decoration: none;
      outline: none;
  }

  #accordion:before{
      content: "";
      width: 1px;
      height: 80%;
      background: #62DDBD;
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
      background: #62DDBD;
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
      border: 1px solid #62DDBD;
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
    border-left: 2px solid #62ddbd;
    padding-left: 10px;
    box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
  }
</style>
@endpush

@section('content')
<div class="container">
    <div class="clearfix border-left-element bg-white mt-4">
        <h5 class="p-2">@lang('label.product')</h5>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-sm-12 col-md-12 col-12">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="product-tab-1">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#product-tab-c-1" aria-expanded="true" aria-controls="product-tab-c-1">
                                <b>@lang('label.manage_course_institution')</b>
                            </a>
                        </div>
                    </div>

                    <div id="product-tab-c-1" class="panel-collapse collapse in {{ (request('tags') == 'kelola-lembaga-kursus') ? 'show' : '' }}" role="tabpanel" aria-labelledby="product-tab-1">
                        <div class="panel-body">
                            <p>
                                @lang('label.manage_your_own_course_institution')
                            </p>

                            <div class="row">
                                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12 text-center">
                                    <img src="{{ asset('img/landing/buka kursus dengan kelas & unggah.png') }}" alt="preview-img" style="width: 100%;">
                                </div>

                                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                                    @lang('label.manage_your_own_course_institution_description') <a href="{{ route('service.index') }}?tags=cara-buka-lembaga-kursus" class="text-dark">@lang('label.manage_your_own_course_institution_description_1')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="product-tab-2">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#product-tab-c-2" aria-expanded="true" aria-controls="product-tab-c-2">
                                <b>@lang('label.manage_course_package')</b>
                            </a>
                        </div>
                    </div>

                    <div id="product-tab-c-2" class="panel-collapse collapse in {{ (request('tags') == 'kelola-paket-kursus') ? 'show' : '' }}" role="tabpanel" aria-labelledby="product-tab-2">
                        <div class="panel-body">
                            <p>
                              @lang('label.manage_course_package')
                            </p>

                            <div class="row">
                                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12 text-center">
                                    <img src="{{ asset('img/landing/siapkan materi berupa video & doc.png') }}" alt="preview-img" style="width: 100%;">
                                </div>

                                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                                    @lang('label.manage_course_package_description') <a href="{{ route('faq', '1') }}" class="text-dark">@lang('label.manage_course_package_description_1')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="product-tab-4">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#product-tab-c-4" aria-expanded="true" aria-controls="product-tab-c-4">
                                <b>@lang('label.discussion_and_communication')</b>
                            </a>
                        </div>
                    </div>

                    <div id="product-tab-c-4" class="panel-collapse collapse in {{ (request('tags') == 'diskusi-dan-komunikasi') ? 'show' : '' }}" role="tabpanel" aria-labelledby="product-tab-4">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12 text-center">
                                    <img src="{{ asset('img/landing/Bisa mengajarmengobrol online.png') }}" alt="preview-img" style="width: 100%;">
                                </div>

                                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                                    @lang('label.discussion_and_communication_description')
                                </div>
                            </div>
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
