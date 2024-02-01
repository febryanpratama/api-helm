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

<div class="container">
	<div class="clearfix bg-white card-custom mb-4">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.back')</a>
				</div>

				<b>@lang('label.details_task')</b>
			</div>
		</div>
	</div>

	<div class="card card-custom" id="task-area">
		<div class="card-body">
			<div class="clearfix mb-1">
				<h6><b>@lang('label.task') :</b> {{ $task->name }}</h6>
			</div>

			<div class="clearfix">
				<h6><b>@lang('label.details_task') :</b></h6>
				{!! $task->detail !!}
			</div>

			<div class="clearfix">
				<h6>
					<b>File :</b>
					@if($task->taskAttachment)
						<a href="{{ $task->taskAttachment->path }}" class="text-color" target="_blank">Preview</a>
					@else
						-
					@endif
				</h6>
			</div>
		</div>
		@if (auth()->user()->role_id == 6)
			<div class="card-footer bg-white" id="report-area">
				@if (!$taskAttachment)
					<form method="POST" enctype="multipart/form-data">
						{{-- Hidden Element --}}
						<input type="hidden" name="task_id" value="{{ $task->id }}">

						<div class="form-group">
							<label for="">@lang('label.upload_report') <span class="text-danger">*</span> <i class="text-info">(*PDF, MS Word, PNG, JPG dan JPEG)</i></label> <br>

							<button class="btn btn-info btn-sm text-white" id="report-btn-file" type="button"><i class="fa fa-camera"></i></button>
							<span id="span-name-file-report" class="pl-2"><i>*Tidak ada file yang dipilih</i></span>
							<input type="file" name="upload_file" class="form-control hide-element" id="file-report" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf,image/jpg,image/jpeg,image/png">
						</div>

						<button class="btn btn-sm btn-primary" type="submit" id="report-btn-loading">@lang('label.save')</button>
					</form>
				@else
					<b>Laporan :</b> <a href="{{ $taskAttachment->path }}" class="text-color" target="_blank">Preview</a>

					@if ($taskAttachment->taskmentorassessment)
						<hr>
						<p>
							<b>Penilaian Mentor :</b>
							<p>
								Tanggapan : <b>{{ $taskAttachment->taskmentorassessment->response }}</b>
								<br>
							</p>
							<p>
								Nilai : <b>{{ $taskAttachment->taskmentorassessment->score }}</b>
							</p>
						</p>
					@endif
				@endif
			</div>
		@endif
	</div>

	@if (auth()->user()->role_id == 1)
	<div class="card card-custom mt-4 mb-4">
		<div class="card-header bg-white">@lang('label.student_report_data')</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-borderless">
					<thead>
						<tr>
							<th>No</th>
							<th>@lang('label.students')</th>
							<th>@lang('label.report_files')</th>
							<th>@lang('label.option')</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($listTaskAttachment as $val)
						<tr>
							<td>{{ $loop->iteration }}</td>
							<td>{{ $val->user->name }}</td>
							<td>
								<a href="{{ $val->path }}" class="text-color" target="_blank">Preview</a>
							</td>
							<td>
								@if($val->taskmentorassessment)
									<a href="{{ route('task.giveScore', $val->id) }}" class="btn btn-primary btn-sm">@lang('label.edit_value')</a>
									<button class="btn btn-danger btn-sm delete-score-value" id="delete-{{ $val->taskmentorassessment->id }}" tma-id="{{ $val->taskmentorassessment->id }}">Hapus Nilai</button>
								@else
									<a href="{{ route('task.giveScore', $val->id) }}" class="btn btn-info btn-sm text-white">@lang('label.give_value')</a>
								@endif
							</td>
						</tr>
						@empty
						<tr>
							<td colspan="3" class="text-center">@lang('label.no_data')</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{!! $listTaskAttachment->links() !!}
		</div>
	</div>
	@endif
</div>
@stop

@push('script')
<script>
	// Trigger Input Type File
	$(document).on('click', '#report-btn-file', function () {
	    $('#file-report').click()
	})

	// Get Full Path
	$(document).on('change', '#file-report', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-report').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-report').html('<i>*Tidak ada file yang dipilih</i>')
	    }
	})
</script>

{{-- Report --}}
<script>
	$(document).on('submit', '#report-area form', function (e) {
		e.preventDefault()

		// Initialize
		let file = $('#file-report')[0].files

		// Validate
		if (file.length == 0) {
			toastr.error(`Laporan harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Initialize
		let fd = new FormData(this)
		fd.append('taskId', $('#task-id').val())

		// Disabled Button True
		$('#report-btn-loading').attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/task/upload/report`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: fd,
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		        toastr.success(`${data.message}`, 'Sukses!', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	location.reload()
		        }, 1000)
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#report-btn-loading').attr('disabled', false)

		        toastr.error(`Data gagal disimpan`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	// Validate Destroy Course
	$(document).on('click', '.delete-score-value', function (e) {
	    e.preventDefault()

	    // Initialize
	    let id = $(this).attr('tma-id')

	    // Validate
	    Swal.fire({
	        text: `Hapus data ini?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyScoreValue(id)
	      }
	    })
	})

	// Destroy Course
	function destroyScoreValue (id) {
		// Disabled True
		$(`#delete-${id}`).attr('disabled', true)

	    $.ajax({
	        url: `${baseUrl}/task/give-score/delete/${id}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		location.reload()
	        },
	        error: e => {
	            console.log(e)

	            // Disabled False
	            $(`#delete-${id}`).attr('disabled', false)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>
@endpush