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

				<a href="{{ route('course.transaction.index') }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.transaction_list')</a>
			</div>
		</div>
	</div>

	<div class="row mt-3">
		<div class="col-sm-12 col-12">
			@if ($checkout->total_payment == 0)
				<div class="card card-custom">
					<div class="card-body">
						<p class="text-center">
							" <b>Selamat</b> paket kursusmu telah aktif!
							Sekarang kamu bisa mulai kursusmu!
							Yuk mulai belajar lebih baik lagi setiap harinya.
							Sampai jumpa! "
						</p>

		
						<div class="clearfix p-2" style="background-color: #f0eef5;">
							<div class="pt-2 text-center"><b>@lang('label.order_details')</b></div>
							
							<div class="clearfix swiper-container swiper-detail-order">
								<div class="swiper-wrapper">
									<div class="swiper-slide">
										@foreach($checkoutDetail as $val)
										<div class="row">
											<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 text-center">
												<img src="{{ $val->course->thumbnail }}" alt="preview-img-course" class="" style="width: 80%; height: 80%;">
											</div>

											<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
												<b>{{ $val->course_name }}</b>

												<p>
													<span>@lang('label.instructor') : {{ $val->course->user->name }}</span> <br>
													<span>@lang('label.course_institute') : {{ $val->course->user->company->Name }}</span>
												</p>
											</div>

											<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 mb-sm-2">
												<b>
													<span class="badge badge-success">@lang('label.free')</span>
												</b>

												<div>
													<span>@lang('label.course_active_period') : <b>{{ $val->course_periode }} {{ coursePeriode($val->course_periode_type) }}</b> <i class="fas fa-question-circle config-tooltip cursor-area text-color" data-toggle="tooltip" data-placement="top" title="@lang('label.course_active_period_description')"></i></span>
												</div>
											</div>
										</div>
										@endforeach
									</div>
								</div>

						      	<div class="swiper-scrollbar"></div>
							</div>
						</div>
					</div>
				</div>


				<div class="clearfix border-left-element mt-4 bg-white" style="border-radius: 5px; box-shadow: rgb(149 157 165 / 20%) 0px 8px 24px;">
					<div class="float-right mr-3 mt-1">
						<button class="btn btn-sm btn-primary btn-outline" id="dropdown-search-course" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fas fa-search"></i>
						</button>

						<div class="dropdown-menu dropdown-menu-right mt-2" aria-labelledby="dropdown-search-course" style="right: 0px !important; width: 14em !important;">
						    <div class="form-group p-2">
						    	<label for="">@lang('label.search_cource_packages')</label>
						    	<input type="text" class="form-control" placeholder="Ex: Belajar Matematika" id="search-course-input">
						    </div>
						</div>
					</div>

					<h5 class="pt-2">
						@lang('label.free_course_package')
					</h5>

					{{-- <span class="text-title" id="sub-text-course-element">Total Paket Kursus Aktif : {{ $courseActive }} Paket Kursus</span> --}}
				</div>

				<div class="clearfix mt-4">
					<div class="text-center mt-5" id="result-loading-data">
						@lang('label.retrive_data')...

						<div class="spinner-grow spinner-grow-sm" role="status">
						  <span class="sr-only">Loading...</span>
						</div>
					</div>

					<div class="row" id="course-list-results">
						
					</div>
				</div>
			@else
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

								<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12">
									<div class="float-right">
										<div style="font-size: 10px">@lang('label.pay_before') (WIB)</div> {{ date('d/m/Y H:i', strtotime($checkout->expired_transaction)) }}
									</div>

									<i class="fas fa-calendar text-color fa-lg mr-3"></i>
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
			@endif
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

{{-- Course --}}
<script>
	$(document).ready(function () {
		// Call Function
		showCourse()
	})

	// Show Data
	function showCourse(nextPageUrlParam = '', action = '', filter = '', keyword = '') {
		// Initialize
		let url = `${$('#member-course-list-url').val()}?free-course=true`

		if (nextPageUrlParam) {
			url = nextPageUrlParam
		}

		// Search Course
		if (keyword) {
			url = `${url}&search=${keyword}`
		}

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = courseTemplate(data)
			    
			    $('#course-list-results').html(template)

				$('#result-loading-data').hide()
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function courseTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data.courses).length > 0) {
			$.each(data.data.courses, function (key, val) {
				// Initialize
				let isPublish 	= `<span class="badge badge-warning p-2">Draft</span>`
				let priceVal 	= ``
				let optionOne 	= `<a class="dropdown-item" href="${baseUrl}/checkout/course?course=${val.slug}">Beli</a>`

				if (val.is_publish == '1') {
					isPublish = `<span class="badge badge-success p-2">Publish</span>`
				}

				if (val.course_type == '2') {
					priceVal = `<span class="badge badge-success">Gratis</span>`
				} else {
					priceVal = `Rp. ${val.price}`
				}

				if (val.status_payment == 'complate-payment') {
					optionOne = `<a class="dropdown-item" href="${baseUrl}/checkout/detail-pembayaran/${val.checkout_id}">Selesaikan <br> Pembayaran</a>`
				} else if (val.status_payment == 'learn-course') {
					optionOne = `<a class="dropdown-item" href="${baseUrl}/student/course/learn/${val.slug}/overview/${val.subject_id}">Mulai Belajar</a>`
				}

				template += `<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 mb-4" id="card-course-area-${val.id}">
								<input type="hidden" id="link-${val.id}" name="link-course-package" value="${baseUrl}/student/course/show/member/${val.slug}">

								<div class="card card-profile h-100">
									<div class="float-right action-course-element">
										<a href="javascript:void(0)" class="btn action-course btn-sm btn-dropdown-area" id="dropdown-action-list" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="fa fa-ellipsis-v"></i>
										</a>

										<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-action-list" style="right: 0px !important; width: 1%;">
											${optionOne}
										    <a class="dropdown-item" href="${baseUrl}/student/course/show/member/${val.slug}">Detail</a>
										    <a class="dropdown-item copy-link" href="javascript:void(0)" id="${val.id}">Copy Link</a>
										</div>
									</div>

									<a href="${baseUrl}/student/course/show/member/${val.slug}">
										<img src="${val.thumbnail}" class="card-img-top fix-img-in-card" alt="preview-thumbnail">
									</a>

									<div class="card-body">
										<h5>${val.name}</h5>
										<div class="">
											${((val.description).replace( /(<([^>]+)>)/ig, '')).slice(0, 80)}...
										</div>

										<p>
											<div>${priceVal}</div>
										</p>
									</div>

									<div class="card-footer bg-white">
										<span class="text-custom-size mr-2">
											<i class="fas fa-book text-color"></i> ${(val.majors).length} ${"@lang('label.session')"}
										</span>

										<span class="text-custom-size">
											<i class="fas fa-users text-color"></i> ${(val.user_course).length} ${"@lang('label.students_join')"}
										</span>
									</div>
								</div>
							</div>`
			})

			// <a class="dropdown-item edit-course" href="javascript:void(0)" id="${val.id}">Wishlist</a>
		} else {
			template += `<div class="col-12">
							<div class="alert alert-info text-center m-auto">
								<i class="fa fa-info-circle"></i> Tidak ada data
							</div>
						</div>`
		}

		return template
	}

	// Search Course
	$(document).on('keyup', '#search-course-input', function () {
		// Initialize
		let keyword = $('#search-course-input').val()
		
		setTimeout(function () {
			// Initialize
			let q = $('#search-course-input').val()

			if (keyword == q) {
				// Remove Value In Var
				nextPageUrl = ''

				// Call Function
				showCourse('', '', '', q)

				// Dom Manipulation
				$('#load-more-area').css('display', 'none')
				$('#result-loading-data').show()
				$('#course-list-results').html('')
			}
		}, 1000)
	})
</script>
@endpush