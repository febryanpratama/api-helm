@extends('layouts.master')

@push('style')
<style>
	.thead-color {
		background-color: #f6f9fc !important;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" class="form-control" id="partner-id" value="{{ $partner->id }}">
<input type="hidden" class="form-control" id="user-store-url" value="{{ route('course.user.partner.store') }}">
<input type="hidden" class="form-control" id="course-id" value="{{ $course->id }}">

<div class="container mb-4">
	<div class="card card-custom">
		<div class="card-header bg-white">

			@if (!$courseTransactionPartner)
			<div class="float-right">
				<button class="btn btn-sm btn-primary" id="add-users">
					@lang('label.add_users')
				</button>
			</div>
			@endif

			<b>Daftar Peserta</b>
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>#ID</th>
			                <th>Nama Peserta</th>
			                <th>Email</th>
			                <th>No Hp</th>
			                <th>Opsi</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach ($users as $val)
			        	<tr>
			        		<td>#{{ $val->uId }}</td>
			        		<td>{{ $val->name }}</td>
			        		<td>{{ $val->email }}</td>
			        		<td>{{ $val->phone }}</td>
			        		<td>
			        			@if ($val->role_id != 2)
				        			<button class="btn btn-sm btn-danger delete-users" id="{{ $val->uId }}" name="{{ $val->name }}">
				        				<i class="fas fa-trash"></i>
				        			</button>
			        			@endif
			        		</td>
			        	</tr>
			        	@endforeach
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
		</div>
	</div>
</div>

{{-- Include File --}}
@include('components.users-modal')
@stop

@push('script')
<script>
	$(document).on('click', '#add-users', function (e) {
		e.preventDefault()

		$('#users-modal').modal('show')
		$('#users-modal-aria').html('Tambah Data')
		$('#users-modal form')[0].reset()
	})

	// Store
	$(document).on('submit', '#users-modal form', function (e) {
		e.preventDefault()

		// Initialize
		let url 	= ``
		let action 	= $('#users-action').val()
		let name 	= $('#name').val()
		let email 	= $('#email').val()
			
		url = $('#user-store-url').val()

		if (action == 'add') {
			// url = $('#class-store-url').val()
		} else {
			// let id  = $('#class-id').val()
			// url = `${baseUrl}/majors/update/${id}`
		}

		// Validate
		if (!name) {
			toastr.error(`Nama harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!email) {
			toastr.error(`Email harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$('#users-btn-loading').attr('disabled', true)

		// Initialize
		let fd = new FormData(this)
		fd.append('partner_id', $('#partner-id').val())

		$.ajax({
		    url: `${url}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: fd,
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		    	$('#users-modal').modal('hide')

		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        // Disabled Button False
		        $('#users-btn-loading').attr('disabled', false)

		        setTimeout(function () {
		        	location.reload()
		        }, 1000)
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#users-btn-loading').attr('disabled', false)

		        toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	// Validate Destroy Class
	$(document).on('click', '.delete-users', function (e) {
	    e.preventDefault()

	    // Initialize
	    let userId   = $(this).attr('id')
	    let username = $(this).attr('name')

	    // Validate
	    Swal.fire({
	        text: `Hapus User ${username}?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyClass(userId)
	      }
	    })
	})

	// Destroy Class
	function destroyClass (userId) {
	    $.ajax({
	        url: `${baseUrl}/course-user-partner/destroy/${$('#partner-id').val()}/${userId}?course-id=${$('#course-id').val()}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        	return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#user-id-${userId}`).remove()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>
@endpush