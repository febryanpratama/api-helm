@extends('layouts.app')

@push('style')
<style>
  a:hover,a:focus{
      text-decoration: none;
      outline: none;
  }

  .web-nav {
    display: none;
  }

  #accordion:before{
      content: "";
      width: 1px;
      height: 80%;
      background: #f95700;
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
      background: #f95700;
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
      border: 1px solid #f95700;
      position: absolute;
      top: 50%;
      left: -48px;
      transform: translateY(-50%);
  }

  #accordion .panel-title a{
      display: block;
      padding: 15px 55px 15px 30px;
      font-size: 20px;
      font-weight: 600;
      color: black;
      border: none;
      margin: 0;
      position: relative;
  }

  #accordion .panel-body{
      padding: 0 30px 15px;
      border: none;
      font-size: 14px;
      color: #305275;
      line-height: 28px;
  }

  .page-footer {
    display: none;
  }
</style>
@endpush

@section('content')
<section class="bg-white">
  <div class="container">
    <h4 class="text-center pt-4">@lang('label.frequently_asked_questions') (FAQ)</h4>

    <div class="row justify-content-center">
      <div class="col-sm-12 col-md-12 col-12">
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
          @foreach($faq as $val)
          <div class="panel panel-default">
              <div class="panel-heading" role="tab" id="faq-{{ $loop->iteration }}">
                  <h4 class="panel-title">
                      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#faq-c-{{ $loop->iteration }}" aria-expanded="true" aria-controls="faq-c-{{ $loop->iteration }}">
                          {{ $val->title }}
                      </a>
                  </h4>
              </div>

              <div id="faq-c-{{ $loop->iteration }}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="faq-{{ $loop->iteration }}">
                  <div class="panel-body">
                      <p>
                        {!! $val->details !!}
                      </p>
                  </div>
              </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Include File --}}
@include('landing.footer')
@endsection
