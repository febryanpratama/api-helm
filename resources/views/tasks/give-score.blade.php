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
<input type="hidden" id="task-id" value="">

<div class="container">
	<div class="clearfix bg-white card-custom mb-4">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.back')</a>
				</div>

				<b>@lang('label.give_value')</b>
			</div>
		</div>
	</div>

	<div class="card card-custom" id="give-score-area">
		<div class="card-body">
			<div class="clearfix mb-1">
				<h6><b>Murid : {{ $taskattachment->user->name }}</b> </h6>
			</div>

			<div class="clearfix">
				<h6>
					<b>Laporan :</b> <a href="{{ $taskattachment->path }}" class="text-color" target="_blank">Preview</a>
				</h6>
			</div>

			<hr>

			<form method="POST">
				{{-- Hidden Element --}}
				<input type="hidden" name="task_attachments_id" value="{{ $taskattachment->id }}">

				<div class="form-group">
					<label for="">Nilai</label> <span class="text-danger">*</span>
					<input type="number" class="form-control" id="score" name="score" placeholder="Masukkan Nilai" value="{{ ($tma) ? $tma->score : '' }}">
				</div>

				<div class="form-group">
					<label for="">Tanggapan</label> <span class="text-danger">*</span>
					<textarea name="response" id="response" cols="30" rows="3" class="form-control" placeholder="Masukkan Tanggapan">{{ ($tma) ? $tma->response : '' }}</textarea>
				</div>

				<button class="btn btn-sm btn-primary" type="submit" id="loading-btn">Simpan</button>
			</form>
		</div>
	</div>
</div>
@stop

@push('script')
{{-- Report --}}
<script>
	$(document).on('submit', '#give-score-area form', function (e) {
		e.preventDefault()

		// Initialize
		let score 	 = $('#score').val()
		let response = $('#response').val()

		// Validate
		if (!score) {
			toastr.error(`Nilai harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!response) {
			toastr.error(`Tanggapan harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$('#loading-btn').attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/task/give-score/store`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: new FormData(this),
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	location.reload()
		        }, 1000)
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#loading-btn').attr('disabled', false)

		        toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>
@endpush