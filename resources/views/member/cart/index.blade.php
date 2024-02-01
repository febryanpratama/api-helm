@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/introjs.min.css" integrity="sha512-iaYE9B9u4GU8+KkRTOdRdZuzKdYw1X0hOAa4GwDV/uwdXgoX/ffT3ph1+HG1m4LPZD/HV+dkuHvWFLZtPviylQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="hint-widget-store-url" value="{{ route('hint.widget.store') }}">
<input type="hidden" id="val-checkout-btn" value="{{ $checkoutBtn }}">

<div class="container mb-4">
	<div class="row mt-4">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			<div class="clearfix bg-white card-custom">
				<div class="border-left-element">
					<div class="p-2">
						<div class="float-right pr-2">
							<a href="{{ route('member.course.index') }}" class="text-dark"><i class="fas fa-arrow-left"></i> @lang('label.keep_shopping')</a>
						</div>

						<b>@lang('label.shopping_cart')</b>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			@if (count($carts) > 0)
				<div class="card card-custom mt-4">
					<div class="card-header bg-white">
						<div class="float-right">
							<b class="text-color">Total : <span id="total-payment">{{ $total }}</span></b>
						</div>

						<h5 class=""><b>{{ count($carts) }} @lang('label.couse_package_in_cart')</b></h5>
					</div>

					<div class="clearfix p-3">
						@foreach($carts as $val)
						<div class="card mb-3" id="card-cart-area-{{ $val->id }}">
							<div class="card-body" style="border: 0px solid #bae0bd !important;">
								<div class="row">
									<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 text-center">
										<img src="{{ $val->course->thumbnail }}" alt="preview-img-course" class="pl-3 pb-3" style="width: 50%; object-fit: cover; height: 90%;">
									</div>

									<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 pl-sm-2">
										<b>{{ $val->course->name }}</b>

										<p>
											<span>Pengajar : {{ $val->course->user->name }}</span> <br>
											<span>Lembaga Kursus : {{ $val->course->user->company->Name }}</span>
										</p>
									</div>

									<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 pl-sm-2 mb-sm-2">
										<b>
											@if ($val->course->course_type == 2)
												<span class="badge badge-success">@lang('label.free')</span>
											@else
												Rp. {{ $val->course->price }}
											@endif
										</b>
										
										<div>
											<span>Masa Aktif Kursus : <b>{{ $val->course->periode }} {{ coursePeriode($val->course->periode_type) }}</b> <i class="fas fa-question-circle config-tooltip cursor-area text-color" data-toggle="tooltip" data-placement="top" title="Masa Aktif Kursus akan berjalan setelah pembayaran."></i></span>
										</div>

										<div class="clearfix">
											<a href="javascript:void(0)" class="text-color delete-cart" id="{{ $val->id }}" name="{{ $val->course->name }}">@lang('label.delete')</a>
										</div>
									</div>
								</div>
							</div>
						</div>
						@endforeach

						<div class="clearfix" id="checkout-btn-area">
							<div class="float-right">
								<a href="{{ route('member.checkout.index') }}" class="btn btn-sm btn-primary" id="checkout-btn-loading">Checkout</a>
							</div>
						</div>
					</div>
				</div>
			@else
			<div class="card card-custom mt-4 mb-3">
				<div class="card-body" style="border: 0px solid #bae0bd !important;">
					<div class="clearfix text-center">
						<img src="{{ asset('img/cart-no-data.jpg') }}" alt="preview-img" style="width: 20%;">
					</div>
				</div>

				<div class="card-footer bg-white text-center" style="border: 0;">
					@lang('label.cart_is_empty') <a href="{{ route('member.course.index') }}" class="text-color">@lang('label.here')</a>
				</div>
			</div>
			@endif
		</div>
	</div>
</div>
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/intro.min.js" integrity="sha512-B0B1bdTGi9XHtoCINbtR6z9Vi8IYvvtImIE8KWMtqWAVUt2Wn2k69GxM7ya/3rQcz/Pgm/o1WcDU347/5k202A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Intro JS --}}
<script>
	$(document).ready(function () {
		if ($('#val-checkout-btn').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					skipLabel: '',
					steps: [{
						title: 'Checkout',
					    element: document.querySelector('#checkout-btn-loading'),
					    intro: 'Klik disini, untuk melanjutkan ke halaman Checkout.'
					}]
				}).start()
			}, 2000)
		}
	})

	$(document).on('click', '.introjs-donebutton', function () {
		if ($('#val-checkout-btn').val() == 0) {
			// Call Function
			insertIntroJs('checkout-btn')
		}
	})

	function insertIntroJs(page) {
		// Initialize
		let url = $('#hint-widget-store-url').val()

	    $.ajax({
	        url: `${url}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        data: {
	        	page : page
	        },
	        success: data => {},
	        error: e => {
	            console.log(e)
	        }
	    })
	}
</script>

<script>
	// Validate Destroy Cart
	$(document).on('click', '.delete-cart', function (e) {
	    e.preventDefault()

	    // Initialize
	    let cartId = $(this).attr('id')

	    // Validate
	    Swal.fire({
	        text: `@lang('label.delete_course_package_from_cart')?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyCart(cartId)
	      }
	    })
	})

	// Destroy Cart
	function destroyCart (cartId) {
	    $.ajax({
	        url: `${baseUrl}/cart/delete/${cartId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	    		$('#total-payment').html(data.total)

	    		$(`#card-cart-area-${cartId}`).remove()

	    		if (data.total == 'Rp.0') {
	    			$('#checkout-btn-area').css('display', 'none')

	    			location.reload()
	    		}
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>
@endpush