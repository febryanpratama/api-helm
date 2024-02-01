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
			<b>Take Down Data - Produk</b>

			<div class="float-right mr-3 mt-1">
				<div class="row">
				   	<div class="col-xs-8 col-xs-offset-2" id="search-course">
				   		<form method="GET">
					    	<div class="input-group">
					     		<input type="text" class="form-control" name="search" id="search-input" placeholder="Nama Produk...">

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
			                <th>Produk</th>
			                <th>Toko</th>
			                <th>Seller</th>
			                <th>Status Publish</th>
			                <th>Tanggal Dibuat</th>
			                <th>@lang('label.option')</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($courses as $val)
			        	<tr>
			        		<td>#{{ $val->id }}</td>
			        		<td>
			        			<a href="https://archiloka.com/detail-product/{{ $val->slug }}" target="_blank" class="text-color">
			        				{{ $val->name }}
			        			</a>
			        		</td>
			        		<td>{{ $val->user->company->Name }}</td>
			        		<td>{{ $val->user->name }}</td>
			        		<td>
			        			@if ($val->is_publish == 1)
			        				<span class="badge badge-success">Publish</span>
			        			@else
			        				<span class="badge badge-warning">Draft</span>
			        			@endif
			        		</td>
			        		<td>{{ $val->created_at->format('d F Y H:i') }}</td>
			        		<td>
			        			@if ($val->is_take_down == 0)
			        				<button class="btn btn-sm btn-danger take-down" id="take-down-data-{{ $val->id }}" course-id="{{ $val->id }}">Take Down</button>
			        			@else
			        				<button class="btn btn-sm btn-success activate-data" id="activate-data-{{ $val->id }}" course-id="{{ $val->id }}">Aktifkan</button>
			        			@endif
			        		</td>
			        	</tr>
			        	@endforeach
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{!! $courses->links() !!}
		</div>
	</div>
</div>
@stop

@push('script')
<script>
	$(document).on('click', '.take-down', function (e) {
		e.preventDefault()

		// Initialize
		let courseId = $(this).attr('course-id')

		// Validate
		Swal.fire({
		    text: `Take Down data dengan ID #${courseId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    takeDownData(courseId)
		  }
		})
	})

	function takeDownData(courseId) {
		// Disabled Button True
		$(`#take-down-data-${courseId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/take-down-data/${courseId}/true`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#take-down-data-${courseId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#take-down-data-${courseId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	$(document).on('click', '.activate-data', function (e) {
		e.preventDefault()

		// Initialize
		let courseId = $(this).attr('course-id')

		// Validate
		Swal.fire({
		    text: `Aktifkan Pake Kursus ID #${courseId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    activateData(courseId)
		  }
		})
	})

	function activateData(courseId) {
		// Disabled Button True
		$(`#activate-data-${courseId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/take-down-data/${courseId}/true`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#activate-data-${courseId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#activate-data-${courseId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}
</script>
@endpush