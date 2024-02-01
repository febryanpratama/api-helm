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
			<b>Take Down Data - Video Higlight</b>

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
			                <th>Name</th>
			                <th>Preview</th>
			                <th>@lang('label.option')</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($subjects as $val)
			        	<tr>
			        		<td>#{{ $val->ID }}</td>
			        		<td>{{ $val->Name }}</td>
			        		<td>
			        			<video controls width="250">
			        			    <source src="{{ $val->Path }}" type="video/mp4">
			        			</video>
			        		</td>
			        		<td>
			        			@if ($val->IsTakeDown == 0)
			        				<button class="btn btn-sm btn-danger take-down" id="take-down-data-{{ $val->ID }}" user-id="{{ $val->ID }}">Take Down</button>
			        			@else
			        				<button class="btn btn-sm btn-success activate-data" id="activate-data-{{ $val->ID }}" user-id="{{ $val->ID }}">Aktifkan</button>
			        			@endif
			        		</td>
			        	</tr>
			        	@endforeach
			        </tbody>
			    </table>
			</div>
		</div>
		{{-- <div class="card-footer bg-white">
			{!! $users->links() !!}
		</div> --}}
	</div>
</div>
@stop

@push('script')
<script>
	$(document).on('click', '.take-down', function (e) {
		e.preventDefault()

		// Initialize
		let subjectId = $(this).attr('user-id')

		// Validate
		Swal.fire({
		    text: `Take Down data dengan ID #${subjectId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    takeDownData(subjectId)
		  }
		})
	})

	function takeDownData(subjectId) {
		// Disabled Button True
		$(`#take-down-data-${subjectId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/take-down-data/video/${subjectId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#take-down-data-${subjectId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#take-down-data-${subjectId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	$(document).on('click', '.activate-data', function (e) {
		e.preventDefault()

		// Initialize
		let subjectId = $(this).attr('user-id')

		// Validate
		Swal.fire({
		    text: `Aktifkan Pake Kursus ID #${subjectId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    activateData(subjectId)
		  }
		})
	})

	function activateData(subjectId) {
		// Disabled Button True
		$(`#activate-data-${subjectId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/take-down-data/video/${subjectId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#activate-data-${subjectId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#activate-data-${subjectId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}
</script>
@endpush