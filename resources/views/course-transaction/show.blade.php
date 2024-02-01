@extends('layouts.master')

@push('style')
<style>
	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}

	@media (min-width: 768px) {
		.mt-md-4-c, .my-md-4-c {
	    	margin-top: 2rem !important;
		}
	}
</style>
@endpush

@section('content')
@php
	// Initialize
	$totalQty = 0;
@endphp

{{-- Hidden Element --}}
<input type="hidden" id="total-payment" value="{{ $checkout->total_payment }}">
<input type="hidden" id="checkout-id" value="{{ $checkout->id }}">

<div class="container mb-4">
	<div class="row justify-content-center">
		<div class="col-sm-10 col-md-10 col-lg-10 col-xl-10 col-10">
			<div class="clearfix bg-white card-custom">
				<div class="border-left-element">
					<div class="p-2" id="pay-area" style="display: {{ ($checkout->status_payment != 2) ? 'none' : '' }};">
						<div class="row">
							<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
								<div class="form-group">
									<label for="">Total Yang Harus Dibayar</label>
									<b>{{ rupiah($checkout->total_payment) }}</b>
								</div>
							</div>

							<div class="col-sm-12 col-md-9 col-lg-9 col-xl-9 col-12">
								<div class="float-right">
									<button class="btn btn-sm btn-info text-white print-invoice"><i class="fas fa-printer"></i> Print Invoice</button>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
								<div class="form-group">
									<label for="">Bayar</label> <span class="text-danger">*</span>
									<input type="text" class="form-control" placeholder="Hanya masukkan angka" id="total-pay">
								</div>
							</div>

							<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
								<div class="form-group">
									<label for="">Kembalian</label>
									<input type="text" class="form-control" placeholder="0" id="change" readonly="">
								</div>
							</div>

							<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
								<div class="form-group">
									<label for="">Status Pembayaran</label> <span class="text-danger">*</span>
									<select name="status_payment" id="status-payment" class="form-control">
										<option value="">-- Pilih --</option>
										<option value="1">Lunas</option>
										<option value="2">Belum Lunas</option>
									</select>
								</div>
							</div>

							<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
								<div class="form-group mt-md-4-c">
									<button class="btn btn-sm btn-company" id="btn-save-pay">Simpan</button>
								</div>
							</div>

						</div>
					</div>

					<div class="p-2" id="print-area" style="display: {{ ($checkout->status_payment == 1) ? '' : 'none' }};">
						<div class="row">
							<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
								<b>Bukti Pembayaran</b>
							</div>

							<div class="col-sm-12 col-md-9 col-lg-9 col-xl-9 col-12">
								<div class="float-right">
									<button class="btn btn-sm btn-info text-white print-invoice"><i class="fas fa-printer"></i> Print Invoice</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-4 justify-content-center">
		<div class="col-sm-10 col-md-10 col-lg-10 col-xl-10 col-10">
			<div class="card card-custom">
				{{-- <div class="card-header bg-white">
					<div class="row">
						<div class="col-6">
							<img src="{{ auth()->user()->company->Logo }}" alt="img-preview" style="height:50px;">
						</div>

						<div class="col-6">
							<div class="float-right">
								<strong>Order ID :</strong>
								#{{ $checkout->id }}
								<br>
								<strong>Tanggal :</strong>
								{{ $checkout->created_at->format('d F Y H:i:s') }}
							</div>
						</div>
					</div>
				</div> --}}

				<div class="card-body">
					<div class="clearfix text-center">
						<div class="clearfix mb-1">
							<b>{{ auth()->user()->company->Name }}</b>
						</div>

						{!! auth()->user()->company->Address !!}
					</div>

					<div class="mt-3" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333;"></div>
					
					<div class="clearfix mt-3">
						<table class="">
							<tbody>
								<tr>
									<td>Tgl & Jam</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ $checkout->created_at->format('d/M/Y') }} {{ $checkout->created_at->format('H:i') }}</td>
								</tr>
								<tr>
									<td>Kasir</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ $checkout->user->name }}</td>
								</tr>
								<tr>
									<td>Customer</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ ($checkout->customer_name) ? $checkout->customer_name : 'N/A'  }}</td>
								</tr>
								<tr>
									<td>Nomor Invoice</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ $checkout->inv_code }}</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="mt-3" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333;"></div>

					<div class="clearfix text-center mt-3"><b>TRANSAKSI</b></div>
					<div class="clearfix text-center mt-3">
						@foreach($checkout->checkoutDetail as $val)
						@php
							// Initialize
							$totalQty += $val->qty;
						@endphp
						<div class="row mb-2">
							<div class="col-6 text-left">
								{{ $val->course_name }} <br> {{ $val->qty }} x {{ $val->price_course }}
							</div>
							<div class="col-6 text-right">
								{{ rupiah($val->original_price_course * $val->qty) }}
							</div>
						</div>
						@endforeach
					</div>

					<div class="mt-3" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333;"></div>

					<div class="clearfix text-center mt-3">
						<div class="row mb-1">
							<div class="col-6 text-left">
								Total Item
							</div>
							<div class="col-6 text-right">
								{{ $totalQty }}
							</div>
						</div>

						<div class="row mb-1">
							<div class="col-6 text-left">
								Sub Total
							</div>
							<div class="col-6 text-right">
								{{ rupiah($checkout->total_payment) }}
							</div>
						</div>

						<div class="row mb-1">
							<div class="col-6 text-left">
								Total
							</div>
							<div class="col-6 text-right">
								{{ rupiah($checkout->total_payment) }}
							</div>
						</div>

						<div class="row mb-1">
							<div class="col-6 text-left">
								Bayar
							</div>
							<div class="col-6 text-right">
								{{ rupiah($checkout->total_pay) }}
							</div>
						</div>

						<div class="row mb-1">
							<div class="col-6 text-left">
								Kembalian
							</div>
							<div class="col-6 text-right">
								{{ rupiah($checkout->change) }}
							</div>
						</div>
					</div>

					<div class="mt-3" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333;"></div>

					<div class="clearfix text-center mt-3">
						<div class="row mb-1">
							<div class="col-6 text-left">
								Tipe Pembayaran
							</div>
							<div class="col-6 text-right">
								{{ paymentType($checkout->payment_type) }}
							</div>
						</div>

						@if ($checkout->payment_type == 'Debit' || $checkout->payment_type == 'Credit')
						<div class="row mb-1">
							<div class="col-6 text-left">
								Nomor Kartu
							</div>
							<div class="col-6 text-right">
								{{ $checkout->card_nomor }}
							</div>
						</div>
						@endif

						<div class="row mb-1">
							<div class="col-6 text-left">
								Status Pembayaran
							</div>
							<div class="col-6 text-right">
								{{ ($checkout->status_payment == 2) ? 'Belum Lunas' : 'Lunas' }}
							</div>
						</div>
					</div>

					<div class="clearfix text-center mt-3">
						<div class="row">
							<div class="col-12">
								TERIMA KASIH
								<br>
								ATAS KUNJUNGANNYA
								<br>
								SMS/WA {{ auth()->user()->company->Phone }}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	{{-- Pure CSS --}}
	<div class="row mt-4 justify-content-center" style="display: none;">
		<div class="col-sm-10 col-md-10 col-lg-10 col-xl-10 col-10">
			<div class="card card-custom">
				<div class="card-body" id="print-area-invoice">
					<div class="clearfix" style="text-align: center;">
						<div class="clearfix" style="margin-bottom: 0.25rem !important;">
							<b>{{ auth()->user()->company->Name }}</b>
						</div>

						{!! auth()->user()->company->Address !!}
					</div>

					<div class="" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333; margin-top: 1rem !important;"></div>
					
					<div class="clearfix" style="margin-top: 1rem !important;">
						<table class="">
							<tbody>
								<tr>
									<td>Tgl & Jam</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ $checkout->created_at->format('d/M/Y') }} {{ $checkout->created_at->format('H:i') }}</td>
								</tr>
								<tr>
									<td>Kasir</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ $checkout->user->name }}</td>
								</tr>
								<tr>
									<td>Customer</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ ($checkout->customer_name) ? $checkout->customer_name : 'N/A'  }}</td>
								</tr>
								<tr>
									<td>Nomor Invoice</td>
									<td>&nbsp; : &nbsp;</td>
									<td>{{ $checkout->inv_code }}</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333; margin-top: 1rem !important;"></div>

					<div class="clearfix text-center" style="margin-top: 1rem !important; text-align: center;"><b>TRANSAKSI</b></div>
					<div class="clearfix text-center" style="margin-top: 1rem !important;">
						@foreach($checkout->checkoutDetail as $val)
						@php
							// Initialize
							$totalQty += $val->qty;
						@endphp
						<div class="clearfix" style="margin-bottom: 0.5rem !important;">
							<table>
								<tr>
									<td style="width: 100%; text-align: left;">{{ $val->course_name }} <br> {{ $val->qty }} x {{ $val->price_course }}</td>
									<td style="width: 100%;">{{ rupiah($val->original_price_course * $val->qty) }}</td>
								</tr>
							</table>
						</div>
						@endforeach
					</div>

					<div class="" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333; margin-top: 1rem !important;"></div>

					<div class="clearfix text-center" style="margin-top: 1rem !important;">
						<table>
							<tr>
								<td style="width: 100%; text-align: left;">Total Item</td>
								<td style="width: 100%;">{{ $totalQty }}</td>
							</tr>

							<tr>
								<td style="width: 100%; text-align: left;">Sub Total</td>
								<td style="width: 100%;">{{ rupiah($checkout->total_payment) }}</td>
							</tr>

							<tr>
								<td style="width: 100%; text-align: left;">Total</td>
								<td style="width: 100%;">{{ rupiah($checkout->total_payment) }}</td>
							</tr>

							<tr>
								<td style="width: 100%; text-align: left;">Bayar</td>
								<td style="width: 100%;">{{ rupiah($checkout->total_pay) }}</td>
							</tr>

							<tr>
								<td style="width: 100%; text-align: left;">Kembalian</td>
								<td style="width: 100%;">{{ rupiah($checkout->change) }}</td>
							</tr>
						</table>
					</div>

					<div class="" style="border-bottom: 1px dashed #1f1209; box-shadow: 0 20px 20px -20px #333; margin-top: 1rem !important;"></div>

					<div class="clearfix text-center" style="margin-top: 1rem !important;">
						<div class="clearfix" style="margin-bottom: 0.5rem !important;">
							<table>
								<tr>
									<td style="width: 100%; text-align: left;">Tipe Pembayaran</td>
									<td style="width: 100%;">{{ $checkout->payment_type }}</td>
								</tr>
							</table>
						</div>

						@if ($checkout->payment_type == 'Debit' || $checkout->payment_type == 'Credit')
						<div class="clearfix" style="margin-bottom: 0.5rem !important;">
							<table>
								<tr>
									<td style="width: 100%; text-align: left;">Nomor Kartu</td>
									<td style="width: 100%;">{{ $checkout->card_nomor }}</td>
								</tr>
							</table>
						</div>
						@endif

						<div class="clearfix" style="margin-bottom: 0.5rem !important;">
							<table>
								<tr>
									<td style="width: 100%; text-align: left;">Status Pembayaran</td>
									<td style="width: 100%;">{{ ($checkout->status_payment == 2) ? 'Belum Lunas' : 'Lunas' }}</td>
								</tr>
							</table>
						</div>
					</div>

					<div class="clearfix" style="margin-top: 1rem !important; text-align: center;">
						TERIMA KASIH <br> ATAS KUNJUNGANNYA <br> SMS/WA {{ auth()->user()->company->Phone }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@push('script')
<script>
	$(document).on('keyup', '#total-pay', function () {
		// Initialize
	    let currentValue  = $("#total-pay").val()
	    let currentValNum = currentValue.replace('.','')
	    let originalVal   = this.value
	    let totalPayment  = $('#total-payment').val()

	    $(this).val(formatRupiah(currentValue, 'Rp.'))

	    let change = (currentValNum - totalPayment)

	    if (change == totalPayment || parseInt(currentValNum) < parseInt(totalPayment) || currentValNum == '') {
		    $('#change').val(0)
	    } else {
		    $('#change').val(formatRupiah(change.toString(), 'Rp.'))
	    }
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

<script>
	$(document).on('click', '#btn-save-pay', function (e) {
		e.preventDefault()

		// Initialize
		let totalPay = $('#total-pay').val()
		let change 	 = $('#change').val()
		let status 	 = $('#status-payment').val()

		// Validate
		if (!totalPay) {
	    	toastr.error({closeButton:!0, tapToDismiss:!1, rtl:o})

	    	$('#total-pay').focus()

			return 0
		}

		if (!status) {
	    	toastr.error(`Status Pembayaran harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$(this).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/offline-transaction/update/${$('#checkout-id').val()}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	totalPay: totalPay,
		    	change: change,
		    	statusPayment: status
		    },
		    success: data => {
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(this).attr('disabled', false)

		        toastr.error(`${e.responseJSON.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>

{{-- Print --}}
<script>
	$(document).on('click', '.print-invoice', function (e) {
		e.preventDefault()

		// Initialize
		var divToPrint 	= document.getElementById('print-area-invoice');
		var newWin  	= window.open('','Print-Window');

		newWin.document.open();
		newWin.document.write('<html><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
		newWin.document.close();

		setTimeout(function(){newWin.close();},10);
	})
</script>
@endpush