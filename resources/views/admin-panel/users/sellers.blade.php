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
			<b>Daftar Seller</b>

			{{-- <div class="float-right">
				<button class="btn btn-sm btn-company" data-toggle="modal" data-target="#search-transaction"><i class="fas fa-calendar"></i></button>
			</div> --}}
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="users-table">
			        <thead class="thead-color">
			            <tr>
			                <th>No</th>
			                <th>@lang('label.name')</th>
			                <th>Data</th>
			                <th>Opsi</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($users as $val)
			        	<tr>
			        		<td>#{{ $val->id }}</td>
			        		<td>{{ $val->name }}</td>
                            <td>
                                <ul>
                                    <li>Punya Toko? : <b>
                                        @if ($val->company)
                                            <span class="text-primary">Ya</span>
                                        @else
                                            <span class="">Tidak</span>
                                        @endif
                                    </b></li>
                                    <li>
                                        Total Alamat : <b>{{ count($val->address) }}</b>
                                    </li>
                                    <li>
                                        Total Portofolio : <b>{{ ($val->company) ? count($val->company->portfolio) : '-' }}</b>
                                    </li>
                                    <li>
                                        Total Foto Kantor : <b>{{ ($val->company) ? count($val->company->officePhotos) : '-' }}</b>
                                    </li>
									<li>
                                        Total Foto Tim : <b>{{ ($val->company) ? count($val->company->teamPhoto) : '-' }}</b>
                                    </li>
                                    <li>
                                        Total Transaksi : <b>{{ count($val->invoice) }}</b>
                                    </li>
                                    <li>
                                        Total Produk : <b>{{ count($val->course) }}</b>
                                    </li>
                                </ul>
                            </td>
                            <td>
			        			<button class="btn btn-sm btn-danger delete-user" id="delete-user-{{ $val->id }}" user-id="{{ $val->id }}">Hapus</button>
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
	$(document).on('click', '.delete-user', function (e) {
		e.preventDefault()

		// Initialize
		let userId = $(this).attr('user-id')

		// Validate
		Swal.fire({
            title: `Hapus Seller dengan ID #${userId}?`,
		    text: `Peringatan : Seller yang sudah dihapus tidak bisa di pulihkan!`,
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
		$(`#delete-user-${userId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/users/sellers/delete/${userId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#delete-user-${userId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#delete-user-${userId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}
</script>
@endpush