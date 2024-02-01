@extends('layouts.master')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
			<div class="card card-custom">
				<div class="card-header bg-white">
					<b>@lang('label.account_profile')</b>
				</div>
				<div class="card-body">
					<form method="POST" enctype="multipart/form-data">
						<div class="form-group">
							<label for="">@lang('label.name')</label> <span class="text-danger">*</span>
							<input type="text" class="form-control" id="account-name" name="name" placeholder="@lang('label.enter_name')" value="{{ auth()->user()->name }}">
						</div>

						<div class="form-group">
							<label for="">@lang('label.email')</label> <span class="text-danger">*</span>
							<input type="text" class="form-control" placeholder="@lang('label.enter_email')" value="{{ auth()->user()->email }}" readonly="">
						</div>

						<div class="form-group">
							<label for="">Curriculum Vitae (CV) </label> <br>
							<button class="btn btn-info btn-sm text-white" id="cv-btn-file" type="button"><i class="fa fa-camera"></i></button>
							<span id="span-name-file-cv" class="pl-2">
								@if(auth()->user()->curriculum_vitae)
									<a href="{{ auth()->user()->curriculum_vitae }}" class="text-color" target="_blank">Preview</a>
									|
									<a href="javascript:void(0)" class="text-danger" id="delete-cv">Hapus CV</a>
								@else
									<i>*@lang('label.not_file_selected')</i>
								@endif
							</span>
							<input type="file" name="curriculum_vitae" class="form-control hide-element" id="file-cv" accept="image/png,image/jpg,image/jpeg,application/pdf,.doc,.docs">
						</div>

						<button class="btn btn-sm btn-company" type="submit" id="save-profile">@lang('label.save')</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@push('script')
<script>
	// Trigger Input Type File
	$(document).on('click', '#cv-btn-file', function () {
	    $('#file-cv').click()
	})

	// Get Full Path
	$(document).on('change', '#file-cv', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-cv').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-cv').html("<i>*@lang('label.not_file_selected')</i>")
	    }
	})

	// Update
	$(document).on('submit', '.card-body form', function (e) {
		e.preventDefault()

		// Initalize
		let name = $('#account-name').val()

		// Validate
		if (!name) {
			toastr.error(`Nama harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$('#save-profile').attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/profil/update/${userId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: new FormData(this),
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		    	// Disabled Button False
		    	$('#save-profile').attr('disabled', false)

		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	location.reload()
		        }, 1000)
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#save-profile').attr('disabled', false)

		        toastr.error(`Data gagal diperbaharui`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	// Validate Destroy Class
	$(document).on('click', '#delete-cv', function (e) {
	    e.preventDefault()

	    // Validate
	    Swal.fire({
	        text: `Hapus Curriculum Vitae (CV)`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyCv()
	      }
	    })
	})

	// Destroy Class
	function destroyCv () {
	    $.ajax({
	        url: `${baseUrl}/profil/delete/cv`,
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

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>
@endpush