@extends('layouts.app')

@push('style')
<style>
	a {
	    color: black;
	}
</style>
@endpush

@section('content')
<div class="container bg-white mt-4" style="border-radius: 10px;">
	<div class="clearfix text-center pt-4">
		<h4><b>{{ $article->title }}</b></h4>
	</div>

	<p class="mb-4">
		{!! $article->description !!}
	</p>
</div>

{{-- Include File --}}
@include('landing.footer')
@stop