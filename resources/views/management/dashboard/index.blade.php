@extends('layouts.master')

@push('style')

@endpush

@section('content')
<div class="container">
	<div class="alert alert-info">
		<b>Selamat datang {{ auth()->user()->name }}!</b>

		<div class="float-right">
			<i class="fas fa-clock">
				<span id="show-time"></span>
			</i>
		</div>
	</div>
</div>
@stop

@push('script')
<script type="text/javascript">
	setInterval(function () {
		{{-- Initialize --}}
		let today = new Date();
		let time  = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds()

		$('#show-time').html(time)
	}, 1000)
</script>
@endpush