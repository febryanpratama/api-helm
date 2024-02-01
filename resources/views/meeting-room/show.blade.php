@extends('layouts.master')

@push('style')
<style>
	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="meet-id" value="{{ $meetingroom->id }}">

<div class="container">
	<div class="clearfix bg-white card-custom mb-4">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.back')</a>
				</div>

				<b>@lang('label.details_meeting_room')</b>
			</div>
		</div>
	</div>

	<div class="card card-custom">
		<div class="card-body">
			<div class="clearfix mb-1">
				<h6><b>@lang('label.meeting_room_name') :</b> {{ $meetingroom->name }}</h6>
			</div>

			<div class="clearfix">
				<h6><b>@lang('label.meeting_room_description') :</b></h6>
				{!! $meetingroom->description !!}
			</div>

			@if ($meetingroom->is_online == 1)
				<div class="clearfix mt-2">
					<h6><b>Link :</b> <a href="{{ $meetingroom->link }}" class="text-color" target="_blank">{{ $meetingroom->link }}</a> </h6>
				</div>
			@else
				<div class="clearfix mt-2">
					<h6><b>@lang('label.address') :</b> </h6>
					{{ $meetingroom->address }}
				</div>
			@endif

			<div class="clearfix mt-2">
				<h6><b>@lang('label.date') :</b> </h6>
				{{ date('d F Y H:i', $meetingroom->time) }}
			</div>

            @if(Session::has('message'))
				<div class="alert alert-danger mt-3 text-center">
	                {{ Session::get('message') }}
				</div>
			@endif
		</div>

		@if ($meetingroom->is_online == 0)
			@if (!$checkIn)
				<div class="card-footer bg-white">
					<button class="btn btn-sm btn-primary" id="check-in-meet" type="button">Check In</button>
				</div>
			@else
				<div class="card-footer bg-white">
					<p>
						Anda sudah melakukan Check In Meeting pada <i><b>{{ $checkIn->created_at->format('d F Y H:i:s') }}</b></i>
					</p>
				</div>
			@endif
		@endif
	</div>
</div>
@stop

@push('script')
<script>
		// Store
		$(document).on('click', '#check-in-meet', function (e) {
		    e.preventDefault()

		    // Disabled Button True
		    $('#check-in-meet').attr('disabled', true)

		    $.ajax({
		        url: `${baseUrl}/check-in/meet/store`,
		        type: 'POST',
		        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		        data: {
		        	meetId: $('#meet-id').val()
		        },
		        success: data => {
		        	// Disabled Button False
	                $('#check-in-meet').attr('disabled', false)
		        	
		            // Validate
		            if (!data.status) {
		                toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		                return 0
		            }

		            toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		            setTimeout(function () {
		            	location.reload()
		            }, 1000)
		        },
		        error: e => {
		            console.log(e)

		            // Disabled Button False
		            $('#check-in-meet').attr('disabled', false)

		            toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		        }
		    })
		})
</script>
@endpush