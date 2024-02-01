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

{{-- SRC --}}
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.2/plyr.css"/>
@endpush

@section('content')
<div class="container">
    <div class="clearfix border-left-element bg-white mt-4">
        <h5 class="p-2">@lang('label.service')</h5>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-sm-12 col-md-12 col-12">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="service-tab-1">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#service-tab-c-1" aria-expanded="true" aria-controls="service-tab-c-1">
                                <b>@lang('label.how_to_open_a_course')</b>
                            </a>
                        </div>
                    </div>

                    <div id="service-tab-c-1" class="panel-collapse collapse in {{ (request('tags') == 'cara-buka-lembaga-kursus') ? 'show' : '' }}" role="tabpanel" aria-labelledby="service-tab-1">
                        <div class="panel-body">
                            <p>
                                <video id="service-one-watch" class="services-watch" playsinline controls style="display: none;">
                                    <source src="{{ asset('mp4/cara buka lembaga kursus.mp4') }}">
                                </video>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="service-tab-2">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#service-tab-c-2" aria-expanded="true" aria-controls="service-tab-c-2">
                                <b>@lang('label.how_to_find_a_mentor')</b>
                            </a>
                        </div>
                    </div>

                    <div id="service-tab-c-2" class="panel-collapse collapse in {{ (request('tags') == 'cara-cari-mentor') ? 'show' : '' }}" role="tabpanel" aria-labelledby="service-tab-2">
                        <div class="panel-body">
                            <p>
                                <video id="service-two-watch" class="services-watch" playsinline controls style="display: none;">
                                    <source src="{{ asset('mp4/cara cari mentor.mp4') }}">
                                </video>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="service-tab-3">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#service-tab-c-3" aria-expanded="true" aria-controls="service-tab-c-3">
                                <b>@lang('label.how_to_find_course_packages')</b>
                            </a>
                        </div>
                    </div>

                    <div id="service-tab-c-3" class="panel-collapse collapse in {{ (request('tags') == 'cara-cari-paket-kursus') ? 'show' : '' }}" role="tabpanel" aria-labelledby="service-tab-3">
                        <div class="panel-body">
                            <p>
                                <video id="service-three-watch" class="services-watch" playsinline controls style="display: none;">
                                    <source src="{{ asset('mp4/CARA CARI PAKET KURSUS.mp4') }}">
                                </video>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="service-tab-4">
                        <div class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#service-tab-c-4" aria-expanded="true" aria-controls="service-tab-c-4">
                                <b>@lang('label.how_to_buy_a_cource_package')</b>
                            </a>
                        </div>
                    </div>

                    <div id="service-tab-c-4" class="panel-collapse collapse in {{ (request('tags') == 'cara-beli-paket-kursus') ? 'show' : '' }}" role="tabpanel" aria-labelledby="service-tab-4">
                        <div class="panel-body">
                            <p>
                                <video id="service-four-watch" class="services-watch" playsinline controls style="display: none;">
                                    <source src="{{ asset('mp4/Cara Beli Paket Kursus.mp4') }}">
                                </video>
                            </p>
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

@push('script')
{{-- SRC --}}
<script src="https://cdn.plyr.io/3.7.2/plyr.js"></script>

{{-- Configs --}}
<script>
    $(document).ready(function () {
        {{-- Initialize --}}
        let player = new Plyr('#service-one-watch')
        let player2 = new Plyr('#service-two-watch')
        let player3 = new Plyr('#service-three-watch')
        let player4 = new Plyr('#service-four-watch')

        $('.services-watch').css('display', '')
    })
</script>
@endpush