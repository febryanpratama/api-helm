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
{{-- Hidden Input --}}
<input type="hidden" class="form-control" id="partner-id" value="{{ $partner->id }}">
<input type="hidden" class="form-control" id="user-store-url" value="{{ route('course.user.partner.store') }}">
<input type="hidden" class="form-control" id="course-id" value="{{ $course->id }}">

<div class="container">
	<div class="clearfix bg-white card-custom mb-4">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.back')</a>
				</div>

				<b>Daftar Peserta - Mitra {{ $partner->name }}</b>
			</div>
		</div>
	</div>

	<div class="card card-custom">
		<div class="card-header bg-white">
			<button class="btn btn-sm btn-primary float-right" id="add-users">
				@lang('label.add_users')
			</button>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-responsive-md">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama</th>
							<th>Email</th>
							<th>Role</th>
							<th>Opsi</th>
						</tr>
					</thead>
					<tbody>
						@forelse($users as $val)
						<tr id="user-id-{{ $val->id }}">
							<td>{{ $loop->iteration }}</td>
							<td>
								{{ $val->name }}
							</td>
							<td>
								{{ $val->email }}
							</td>
							<td>
								@if ($val->role_id == 2)
									<span class="badge badge-success">{{ $val->role_name }}</span>
								@else
									<span class="badge badge-info text-white">{{ $val->role_name }}</span>
								@endif
							</td>
							<td>
								@if ($val->role_id != 2)
									<a href="javascript:void(0)" class="edit-users" id="{{ $val->id }}" name="{{ $val->name }}">
										<i class="fas fa-edit text-info"></i>
									</a>

									<a href="javascript:void(0)" class="delete-users" id="{{ $val->id }}" name="{{ $val->name }}">
										<i class="fas fa-trash text-danger"></i>
									</a>
								@endif
							</td>
						</tr>
						@empty
						<tr>
							<td colspan="5" class="text-center">@lang('label.no_data')</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
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
		$('#user-action').val('add')
		$('#users-btn-loading').html(`@lang('label.add')`)
	})

	// Store
	$(document).on('submit', '#users-modal form', function (e) {
		e.preventDefault()

		// Initialize
		let url 	= ``
		let action 	= $('#user-action').val()
		let name 	= $('#name').val()
		let email 	= $('#email').val()
			

		if (action == 'add') {
			url = $('#user-store-url').val()
		} else {
			let id  = $('#user-id').val()
			url = `${baseUrl}/course-user-partner/update/${id}`
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
		        // Disabled Button False
		        $('#users-btn-loading').attr('disabled', false)
		    	
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
		    	}

		    	$('#users-modal').modal('hide')

		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

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

	// Edit
	$(document).on('click', '.edit-users', function (e) {
		e.preventDefault()

		$('#user-action').val('edit')
		$('#users-modal-aria').html('Edit Data')
		$('#users-btn-loading').html(`@lang('label.edit')`)

	    // Call Function
		editMember($(this).attr('id'))
	})

	function editMember(usersId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/course-user-partner/edit/${usersId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Append Value
		    	$('#user-id').val(data.data.id)
		    	$('#name').val(data.data.name)
		    	$('#email').val(data.data.email)
		    	$('#phone').val(data.data.phone)

	    		$('#users-modal').modal({backdrop: 'static', keyboard: false})
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				$(`#${classId}`).attr('disabled', false)
		    }
		})
	}

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