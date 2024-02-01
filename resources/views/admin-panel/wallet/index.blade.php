@extends('layouts.master')

@push('style')
<style>
	.border-custom-balance {
		/*border: 1px solid black;*/
		padding: 5px;
		background-color: #62DDBD;
		color: white;
		border-radius: 10px;
	}

	.balance-in-icon {
    	width: 30px;
        height: 30px;
        background: rgba(234,84,85,.12) !important;
        color: #EA5455 !important;
        display: flex;
        align-items: center;
        justify-content: center;
	}

	.balance-out-icon {
		width: 30px;
	    height: 30px;
	    background: #28C76F !important;
	    background: rgba(40,199,111,.12) !important;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	}

	.circle-icon{
		width: 60px;
	}

	.circle-icon .icon{
		width: 60px;
		height: 60px;
		background: #ecf0f4;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.border-radius-100{
		-webkit-border-radius: 100%;
		-moz-border-radius: 100%;
		border-radius: 100%;
	}

	.table td {
		border-top: 0 !important;
	}
</style>
@endpush

@section('content')
<div class="container">
	<div class="row">
		{{-- <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 mb-4">
			<div class="card card-custom">
				<div class="card-body">
					<div class="text-center">
						<h6>@lang('label.total_balance_application')</h6>
						<h5><b>{{ rupiah($totalBalance) }}</b></h5>
					</div>

					<div class="clearfix mt-4 text-center">
						<div class="alert alert-info" style="font-size: 14px;">Total Saldo dari Komisi (5%) transaksi.</div>
					</div>
				</div>
			</div>
		</div> --}}

		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 mb-4">
			<div class="card card-custom h-100">
				<div class="card-body">
					<div class="text-center">
						<h6>@lang('label.total_balance_all_user')</h6>
						<h5><b>{{ rupiah($balance) }}</b></h5>
					</div>

					{{-- <div class="clearfix mt-4">
						<div class="row justify-content-center">
							<div class="col-4 border-custom-balance p-2">
								<h6>@lang('label.balance_increase')</h6>
								<b>{{ rupiah($balanceIn) }}</b>
							</div>

							<div class="col-4 border-custom-balance ml-4 p-2" style="background-color: #F5F6FA !important; color: black !important;">
								<h6>@lang('label.withdrawal_balance')</h6>
								<b>{{ rupiah(str_replace('-', '', $balanceOut)) }}</b>
							</div>
						</div>
					</div> --}}
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			<div class="card card-custom">
				<div class="card-header bg-white">
					Daftar User Withdraw
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-responsive-md">
							@forelse($history as $val)
							<tr>
								<td>#{{ $val->id }}</td>
								<td>
									{{ $val->user->name }}
								</td>
								<td>
									<div class="circle-icon">
										<div class="balance-in-icon border-radius-100 font-24 text-danger">
											<i class="fa fa-arrow-up" aria-hidden="true"></i>
											{{ $val->details }}
										</div>
									</div>
								</td>
								<td>
									<div>Informasi Bank</div>
									<div class="clearfix">
										<b>
											<span>{{ $val->bank_name }}</span>
											<br>
											<span>{{ $val->account_number }}</span>
											<br>
											<span>Atas Nama : {{ $val->account_holder_name }}</span>
										</b>
									</div>
								</td>
								<td>
									{{ $val->created_at->format('d F Y') }}
									<div style="font-size: 12px;">{{ $val->created_at->format('H:i:s') }}</div>
								</td>
								<td>{{ rupiah($val->balance) }}</td>
								<td>
									@if ($val->is_verified == 1)
										<div class="badge badge-success">Approved</div>
									@elseif ($val->is_verified == 2)
										<div class="badge badge-danger">Rejected</div>
									@else
										<div class="badge badge-info text-white">Pending</div>
									@endif
								</td>
								<td>
									@if ($val->is_verified == 0)
										<button class="btn btn-sm btn-primary approve-withdraw" id="{{ $val->id }}" w-id="{{ $val->id }}">Approve</button>
									@elseif ($val->is_verified == 1)
										<a href="{{ $val->evidence_of_transfer }}" target="_blank" class="text-success">Bukti Transfer <i class="fa fa-external-link" aria-hidden="true"></i></a>
									@endif
								</td>
							</tr>
							@empty
							<tr>
								<td colspan="5" class="text-center">@lang('label.no_data')</td>
							</tr>
							@endforelse
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="approve-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header">
        		<h5 class="modal-title" id="exampleModalLabel">Upload Bukti Transfer</h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
      		</div>
	      	<form method="POST" enctype="multipart/form-data">
	      		{{-- Hidden Element --}}
	      		<input type="hidden" name="id" id="approval-id">

		    	<div class="modal-body">
		    		<div class="form-group">
		    			<label for="">Bukti Transfer <span class="text-danger">*</span></label>
		    			<input type="file" id="evidence-of-transfer" name="evidence_of_transfer" class="form-control">
		    		</div>
	      		</div>
				<div class="modal-footer">
		        	<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
		        	<button type="submit" class="btn btn-sm btn-primary" id="btn-loading">Simpan</button>
		      	</div>
	      	</form>
    	</div>
  	</div>
</div>
@stop

@push('script')
<script>
	$(document).on('click', '.approve-withdraw', function (e) {
		e.preventDefault()

		// Initialize
		let wId = $(this).attr('w-id')

		$('#approval-id').val(wId)
		$('#approve-modal').modal('show')
	})

	$(document).on('submit', '#approve-modal form', function (e) {
		e.preventDefault()

		// Validation
	    let path = $('#evidence-of-transfer')[0].files
		
		if (path.length == 0) {
			toastr.error(`Bukti Transfer harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$(`#btn-loading`).attr('disabled', true)
		
		$.ajax({
		    url: `${baseUrl}/admin-panel/withdraw/update`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: new FormData(this),
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#btn-loading`).attr('disabled', false)

			        return 0
		    	}

				$('#approve-modal').modal('hide')
			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#btn-loading`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>
@endpush