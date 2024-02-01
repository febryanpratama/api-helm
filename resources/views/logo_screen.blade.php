@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 justify-content-center">
            <img src="{{ asset('img/new2.png') }}" alt="" srcset="">
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        setTimeout(function(){
            window.location.href = '/auth';
         }, 3000);
    </script>
@endpush
