@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
	.thead-color {
		background-color: #f6f9fc !important;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="search-course-package-url" value="{{ route('course.transaction.search-course-package') }}">
<input type="hidden" id="cart-index-url" value="{{ route('offline.cart.index') }}">
<input type="hidden" id="cart-store-url" value="{{ route('offline.cart.store') }}">
<input type="hidden" id="transaction-store-url" value="{{ route('offline.transaction.store') }}">

<div class="container mb-4">
	<div class="row">
		<div class="col-sm-12 col-md-7 col-lg-7 col-xl-7 col-12">
			<div class="row">
				<div class="col-12 col-md-12 col-lg-12 col-xl-12 col-12">
					<div class="card card-custom">
						<div class="card-header bg-white">
							@lang('label.search_item')
						</div>

						<div class="card-body" id="search-items-area">
							<form method="POST">
								<div class="row">
									<div class="col-9">
										<div class="form-group">
											<label for="">@lang('label.enter_item_name')</label>

											<div class="clearfix">
												<select class="search-course-package w-100" id="search-course-package" name="courseId"></select>
											</div>
										</div>
									</div>

									<div class="col-3">
										<div class="form-group">
											<label for="">QTY</label>
											<input type="number" value="1" class="form-control" name="qty" style="height: 30px;">
										</div>
									</div>
								</div>

								<div class="form-group">
									<button type="submit" class="btn btn-sm btn-primary" id="add-btn-loading">Tambahkan</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="row mt-4">
				<div class="col-12 col-md-12 col-lg-12 col-xl-12 col-12">
					<div class="card card-custom">
						<div class="card-header bg-white">
							@lang('label.order_list')
						</div>

						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th width="100">Gambar</th>
											<th>Item</th>
											<th>Harga</th>
											<th>QTY</th>
											<th>Opsi</th>
										</tr>
									</thead>
									<tbody id="list-items-result">
										<tr>
											<td colspan="6" class="text-center">@lang('label.no_data')</td>
										</tr>	
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-12">
			<div class="card card-custom">
				<div class="card-body">
					<h4 class="text-center"><b>TRANSAKSI</b></h4>

					<div class="clearfix">
						<div class="float-right">
							<b id="total-items">0</b>
						</div>

						<b>Total Item :</b>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<b id="subtotal-payment">Rp.0</b>
						</div>

						<b>Sub Total :</b>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<b>Rp.0</b>
						</div>

						<b>Biaya Admin :</b>
					</div>

					<hr>
					<div class="clearfix">
						<div class="float-right">
							<b id="total-payment-text">Rp.0</b>
						</div>

						<b>Total :</b>
					</div>

					<div class="clearfix mt-4" style="font-size: 12px;">
						Dengan menyelesaikan pembelian, Anda menyetujui <b><a href="javascript:void(0)" class="text-color">Ketentuan Layanan</a></b> ini.
					</div>

					<div class="clearfix mt-2">
						<button class="btn btn-primary w-100 cursor-area" id="complate-payment" disabled="">@lang('label.complete_payment')</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- Include File --}}
@include('components.pay-offline-modal')
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>

<script>
	$(document).on('change', '#payment-type', function () {
		if (this.value == 2) {
			$('#e-money').css('display', '')
			$('#bank-transfer').css('display', 'none')
			$('#debit-credit').css('display', 'none')
		} else if (this.value == 1) {
			$('#bank-transfer').css('display', '')
			$('#e-money').css('display', 'none')
			$('#debit-credit').css('display', 'none')
		} else if (this.value == 3) {
			$('#bank-transfer').css('display', 'none')
			$('#e-money').css('display', 'none')
			$('#debit-credit').css('display', 'none')
		} else if (this.value == 4 || this.value == 5) {
			$('#bank-transfer').css('display', 'none')
			$('#e-money').css('display', 'none')
			$('#debit-credit').css('display', '')
		} else {
			$('#bank-transfer').css('display', 'none')
			$('#e-money').css('display', 'none')
			$('#debit-credit').css('display', 'none')
		}
	})
</script>

<script>
	$(document).ready(function() {
		// Initialize
		let url = $('#search-course-package-url').val()

	    $('.search-course-package').select2({
	    	placeholder: "Masukkan minimal 3 karakter",
    	    allowClear: true,
    	    minimumInputLength: 3,
    	   	ajax: {
    	   		url: `${url}`,
	         	dataType: 'json',
	         	data: (params) => {
		           return {
		             q: params.term,
		           }
		        },
	         	processResults: (data, params) => {
	           		const results = data.items.map(item => {
	             		return {
			               id: item.id,
			               text: item.full_name || item.name,
			             };
		           	});
           			
           			return {
		             results: results,
		           }
	         	},
	       	},
	    });

	    // Call Function
	    showCart()
	})
</script>

<script>
	$(document).on('submit', '#search-items-area form', function (e) {
		e.preventDefault()

		// Initialize
		let url = $('#cart-store-url').val()

		// Disabled Button
		$('#add-btn-loading').attr('disabled', true)

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
		    	// Disabled Button
		    	$('#add-btn-loading').attr('disabled', false)

		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
		    	}

		    	// Call Function
		    	showCart()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button
		        $('#add-btn-loading').attr('disabled', false)

		        toastr.error(`500 Internal server error!`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	function showCart() {
		// Initialize
		let url = $('#cart-index-url').val()

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = cartTemplate(data)

		    	$('#list-items-result').html(template)
		    	$('#total-items').html(data.qty)
		    	$('#subtotal-payment').html(data.totals)
		    	$('#total-payment').val(data.totals_num)
		    	$('#total-payment-text').html(data.totals)
		    	$('#complate-payment').attr('disabled', data.isDisabled)
		    },
		    error: e => {
		        console.log(e)
		    }
		})
	}

	function cartTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				template += `<tr>
								<td>${key+1}</td>
								<td>
									<img src="${val.course.thumbnail}" alt="preview-img" class="img-thumbnail" style="width: 100%;">
								</td>
								<td>${val.course.name}</td>
								<td>${val.course.price}</td>
								<td>${val.qty}</td>
								<td>
									<a href="javascript:void(0)" class="text-danger delete-cart" id="${val.id}" name="${val.course.name}"><i class="fas fa-trash"></i></a>
								</td>
							</tr>`
			})
		} else {
			template += `<tr>
							<td colspan="6" class="text-center">@lang('label.no_data')</td>
						</tr>`
		}

		return template
	}

	// Validate Destroy Cart
	$(document).on('click', '.delete-cart', function (e) {
	    e.preventDefault()

	    // Initialize
	    let cartId = $(this).attr('id')
	    let name   = $(this).attr('name')

	    // Validate
	    Swal.fire({
	        text: `Hapus Item ${name}?`,
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
	        url: `${baseUrl}/offline-cart/delete/${cartId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		// Call Function
		    	showCart()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>

<script>
	$(document).on('click', '#complate-payment', function () {
		$('#pay-offline-transaction-modal').modal('show')
	})

	$(document).on('submit', '#pay-offline-transaction-modal form', function (e) {
		e.preventDefault()

		// Initialize
		let url 		= $('#transaction-store-url').val()
		let paymentType = $('#payment-type option:selected').text()
		let bank 		= $('input[name="bank"]:checked').val()

		// Validate
		// if (!bank) {
		//     toastr.error(`Pilih Bank Tujuan!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		//     return 0
		// }

		// Disabled Button True
		$('#btn-pay-loading').attr('disabled', true)

		// Initialize
		let fd = new FormData(this)
		fd.append('paymentType', paymentType)
		fd.append('bank', bank)

		$.ajax({
		    url: `${url}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: fd,
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		    	if (!data.status) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        	return 0
		    	}

				$('#pay-offline-transaction-modal').modal('hide')
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        window.location = `${baseUrl}/offline-transaction/show/${data.data.id}`
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#btn-pay-loading').attr('disabled', false)

		        toastr.error(`${e.responseJSON.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>
@endpush