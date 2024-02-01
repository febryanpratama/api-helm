@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/css/swiper.min.css" integrity="sha256-DBYdrj7BxKM3slMeqBVWX2otx7x4eqoHRJCsSDJ0Nxw=" crossorigin="anonymous"/>

<style>
	.border-left-element {
	    border-left: 2px solid #62ddbd;
	    padding-left: 10px;
	}

	@media screen and (max-width: 576px) {
		.mobile-area {
			display: '';
		}

		.desktop-area {
			display: none;
		}
	}

	@media screen and (min-width: 1200px) and (max-width: 2000px) {
		.mobile-area {
			display: none;
		}
	}
</style>

<style>
	.card {
		overflow: visible;
	}

	.card-profile {
		border-radius: 5px;
		box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
		border: 0px;
	}

	.text-color {
		color: #62ddbd !important;
	}

	.edit-profil-company-element {
		position: absolute;
		right: 1em;
	}

	.edit-profil-company {
		border: 1px solid #62ddbd;
		color: #62ddbd !important;
	}

	.edit-profil-company:hover {
		background-color: #62ddbd !important;
		color: white !important;
	}

	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}

	.text-custom-size {
		font-size: 12px;
	}

	.action-course-element {
		position: absolute;
	    right: 0.5em;
	    top: 5px;
	}

	.action-course {
		background-color: white !important;
		width: 2.5em;
	}

	.dropdown-menu:before {
		content: '';
		position: absolute;
	    top: 0 !important;
	    right: 0 !important;
	    border: 10px solid white !important;
	    border-color: transparent !important;
	}

	.course-is-publish {
		position: absolute;
	}

	.custom-height-detail-order {
		height: 16vw;
	}
</style>
@endpush

@section('content')
{{-- Hidden --}}
<input type="hidden" id="member-course-list-url" value="{{ route('member.course.list') }}">

<div class="container">
	<div class="clearfix bg-white card-custom">
		<div class="border-left-element">
			<div class="p-2">
				<div class="float-right pr-2">
					<b>@lang('label.order_details')</b>
				</div>

				<a href="{{ route('management.transaction.index') }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.transaction_list')</a>
			</div>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-sm-12 col-12">
			<div class="card card-custom">
				<div class="card-body">
					@lang('label.thanks_for_order_information')

					<p>
						@lang('label.thanks_for_order_information_1') <b>{{ date('d/m/Y H:i', strtotime($checkout->expired_transaction)) }} (WIB)</b>.
					</p>

					<div class="clearfix text-center mt-5">
						<div class="border bg-dark text-white m-auto" style="width: 15em;">{{ $checkout->no_rek }} ({{ $checkout->bank_name }})</div>

						<div class="row mt-5 justify-content-center desktop-area mb-5">
							<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12">
								<div class="float-right">
									<div style="font-size: 10px">@lang('label.amount')</div> {{ rupiah($checkout->total_payment) }}
								</div>

								 <i class="fas fa-wallet text-color fa-lg ml-4"></i>
							</div>
						</div>
					</div>

					<div class="row mobile-area mt-5 mb-4">
						<div class="col-sm-6 col-6">
							<div class="float-right">
								<div style="font-size: 10px">@lang('label.amount')</div> {{ rupiah($checkout->total_payment) }}
							</div>

							 <i class="fas fa-wallet text-color fa-lg ml-4"></i>
						</div>

						<div class="col-sm-6 col-6">
							<div class="float-right">
								<div style="font-size: 10px">@lang('label.pay_befor') (WIB)</div> {{ date('d/m/Y H:i', strtotime($checkout->expired_transaction)) }}
							</div>

							<i class="fas fa-calendar text-color fa-lg"></i>
						</div>
					</div>

					<div class="clearfix">
						<div class="clearfix mb-2">
							@lang('label.checkout_verification_information')
						</div>

						<b>@lang('label.transfer_instructions')</b>

						@if ($checkout->bank_name == 'BCA')
							<div class="mt-2">
								1. Pilih m-Transfer > Daftar Transfer > Antar Rekening <br>
								2. Masukkan nomor Rekening 6801221367 <br>
								3. Pilih INCORE SYSTEMS SOLUTIONS <br>
								4. Masukkan PIN m-BCA Anda dan pilih OK <br>
								5. Pilih Transfer > Antar Rekening <br>
								6. Pilih Rekening > INCORE SYSTEMS SOLUTIONS, lalu masukkan Jumlah Uang {{ rupiah($checkout->total_payment) }} <br>
								7. Masukkan PIN m-BCA Anda dan pilih OK
							</div>
						@elseif ($checkout->bank_name == 'MANDIRI')
							<div class="mt-2">
								1. Pilih Transfer <br>
								2. Pilih Ke Rekening Mandiri <br>
								3. Tentukan Rekening Sumber <br>
								4. Isi Rekening Tujuan 1640015155155 dan Jumlah Transfer {{ rupiah($checkout->total_payment) }}<br>
								5. Konfirmasi dan Masukkan MPIN
							</div>
						@elseif ($checkout->bank_name == 'BRI')
							<div class="mt-2">
								1. Pilih menu Transfer > klik Sesama BRI <br>
								2. Silahkan masukan Nomor Rekening 039301001433302 dan {{ rupiah($checkout->total_payment) }} klik OK <br>
								3. Masukan PIN > klik SEND <br>
								4. Jika sudah, maka akan muncul informasi Mobile Banking BRI akan melakukan transaksi menggunakan SMS > klik OK <br>
								5. Selanjutnya akan berpindah ke halaman SMS, silahkan Kirim format SMS yang tersedia <br>
								6. Akan ada SMS masuk, silahkan balas SMS tersebut sesuai perintah isi SMS > ketikan YA(spasi)Nomor Kode > kemudian Kirim SMS
							</div>
						@elseif ($checkout->bank_name == 'Go Pay')
							<div class="mt-2">
								1. Buka Aplikasi Gojek <br>
								2. Pilih Bayar <br>
								3. Masukkan Data Penerima dengan nomor ponsel 081285365902 <br>
								4. Isi Nominal Transfer {{ rupiah($checkout->total_payment) }} <br>
								5. Konfirmasi dan Masukkan PIN GoPay
							</div>
						@elseif ($checkout->bank_name == 'OVO')
							<div class="mt-2">
								1. Buka aplikasi OVO <br>
								2. Pilih menu Transfer > Pilih Ke Sesama OVO <br>
								3. Masukkan nomor handphone 081285365902 <br>
								4. Masukkan jumlah uang {{ rupiah($checkout->total_payment) }} <br>
								5. Pilih tombol Lanjutkan <br>
								6. Cek kembali apakah nomor penerima dan jumlah yang sudah dikirim benar > Jika benar, pilih tombol Transfer <br>
								7. Masukkan kode keamanan atau PIN OVO
							</div>
						@elseif ($checkout->bank_name == 'Shopee Pay')
							<div class="mt-2">
								1. Buka aplikasi Shopee <br>
								2. Klik menu Saya > Pilih Shopeepay <br>
								3. Pilih menu Transfer <br>
								4. Masukan nomor handphone 081285365902 <br>
								5. Isi nominal transfer {{ rupiah($checkout->total_payment) }} > Pilih Konfirmasi <br>
								6. Masukan PIN Shopeepay atau menggunakan sidik jari atau Face ID untuk iOS
							</div>
						@elseif ($checkout->bank_name == 'DANA')
							<div class="mt-2">
								1. Buka aplikasi Dana <br>
								2. Pilih Kirim <br>
								3. Tentukan Rekening Sumber <br>
								4. Pilih cara Kirim Uang ke nomor ponsel <br>
								5. Isi nomor 081285365902 dan Nominal {{ rupiah($checkout->total_payment) }} <br>
								6. Pilih Saldo DANA atau Kartu Debit untuk kirim uang <br>
								7. Pilih Konfirmasi
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/js/swiper.min.js" integrity="sha256-4sETKhh3aSyi6NRiA+qunPaTawqSMDQca/xLWu27Hg4=" crossorigin="anonymous"></script>

{{-- Config --}}
<script>
	$(document).ready(function () {
		let swiper = new Swiper(".swiper-detail-order", {
	        direction: "vertical",
	        slidesPerView: "auto",
	        freeMode: true,
	        scrollbar: {
	          el: ".swiper-scrollbar",
	        },
	        mousewheel: true,
		});

		// Call Function
		configTooltip()
	})

	function configTooltip() {
		$('.config-tooltip').tooltip()
	}
</script>
@endpush