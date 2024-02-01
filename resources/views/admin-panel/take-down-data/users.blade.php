@extends('layouts.master')

@push('style')
<style>
	.thead-color {
		background-color: #f6f9fc !important;
	}
</style>
@endpush

@section('content')
<div class="container mb-4">
	<div class="card card-custom">
		<div class="card-header bg-white">
			<b>Take Down Data - Users</b>

			<div class="float-right mr-3 mt-1">
				<div class="row">
				   	<div class="col-xs-8 col-xs-offset-2" id="search-user">
				   		<form method="GET">
					    	<div class="input-group">
					     		<input type="text" class="form-control" name="search" id="search-input" placeholder="Cari...">

							    <span class="input-group-btn">
								    <button class="btn btn-primary" id="btn-search-loading" type="submit" style="border-radius: 0px !important;">
								      <span class="fas fa-search"></span>
								    </button>
								</span>
					    	</div>
				   		</form>
				   	</div>
			  	</div>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>#ID</th>
			                <th>Nama</th>
			                <th>Email</th>
			                <th>Role</th>
			                <th>Tanggal Dibuat</th>
			                <th>@lang('label.option')</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($users as $val)
			        	<tr>
			        		<td>#{{ $val->id }}</td>
			        		<td>{{ $val->name }}</td>
			        		<td>{{ $val->email }}</td>
			        		<td>
			        			@if ($val->role_id == 6)
			        				<span class="badge badge-info text-white">{{ $val->role->Name }}</span>
			        			@else
			        				<span class="badge badge-success">{{ $val->role->Name }}</span>
			        			@endif
			        		</td>
			        		<td>{{ $val->created_at->format('d F Y H:i') }}</td>
			        		<td>
			        			@if ($val->is_take_down == 0)
			        				<button class="btn btn-sm btn-danger take-down" id="take-down-data-{{ $val->id }}" user-id="{{ $val->id }}">Take Down</button>
			        			@else
			        				<button class="btn btn-sm btn-success activate-data" id="activate-data-{{ $val->id }}" user-id="{{ $val->id }}">Aktifkan</button>
			        			@endif
			        		</td>
			        	</tr>
			        	@endforeach
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{!! $users->links() !!}
		</div>
	</div>
</div>
@stop

@push('script')
<script>
	$(document).on('click', '.take-down', function (e) {
		e.preventDefault()

		// Initialize
		let userId = $(this).attr('user-id')

		// Validate
		Swal.fire({
		    text: `Take Down data dengan ID #${userId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    takeDownData(userId)
		  }
		})
	})

	function takeDownData(userId) {
		// Disabled Button True
		$(`#take-down-data-${userId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/take-down-data/users/${userId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#take-down-data-${userId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#take-down-data-${userId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	$(document).on('click', '.activate-data', function (e) {
		e.preventDefault()

		// Initialize
		let userId = $(this).attr('user-id')

		// Validate
		Swal.fire({
		    text: `Aktifkan Pake Kursus ID #${userId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    activateData(userId)
		  }
		})
	})

	function activateData(userId) {
		// Disabled Button True
		$(`#activate-data-${userId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/take-down-data/users/${userId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#activate-data-${userId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#activate-data-${userId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}
</script>
@endpush