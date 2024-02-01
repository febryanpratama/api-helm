@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/introjs.min.css" integrity="sha512-iaYE9B9u4GU8+KkRTOdRdZuzKdYw1X0hOAa4GwDV/uwdXgoX/ffT3ph1+HG1m4LPZD/HV+dkuHvWFLZtPviylQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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

	/* Style to create scroll bar in dropdown */ 
	.scrollable-dropdown{
	    height: auto;
	    max-height:320px;  /* Increase / Decrease value as per your need */
	    overflow-x: hidden;
	}
</style>
@endpush

@section('content')
{{-- Hidden --}}
<input type="hidden" id="member-course-list-url" value="{{ route('member.course.list') }}">
<input type="hidden" id="my-course" value="{{ request('my-course') }}">
<input type="hidden" id="cart-store-url" value="{{ route('cart.store') }}">
<input type="hidden" id="hint-widget-store-url" value="{{ route('hint.widget.store') }}">
<input type="hidden" id="val-more-btn-in-show-course" value="{{ $moreBtn }}">
<input type="hidden" id="val-add-to-cart-in-btn" value="{{ $cartBtn }}">

<div class="container">
	<section class="mb-5">
		<div class="clearfix bg-white border-left-element">
			<div class="float-right mr-3 mt-1">
				<div class="row">
				   	<div class="col-xs-8 col-xs-offset-2" id="search-course">
				   		<form method="POST">
					    	<div class="input-group">
					     		<div class="input-group-btn search-panel">
							      	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								    	<span id="search_concept">@lang('label.category')</span> <span class="caret"></span>
								    </button>

							      	<ul class="dropdown-menu scrollable-dropdown p-2" role="menu">
							        	<li>
							        		<a href="javascript:void(0)" class="text-dark category-data" id="all" course-name="@lang('label.all_category')">@lang('label.all_category')</a>
							        	</li>
							      	
							      		@foreach($category as $val)
							        	<li>
						        			<img src="{{ asset($val->thumbnail) }}" alt="preview-img" style="width: 30px;">
							        		
							        		<a href="javascript:void(0)" class="text-dark category-data" id="{{ $val->id }}" course-name="{{ $val->name }}">{{ $val->name }}
							        		</a>
							        	</li>
							        	@endforeach
							      	</ul>
							    </div>
							    
							    <input type="hidden" name="category_id" value="all" id="input-category-id">
					     		<input type="text" class="form-control" name="search" id="search-input" placeholder="@lang('label.learn_math')">
							    
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

			<h5 class="pt-2">
				@if (request('my-course'))
					@lang('label.me_course_package')
				@else
					@lang('label.course_package')
				@endif
			</h5>

			{{-- <span class="text-title" id="sub-text-course-element">Total Paket Kursus Aktif : {{ $countCourse }} Paket Kursus</span> --}}
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

		<div class="clearfix text-center mt-2 mb-5" id="load-more-area" style="display: none;">
			<button class="btn btn-sm btn-primary" id="load-more-course">@lang('label.more')</button>
		</div>
	</section>
</div>

{{-- Include File --}}
@include('components.cart-modal')
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/intro.min.js" integrity="sha512-B0B1bdTGi9XHtoCINbtR6z9Vi8IYvvtImIE8KWMtqWAVUt2Wn2k69GxM7ya/3rQcz/Pgm/o1WcDU347/5k202A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Intro JS --}}
<script>
	function introJsForMoreBtn() {
		if ($('#val-more-btn-in-show-course').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					skipLabel: '',
					steps: [{
						title: 'Menu',
					    element: document.querySelector('#dropdown-action-list'),
					    intro: 'Disini terdapat beberapa menu untuk kamu, membeli Paket Kursus secara langsung, masukkan Paket Kursus ke keranjang, detail Paket Kursus dan Copy Link Paket Kursus.'
					}]
				}).start()
			}, 2000)
		}
	}

	$(document).on('click', '#dropdown-action-list', function () {
		introJsForCartInBtn()

		if ($('#val-more-btn-in-show-course').val() == 0) {
			// Call Function
			insertIntroJs('more-btn-in-show-course')
		}
	})

	function introJsForCartInBtn() {
		if ($('#val-add-to-cart-in-btn').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					skipLabel: '',
					steps: [{
						title: 'Keranjang',
					    element: document.querySelector('.cart-course-package-0'),
					    intro: 'Yuk! Coba masukkan Paket Kursus kedalam Keranjang!'
					}]
				}).start()
			}, 1000)
		}
	}

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

{{-- Course --}}
<script>
	// Global Var
	let nextPageUrl = ''

	$(document).ready(function () {
		// Call Function
		showCourse()
	})

	// Show Data
	function showCourse(nextPageUrlParam = '', action = '', filter = '', keyword = '') {
		// Initialize
		let url 	 = $('#member-course-list-url').val()
		let myCourse = $('#my-course').val()

		if (nextPageUrlParam) {
			url = nextPageUrlParam
		}

		// Filter By is_admin_confirm Or is_publish
		if (filter) {
			endPoint = $('#member-course-list-url').val()
			url = `${endPoint}?filter=${filter}`
		}

		// Search Course
		if (keyword) {
			url = `${url}?search=${keyword}`
		}

		// My Course
		if (myCourse) {
			url = `${url}?my-course=true`
		}

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = courseTemplate(data)
			    
			    if (nextPageUrlParam) {
			    	$('#course-list-results').append(template)
			    } else {
				    $('#course-list-results').html(template)
			    }

		    	// Next Page
		    	nextPageUrl = data.meta.next_page_url

		    	if (nextPageUrl) {
		    		$('#load-more-area').css('display', '')
		    	} else {
		    		$('#load-more-area').css('display', 'none')
		    	}

				$('#load-more-course').attr('disabled', false)
				$('#result-loading-data').hide()
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function courseTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data.courses).length > 0) {
			$.each(data.data.courses, function (key, val) {
				// Initialize
				let categoryType 	= ``
				let priceVal 		= ``
				let optionOne 		= `
									<a class="dropdown-item" href="${baseUrl}/checkout/buy-now/${val.slug}">Beli</a>
									<a class="dropdown-item add-to-cart cart-course-package-${key}" href="javascript:void(0)" course-id="${val.id}">@lang('label.cart')</a>
									`

				if (val.course_package_category == '1') {
					categoryType = `<span class="badge badge-primary p-2">Program Magang</span>`
				} else if (val.course_package_category == '2') {
					categoryType = `<span class="badge badge-success p-2">Program Kerja</span>`
				} else {
					categoryType = `<span class="badge badge-info text-white p-2">Program Paket Kursus</span>`
				}

				if (val.course_type == '2') {
					priceVal = `<span class="badge badge-success">Gratis</span>`
				} else {
					if (val.is_private == 0) {
						priceVal = `Rp. ${val.price}`
					} else {
						if (val.user_have_course) {
							priceVal = `Rp. ${val.price}`
						}
					}
				}

				if (val.status_payment == 'complate-payment') {
					optionOne = `<a class="dropdown-item" href="${baseUrl}/checkout/detail-pembayaran/${val.checkout_id}">Selesaikan <br> Pembayaran</a>`
				} else if (val.status_payment == 'learn-course') {
					optionOne = `<a class="dropdown-item" href="${baseUrl}/student/course/learn/${val.slug}/overview/${val.subject_id}">Mulai Belajar</a>`
				}

				template += `<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 mb-4" id="card-course-area-${val.id}">
								<input type="hidden" id="link-${val.id}" name="link-course-package" value="${baseUrl}/student/course/show/member/${val.slug}">

								<div class="card card-profile h-100">
									<div class="float-left course-is-publish ml-2 mt-2">
										${categoryType}
									</div>
								
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
										<h5>
											<a href="${baseUrl}/student/course/show/member/${val.slug}" class="text-dark">
												${val.name}
											</a>
										</h5>
										<div class="">
											${((val.description).replace( /(<([^>]+)>)/ig, '')).slice(0, 80)}...
										</div>

										<p>
											<div>
												<a href="${baseUrl}/student/course/show/member/${val.slug}" class="text-dark">
													${priceVal}
												</a>
											</div>
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

			// Call Function
			introJsForMoreBtn()

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
	$(document).on('click', '.category-data', function () {
		$('#input-category-id').val($(this).attr('id'))
		$('#search_concept').html($(this).attr('course-name'))
	})

	$(document).on('submit', '#search-course form', function (e) {
		e.preventDefault()

		// Disabled True
		$('#btn-search-loading').attr('disabled', true)

		// Initialize
		let url 	 = $('#member-course-list-url').val()
		let myCourse = $('#my-course').val()
		let search 	 = $('#search-input').val()
		let category = $('#input-category-id').val()

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	myCourse,
		    	search,
		    	category
		    },
		    success: data => {
		    	// Initialize
		    	let template = courseTemplate(data)

			    $('#course-list-results').html(template)

		   		// Disabled False
				$('#btn-search-loading').attr('disabled', false)
			    
		    	// Next Page
		    	nextPageUrl = data.meta.next_page_url

		    	if (nextPageUrl) {
		    		$('#load-more-area').css('display', '')
		    	} else {
		    		$('#load-more-area').css('display', 'none')
		    	}
		    },
		    error: e => {
		        console.log(e)

           		// Disabled False
        		$('#btn-search-loading').attr('disabled', false)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	// Load More
	$(document).on('click', '#load-more-course', function () {
		// Call Function
		showCourse(nextPageUrl)

		$(this).attr('disabled', true)
	})

	$(document).on('click', '.copy-link', function (e) {
		e.preventDefault()

		// Call Function
		copyToClipboard($(`#link-${$(this).attr('id')}`).val())
	})

	function copyToClipboard(element) {
		// Notification
		toastr.success(`Link Paket Kursus di copy ke clipboard`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		// Initialize
		var $temp = $("<input>");
	 	$("body").append($temp);

	 	$temp.val(element).select();
	 	
	 	document.execCommand("copy");
	 	
	 	$temp.remove();
	}
</script>

{{-- Add To Cart --}}
<script>
	$(document).on('click', '.add-to-cart', function (e) {
		e.preventDefault()

		$(this).attr('disabled', true)

		// Initialize
		let url = $('#cart-store-url').val()

		$.ajax({
		    url: `${url}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	courseId: $(this).attr('course-id')
		    },
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
		    	}

		    	$('#cart-modal').modal('show')

		    	$('#results-data-cart').html(`<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
									        		<img src="${data.data.thumbnail}" alt="preview-img" style="width: 100%; object-fit: cover; height: 100%;">
									        	</div>

									        	<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 pt-1">
									        		<h5><b>${data.data.name}</b></h5>
									        		<span>Rp.${data.data.price}</span>
									        	</div>

									        	<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 pt-2">
									        		<a href="${baseUrl}/cart" class="btn btn-sm btn-primary w-100">@lang('label.view_cart')</a>
									        	</div>`)

		    	$('#cart-global-element').html(data.total_cart)

				if ($('#val-add-to-cart-in-btn').val() == 0) {
			    	// Call Function
					insertIntroJs('add-to-cart-in-btn')
				}
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(this).attr('disabled', false)

		        toastr.error(`500 Internal server `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>
@endpush