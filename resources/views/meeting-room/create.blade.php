@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css" integrity="sha512-rxThY3LYIfYsVCWPCW9dB0k+e3RZB39f23ylUYTEuZMDrN/vRqLdaCBo/FbvVT6uC2r0ObfPzotsfKF9Qc5W5g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="meeting-room-store-url" value="{{ route('meeting.room.store') }}">
<input type="hidden" id="redirect-back" value="{{ url()->previous() }}">

<div class="container mb-4">
	<div class="clearfix bg-white card-custom mb-4">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.back')</a>
				</div>

				<b>@lang('label.add_meeting_room')</b>
			</div>
		</div>
	</div>

	<div class="card card-custom" id="meeting-room-area">
		<form method="POST" enctype="multipart/form-data">
			{{-- Hidden Element --}}
			<input type="hidden" name="course_id" value="{{ $majors->IDCourse }}">
			<input type="hidden" name="session_id" value="{{ $majors->ID }}">
			{{-- <input type="hidden" name="theory_id" value="{{ $subject->ID }}"> --}}

			<div class="card-body">
				<div class="row">
					<div class="col-sm-12 col-12">
						<div class="form-group">
							<label for="">@lang('label.meeting_room_name') <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="name" id="name" placeholder="@lang('label.enter_meeting_room_name')">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12 col-12">
						<div class="form-group">
							<label for="task">@lang('label.meeting_room_description')</label>
							<textarea name="description" class="form-control tinymce-element" placeholder="@lang('label.enter_meeting_room_description')" id="note-detail-task" rows="3"></textarea>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
						<label for="">@lang('label.meeting_room_type') <span class="text-danger">*</span></label>
						<select name="is_online" id="is-online" class="form-control">
							<option value="1">Online</option>
							<option value="0">Offline</option>
						</select>
					</div>

					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
						<div class="row">
							<div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-12">
								<div class="form-group">
									<label for="">@lang('label.date') <span class="text-danger">*</span></label>
									<input type="text" name="date" class="form-control date" id="date" placeholder="Ex: 01/01/2022">
								</div>
							</div>

							<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-12">
								<div class="form-group">
									<label for="">@lang('label.time') <span class="text-danger">*</span></label><br>
									<input type="text" name="time" class="form-control time" id="time" placeholder="Ex: 12:00">
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group mt-3 online-element">
					<label for="">Link <span class="text-danger">*</span></label>
					<input type="text" name="link" class="form-control" value="{{ env('SITE_URL') }}/meet/{{ strtolower(str_replace(' ', '-', $majors->Name)) }}-{{ $randomString }}" readonly="">
				</div>

				<div class="row online-element">
					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
						<div class="form-group">
							<label for="">@lang('label.expiration_date_link')</label>
							<input type="text" name="date_exp" class="form-control date" id="date-exp" placeholder="Ex: 01/01/2022">
						</div>
					</div>

					<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
						<div class="form-group">
							<label for="">@lang('label.link_expiration_time')</label><br>
							<input type="text" name="time_exp" class="form-control time" id="time-exp" placeholder="Ex: 12:00">
						</div>
					</div>
				</div>

				<div class="form-group offline-element mt-3" style="display: none;" id="input-address">
					<label for="">@lang('label.address') <span class="text-danger">*</span></label>
					<input type="text" name="address" id="address" class="form-control" placeholder="ex: Jl. Asia Afrika Bandung">
				</div>
				
				<div class="form-group mt-4">
					<button class="btn btn-sm btn-primary" id="meeting-room-btn-loading">@lang('label.save')</button>
				</div>
			</div>
		</form>
	</div>
</div>
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdn.tiny.cloud/1/9r22aawjna4i5xiq305h1avqyndi0pzuxu0aysqdgkijvnwh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-clock-timepicker@2.6.2/jquery-clock-timepicker.min.js"></script>

{{-- Configs --}}
<script>
	// TinyMce
	tinymce.init({
        selector: '.tinymce-element',
        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
        image_title: true,
        automatic_uploads: true,
        height : "380",
        images_upload_url: `{{ route('tinymce.upload.image', ['_token' => csrf_token()]) }}`,
        file_picker_types: 'image',
        file_picker_callback: function(cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function() {
                var file = this.files[0];

                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function () {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
            };
            input.click();
        }
    })
</script>

<script>
	$(document).on('change', '#is-online', function () {
		if (this.value == 0) {
			$('.offline-element').css('display', '')
			$('.online-element').css('display', 'none')
		} else {
			$('.online-element').css('display', '')
			$('.offline-element').css('display', 'none')
		}
	})

	$('.date').datepicker({
	    autoclose: true,
	    todayHighlight: true
	})

	$('.time').clockTimePicker({
		duration: true,
		durationNegative: true,
		precision: 5,
		i18n: {
			cancelButton: 'Abbrechen'
		},
		onAdjust: function(newVal, oldVal) {}
	})
</script>

<script>
	// Store
	$(document).on('submit', '#meeting-room-area form', function (e) {
	    e.preventDefault()

	    // Initialize
	    let url  	 = $('#meeting-room-store-url').val()
	    let name 	 = $('#name').val()
	    let isOnline = $('#is-online option:selected').val()
	    let date 	 = $('#date').val()
	    let time 	 = $('#time').val()

	    // Validate
	    if (!name) {
	        toastr.error(`@lang('label.meeting_room_name') harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	        return 0
	    }

	    if (!date) {
	    	toastr.error(`@lang('label.date') harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (!time) {
	    	toastr.error(`@lang('label.time') harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (isOnline == 0) {
	    	// Initialize
	    	let address = $('#address').val()

	    	if (!address) {
	    		toastr.error(`@lang('label.address') harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		return 0
	    	}
	    }

	    // Disabled Button True
	    $('#meeting-room-btn-loading').attr('disabled', true)

	    $.ajax({
	        url: `${url}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        data: new FormData(this),
	        contentType: false,
	        cache: false,
	        processData: false,
	        dataType: 'json',
	        success: data => {
	        	// Disabled Button False
                $('#meeting-room-btn-loading').attr('disabled', false)

	            // Validate
	            if (!data.status) {
	                toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	                return 0
	            }

	            toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	            setTimeout(function () {
	            	window.location = `${$('#redirect-back').val()}?tags=session-and-theory`
	            }, 1000)
	        },
	        error: e => {
	            console.log(e)

	            // Disabled Button False
	            $('#meeting-room-btn-loading').attr('disabled', false)

	            toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	        }
	    })
	})
</script>
@endpush