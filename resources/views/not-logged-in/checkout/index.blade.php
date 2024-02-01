@extends('layouts.app')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/css/swiper.min.css" integrity="sha256-DBYdrj7BxKM3slMeqBVWX2otx7x4eqoHRJCsSDJ0Nxw=" crossorigin="anonymous"/>

<style>
	.text-color {
		color: #62ddbd !important;
	}

	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}

	@media screen and (max-width: 576px) {
		.pl-sm-2 {
			padding-left: 2.5rem !important;
		}

		.mb-sm-2 {
			margin-bottom: 1.5rem !important;
		}
	}

	.swiper {
		width: 100%;
		height: 100%;
	}

	.swiper-slide {
		height: auto;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
		padding: 30px;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="check-store-url" value="{{ route('not.logged.in.checkout.store') }}?course-id={{ $course->id }}&buy-now=true">
<input type="hidden" id="unique-code" value="{{ $uniqueCode }}">

<div class="container mb-4">
	<div class="row mt-4">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			<div class="clearfix bg-white card-custom">
				<div class="border-left-element">
					<div class="p-2">
						<div class="float-right pr-2">
							<a href="{{ url()->previous() }}" class="text-dark"><i class="fas fa-arrow-left"></i> Kembali</a>
						</div>

						<b>CHECKOUT</b>
					</div>
				</div>
			</div>
		</div>

		@if ($uniqueCode != 0)
		<div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-12">
			<div class="card card-custom mt-4">
				<h5 class="pl-3 pt-2 pb-2"><b>Detail Pesanan</b></h5>

				<div class="clearfix">
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 text-center">
							<img src="{{ $course->thumbnail }}" alt="preview-img-course" class="" style="width: 80%; object-fit: cover; height: 80%;">
						</div>

						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 pl-sm-2">
							<b>{{ $course->name }}</b>

							<p>
								<span>Pengajar : {{ $course->user->name }}</span> <br>
								<span>Lembaga Kursus : {{ $course->user->company->Name }}</span>
							</p>
						</div>

						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 pl-sm-2 mb-sm-2">
							@if ($course->course_type == 2)
								<span class="badge badge-success">@lang('label.free')</span>
							@else
								Rp. {{ $course->price }}</b>
							@endif

							<div>
								<span>Masa Aktif Kursus : <b>{{ $course->periode }} {{ coursePeriode($course->periode_type) }}</b> <i class="fas fa-question-circle config-tooltip cursor-area text-color" data-toggle="tooltip" data-placement="top" title="Masa Aktif Kursus akan berjalan setelah pembayaran."></i></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card card-custom mt-4">
				<div class="card-body">
					<div class="clearfix">
						<h6>
							<b>@lang('label.account_profile')</b>

							<span>
								<i class="fas fa-question-circle config-tooltip cursor-area text-color" data-toggle="tooltip" data-placement="top" title="Akun akan otomatis dibuat setelah transaksi selesai"></i>
							</span>
						</h6>

						<hr>

						<div class="form-group">
							<label for="">@lang('label.name')</label> <span class="text-danger">*</span>

							<input id="profil-name" type="text" class="form-control" name="name" value="" placeholder="@lang('label.enter_name')">
						</div>

						<div class="form-group">
							<label for="">@lang('label.email')</label> <span class="text-danger">*</span>

							<input type="email" class="form-control" name="email" value="" id="profil-email" placeholder="@lang('label.enter_email')">
						</div>
					</div>

					<div class="form-group">
						<label for="">Metode Pembayaran</label>

						<select name="payment-type" id="payment-type" class="form-control">
							<option value="1">Bank Transfer</option>
							<option value="2">E-Money</option>
						</select>
					</div>

					<div class="clearfix" id="bank-transfer">
						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="BCA|6801221367"><b> Bank BCA</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="BRI|039301001433302"><b> Bank BRI</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="MANDIRI|1640015155155"><b> Bank MANDIRI</b>
						</div>
					</div>

					<div class="clearfix" id="e-money" style="display: none;">
						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="OVO|081285365902"><b> OVO</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="Go Pay|081285365902 "><b> Go Pay</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="Shopee Pay|081285365902"><b> Shopee Pay</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="DANA|081285365902"><b> DANA</b>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-12 mt-4 mb-sm-2">
			<div class="card card-custom">
				<div class="card-body">
					<h4><b>Ringkasan</b></h4>

					<div class="clearfix">
						<div class="float-right">
							<b>{{ rupiah($course->price_num) }}</b>
						</div>

						<b>Harga :</b>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<b>Rp.{{ $uniqueCode }}</b>
						</div>

						<b>Kode Unik :</b>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<b>Rp.0</b>
						</div>

						<b>Diskon :</b>
					</div>

					<hr>
					<div class="clearfix">
						<div class="float-right">
							<b>{{ rupiah($course->price_num + $uniqueCode) }}</b>
						</div>

						<b>Total :</b>
					</div>

					<div class="clearfix mt-4" style="font-size: 12px;">
						Dengan menyelesaikan pembelian, Anda menyetujui <b><a href="javascript:void(0)" class="text-color">Ketentuan Layanan</a></b> ini.
					</div>

					<div class="clearfix mt-2">
						<button class="btn btn-primary w-100 cursor-area" id="complate-payment">Selesaikan Pembayaran</button>
					</div>
				</div>
			</div>
		</div>
		@else
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			<div class="card card-custom mt-4">
				<h5 class="pl-3 pt-2 pb-2"><b>Detail Pesanan</b></h5>

				<div class="clearfix">
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 text-center">
							<img src="{{ $course->thumbnail }}" alt="preview-img-course" class="" style="width: 80%; object-fit: cover; height: 80%;">
						</div>

						<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 pl-sm-2">
							<b>{{ $course->name }}</b>

							<p>
								<span>Pengajar : {{ $course->user->name }}</span> <br>
								<span>Lembaga Kursus : {{ $course->user->company->Name }}</span>
							</p>
						</div>

						<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 pl-sm-2 mb-sm-2">
							@if ($course->course_type == 2)
								<span class="badge badge-success">@lang('label.free')</span>
							@else
								Rp. {{ $course->price }}</b>
							@endif

							<div>
								<span>Masa Aktif Kursus : <b>{{ $course->periode }} {{ coursePeriode($course->periode_type) }}</b> <i class="fas fa-question-circle config-tooltip cursor-area text-color" data-toggle="tooltip" data-placement="top" title="Masa Aktif Kursus akan berjalan setelah pembayaran."></i></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card card-custom mt-4">
				<div class="card-body">
					<div class="clearfix">
						<h6>
							<b>@lang('label.account_profile')</b>

							<span>
								<i class="fas fa-question-circle config-tooltip cursor-area text-color" data-toggle="tooltip" data-placement="top" title="Akun akan otomatis dibuat setelah transaksi selesai"></i>
							</span>
						</h6>

						<hr>

						<div class="form-group">
							<label for="">@lang('label.name')</label> <span class="text-danger">*</span>

							<input id="profil-name" type="text" class="form-control" name="name" value="" placeholder="@lang('label.enter_name')">
						</div>

						<div class="form-group">
							<label for="">@lang('label.email')</label> <span class="text-danger">*</span>

							<input type="email" class="form-control" name="email" value="" id="profil-email" placeholder="@lang('label.enter_email')">
						</div>
					</div>


					<div class="clearfix" id="bank-transfer" style="display: none;">
						<div class="form-group">
							<label for="">Metode Pembayaran</label>

							<select name="payment-type" id="payment-type" class="form-control">
								<option value="1" selected="">Bank Transfer</option>
								<option value="2">E-Money</option>
							</select>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="BCA|6801221367"><b> Bank BCA</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="BRI|039301001433302"><b> Bank BRI</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="MANDIRI|1640015155155" checked=""><b> Bank MANDIRI</b>
						</div>
					</div>

					<div class="clearfix" id="e-money" style="display: none;">
						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="OVO|081285365902"><b> OVO</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="Go Pay|081285365902 "><b> Go Pay</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="Shopee Pay|081285365902"><b> Shopee Pay</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="DANA|081285365902"><b> DANA</b>
						</div>
					</div>
				</div>
			</div>

			<div class="card card-custom mt-4">
				<div class="card-body">
					<h4><b>Ringkasan</b></h4>

					<div class="clearfix">
						<div class="float-right">
							<b>{{ rupiah($course->price_num) }}</b>
						</div>

						<b>Harga :</b>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<b>Rp.{{ $uniqueCode }}</b>
						</div>

						<b>Kode Unik :</b>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<b>Rp.0</b>
						</div>

						<b>Diskon :</b>
					</div>

					<hr>
					<div class="clearfix">
						<div class="float-right">
							<b>{{ rupiah($course->price_num + $uniqueCode) }}</b>
						</div>

						<b>Total :</b>
					</div>

					<div class="clearfix mt-4" style="font-size: 12px;">
						Dengan menyelesaikan pembelian, Anda menyetujui <b><a href="javascript:void(0)" class="text-color">Ketentuan Layanan</a></b> ini.
					</div>

					<div class="clearfix mt-2">
						<button class="btn btn-primary w-100 cursor-area" id="complate-payment">Selesaikan Pesanan</button>
					</div>
				</div>
			</div>
		</div>
		@endif
	</div>
</div>
@stop

@push('script')
{{-- Config --}}
<script>
	$(document).ready(function () {
		// Call Function
		configTooltip()
	})

	function configTooltip() {
		$('.config-tooltip').tooltip()
	}

	$(document).on('change', '#payment-type', function () {
		if (this.value == 2) {
			$('#e-money').css('display', '')
			$('#bank-transfer').css('display', 'none')
		} else {
			$('#bank-transfer').css('display', '')
			$('#e-money').css('display', 'none')
		}
	})
</script>

{{-- Checkout --}}
<script>
	$(document).on('click', '#complate-payment', function () {
		// Initialize
		let url 			= $('#check-store-url').val()
		let courseId 		= $('#course-id').val()
		let paymentType 	= $('#payment-type option:selected').text()
		let bank 			= $('input[name="bank"]:checked').val()
		let uniqueCode  	= $('#unique-code').val()
		let profileName 	= $('#profil-name').val()
		let profileEmail 	= $('#profil-email').val()

		// Validate
		if (!profileName) {
			toastr.error(`Nama harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!profileEmail) {
			toastr.error(`Email harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!bank) {
		    toastr.error(`Pilih Bank Tujuan!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		    return 0
		}

		// Disabled Button True
		$('#complate-payment').attr('disabled', true)

		$.ajax({
		    url: `${url}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	paymentType: paymentType,
		    	bank: bank,
		    	uniqueCode: uniqueCode,
		    	name: profileName,
		    	email: profileEmail
		    },
		    success: data => {
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        window.location = `${baseUrl}/verify/otp?email=${profileEmail}&redirect=checkout/detail-pembayaran/${data.data.id}`
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#complate-payment').attr('disabled', false)

		        toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>
@endpush