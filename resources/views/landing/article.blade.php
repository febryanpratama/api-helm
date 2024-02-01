@extends('layouts.app')

@section('content')
<div class="container-fluid bg-white">
  <div class="container p-5">
    <div class="clearfix text-center mt-2 mb-5">
        <h3>Daftar Artikel</h3>
    </div>

    <div class="row">
      @foreach($articles as $val)
      <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-12 mb-4">
        <div class="card h-100" style="border: 0px;">
          <div class="card-header bg-white" style="border: 0px !important;">
            <img src="{{ ($val->thumbnail) ? $val->thumbnail : 'https://media.istockphoto.com/vectors/no-thumbnail-image-vector-graphic-vector-id1147544806?k=20&m=1147544806&s=170667a&w=0&h=5rN3TBN7bwbhW_0WyTZ1wU_oW5Xhan2CNd-jlVVnwD0=' }}" alt="thumbnail-img" class="">
          </div>
          <div class="card-body" style="border: 0px;">
            <h6><b>{{ $val->title }}</b></h6>
            <div class="clearfix">
              <div class="float-left pr-2"><i class="fa fa-user" style="color: #dfdfdf;"></i> Tugas.id</div>
              <div class="float-left"><i class="fa fa-calendar" style="color: #dfdfdf;"></i> {{ $val->created_at->format('d F Y') }}</div>
            </div>

            <div class="mt-2">
              <a href="{{ URL::to('/') }}/artikel/read/{{ $val->slug }}" class="text-dark">Selengkapnya <i class="fa fa-angle-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Include File --}}
@include('landing.footer')
@stop