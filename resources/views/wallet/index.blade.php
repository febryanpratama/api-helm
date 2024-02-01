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
{{-- Hidden Input --}}
<input type="hidden" id="wallet-store-url" value="{{ route('e.wallet.store') }}">
<input type="hidden" id="current-balance" value="{{ $balance }}">

<div class="container">
	<div class="row">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 mb-4">
			<div class="card card-custom h-100">
				<div class="card-body">
					<div class="text-center">
						<h6>@lang('label.total_balance')</h6>
						<h5><b>{{ rupiah($balance) }}</b></h5>
					</div>

					<div class="clearfix mt-4">
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
					</div>

					<div class="clearfix mt-4">
						<div class="alert alert-info" style="font-size: 14px;">@lang('label.alert_time_transfer')</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 mb-4">
			<div class="card card-custom">
				<div class="card-header bg-white">
					@lang('label.withdraw_balance')
				</div>
				<div class="card-body" id="send-balance-area">
					<form method="POST">
						<div class="row">
							<div class="col-sm-12 col-lg-6 col-xl-6 col-12">
								<div class="form-group">
									<label for="">@lang('label.no_account')</label> <span class="text-danger">*</span>
									<input type="text" class="form-control" id="account-number" name="account_number" placeholder="@lang('label.enter_no_account')">
								</div>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
								<div class="form-group">
									<label for="">@lang('label.account_name')</label> <span class="text-danger">*</span>
									<input type="text" class="form-control" id="account-holder-name" name="account_holder_name" placeholder="@lang('label.enter_account_name')">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
								<div class="form-group">
									<label for="">@lang('label.bank_or_wallet')</label> <span class="text-danger">*</span>
									<select name="bank_name" id="bank-name" class="form-control">
										<option value="Bank BCA">Bank BCA</option>
										<option value="Bank BRI">Bank BRI</option>
										<option value="Bank MANDIRI">Bank MANDIRI</option>
										<option value="OVO">OVO</option>
										<option value="Go Pay">Go Pay</option>
										<option value="Shopee Pay">Shopee Pay</option>
										<option value="Dana">Dana</option>
									</select>
								</div>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
								<div class="form-group">
									<label for="">@lang('label.transfer_amount')</label> <span class="text-danger">*</span>
									<input type="text" class="form-control" name="balance" id="input-balance" placeholder="Masukkan Nominal Transfer">
									<span class="text-info" style="font-size: 12px;">*@lang('label.minimum_transfer') Rp.250.000</span>
								</div>
							</div>
						</div>

						<button class="btn btn-sm btn-company" id="send-btn-loading" disabled="">@lang('label.withdraw')</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			<div class="card card-custom">
				<div class="card-header bg-white">
					@lang('label.previous_transaction')
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-responsive-md">
							@forelse($history as $val)
							<tr>
								<td width="10">
									@if ($val->balance_type)
										<div class="circle-icon">
											<div class="balance-in-icon border-radius-100 font-24 text-danger"><i class="fa fa-arrow-up" aria-hidden="true"></i></div>
										</div>
									@else
										<div class="circle-icon">
											<div class="balance-out-icon border-radius-100 font-24 text-success"><i class="fa fa-arrow-down" aria-hidden="true"></i></div>
										</div>
									@endif
								</td>
								<td>
									{{ $val->details }}
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
@stop

@push('script')
{{-- Change Format --}}
<script>
	$(document).on('keyup', '#input-balance', function () {
	    // Initialize
	    let balanceInput	= ($(this).val()).replace('.','')
	    let currentBalance 	= $('#current-balance').val()

	    $(this).val(formatRupiah($(this).val(), 'Rp.'))

	    // if (balanceInput <= currentBalance) {
	    	$('#send-btn-loading').attr('disabled', false)
	    // } else {
	    // 	$('#send-btn-loading').attr('disabled', true)
	    // }
	})

	function formatRupiah(angka, prefix) {
	    var number_string = angka.replace(/[^,\d]/g, '').toString(),
	        split = number_string.split(','),
	        sisa = split[0].length % 3,
	        rupiah = split[0].substr(0, sisa),
	        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

	    if (ribuan) {
	        separator = sisa ? '.' : '';
	        rupiah += separator + ribuan.join('.');
	    }

	    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	    return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
	}
</script>

{{-- Send Balance --}}
<script>
	$(document).on('submit', '#send-balance-area form', function (e) {
		e.preventDefault()

		// Initialize
		let url 				= $('#wallet-store-url').val()
		let accountNumber 		= $('#account-number').val()
		let accountHolderName 	= $('#account-holder-name').val()
		let bankName 			= $('#bank-name').val()
		let inputBalance 		= $('#input-balance').val()
		let originalBalance 	= inputBalance.replace('.','')

		// Validate
		if (!accountNumber) {
			toastr.error(`Nomor Akun harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!accountHolderName) {
			toastr.error(`Nama Akun harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!bankName) {
			toastr.error(`Bank/Wallet harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!inputBalance) {
			toastr.error(`Nominal Transfer harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (originalBalance <= 250000) {
			toastr.error(`Minimal Transfer Rp.250.000`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$('#send-btn-loading').attr('disabled', true)

	    $.ajax({
	        url: `${url}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        data: new FormData(this),
	        contentType: false,
	        cache: false,
	        processData: false,
	        dataType: 'json',
	        success: data => {
	        	// Disabled Button False
	        	$('#send-btn-loading').attr('disabled', false)

				if (!data.status) {
					toastr.error(`${data.message}`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

					return 0
				}

				toastr.success(`${data.message}`, 'Sukses!', {closeButton:!0, tapToDismiss:!1, rtl:o})

				setTimeout(function () {
					location.reload()
				}, 2000)
	        },
	        error: e => {
	            console.log(e)

	            // Disabled Button False
	            $('#send-btn-loading').attr('disabled', false)

	            toastr.error(`Penarikan saldo gagal, silahkan coba kembali.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
	        }
	    })
	})
</script>
@endpush