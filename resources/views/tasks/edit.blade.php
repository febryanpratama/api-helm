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
<input type="hidden" id="task-id" value="{{ $task->id }}">
<input type="hidden" id="redirect-back" value="{{ url()->previous() }}">

<div class="container">
	<div class="clearfix bg-white card-custom mb-4">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.back')</a>
				</div>

				<b>@lang('label.edit_task')</b>
			</div>
		</div>
	</div>

	<div class="card card-custom" id="task-area">
		<form method="POST" enctype="multipart/form-data">
			<div class="card-body">
	      		<div class="row">
	      			<div class="col-12 col-sm-12 col-md-12 col-xl-12">
	      				<div class="form-group">
	      				    <label for="task">@lang('label.task_name')</label> <span class="text-danger">*</span>
	      				    <input type="text" name="task" class="form-control" placeholder="@lang('label.enter_task_name')" value="{{ $task->name }}" id="task_name" autofocus>
	      				</div>
	      			</div>
	      		</div>
									
				<div class="row">
	      			<div class="col-12 col-sm-12 col-md-12 col-xl-12">
	      				<div class="form-group file-upload-task">
		                <label for="">File <i class="text-info">*JPG/MP4/PDF (@lang('label.maks_file_upload_task'))</i></label><br>
		                <button class="btn btn-info btn-sm text-white" id="task-btn-file" type="button"><i class="fa fa-camera"></i></button>
		                <span id="span-name-file-task" class="pl-2">
		                	<i>
			                	@if ($task->taskAttachment)
			                		<a href="{{ $task->taskAttachment->path }}" class="text-color" target="_blank" title="">Preview</a>
			                	@else
			                		*@lang('label.not_file_selected')
			                	@endif
			                </i>
			            </span>
		                
		                <input type="file" name="upload_file" class="form-control hide-element" id="file-task" accept="image/png,image/jpg,image/jpeg,video/mp4,video/avi,application/pdf">
		              </div>
	      			</div>
	      		</div>

		        <div class="row">
		            <div class="col-12 col-sm-12 col-md-12 col-xl-12">
		              <div class="form-group">
		                <label for="task">@lang('label.details')</label>
		                <textarea name="note" class="form-control tinymce-element" placeholder="@lang('label.enter_task_details')" id="note-detail-task" rows="3">{{ $task->detail }}</textarea>
		              </div>
		            </div>
		        </div>

		        <button class="btn btn-sm btn-primary" type="submit" id="task-btn-loading">@lang('label.save')</button>
			</div>
		</form>
	</div>
</div>
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdn.tiny.cloud/1/9r22aawjna4i5xiq305h1avqyndi0pzuxu0aysqdgkijvnwh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

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

{{-- Task --}}
<script>
	// Trigger Input Type File
	$(document).on('click', '#task-btn-file', function () {
	    $('#file-task').click()
	})

	// Get Full Path
	$(document).on('change', '#file-task', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-task').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-task').html('<i>*Tidak ada file yang dipilih</i>')
	    }
	})

	// Store
	$(document).on('submit', '#task-area form', function (e) {
	    e.preventDefault()

	    // Initialize
	    let task        = $('#task_name').val()
	    let startDate   = $('#startDate').val()
	    let startTime   = $('#startTime').val()
	    let url         = $('#task-store-url').val()

	    // Validate
	    if (!task) {
	        toastr.error(`Nama Tugas harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

	        return 0
	    }

	    // if (!startDate) {
	    //     toastr.error(`Waktu Mulai harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    //     return 0
	    // }

	    // if (!startTime) {
	    //     toastr.error(`Jam Mulai harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    //     return 0
	    // }

	    // Disabled Button True
	    $('#task-btn-loading').attr('disabled', true)

	    $.ajax({
	        url: `${baseUrl}/task/update/${$('#task-id').val()}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        data: new FormData(this),
	        contentType: false,
	        cache: false,
	        processData: false,
	        dataType: 'json',
	        success: data => {
	        	// Disabled Button False
                $('#task-btn-loading').attr('disabled', false)

	            // Validate
	            if (!data.status) {
	                toastr.error(`${data.message}`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

	                return 0
	            }

	            toastr.success(`${data.message}`, 'Sukses!', {closeButton:!0, tapToDismiss:!1, rtl:o})

	            setTimeout(function () {
	            	window.location = `${$('#redirect-back').val()}?tags=session-and-theory`
	            }, 1000)
	        },
	        error: e => {
	            console.log(e)

	            // Disabled Button False
	            $('#task-btn-loading').attr('disabled', false)

	            toastr.error(`500 Internal Server Error!`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
	        }
	    })
	})
</script>
@endpush