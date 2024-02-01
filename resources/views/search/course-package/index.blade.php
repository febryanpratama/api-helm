@extends('layouts.app')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/css/swiper.min.css" integrity="sha256-DBYdrj7BxKM3slMeqBVWX2otx7x4eqoHRJCsSDJ0Nxw=" crossorigin="anonymous"/>

<style>
	.avatar-preview {
		width: 100px;
		height: 100px;
	    border-radius: 100%;
	    border: 1px solid gray;
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

	.course-is-publish {
		position: absolute;
	}

	.bansner-invite-instructor {
		width: 100%;
		height: 100%;
	}

	.swiper {
		width: 100%;
		height: 100%;
	}

	.swiper-slide {
		text-align: center;
		font-size: 18px;
		background: #fff;

		/* Center slide text vertically */
		display: -webkit-box;
		display: -ms-flexbox;
		display: -webkit-flex;
		display: flex;
		-webkit-box-pack: center;
		-ms-flex-pack: center;
		-webkit-justify-content: center;
		justify-content: center;
		-webkit-box-align: center;
		-ms-flex-align: center;
		-webkit-align-items: center;
		align-items: center;
	}

	.swiper-slide img {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
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
{{-- Hidden Element --}}
<input type="hidden" id="not-logged-in-course-list-url" value="{{ route('not.logged.in.courses') }}">

<div class="container">
	<div class="clearfix mt-4">
		<!-- Swiper -->
	    <div class="swiper-container swiper-invite-instructor">
	      <div class="swiper-wrapper">
	        <div class="swiper-slide">
	        	<a href="{{ route('auth.signin') }}?insturctor=true">
		        	<img src="{{ asset('img/slide-1.jpg') }}" alt="preview-img" class="banner-invite-instructor">
	        	</a>
	        </div>
	        <div class="swiper-slide">
	        	<a href="{{ route('auth.signin') }}?insturctor=true">
		        	<img src="{{ asset('img/slide-2.jpg') }}" alt="preview-img" class="banner-invite-instructor">
		        </a>
	        </div>
	        <div class="swiper-slide">
	        	<a href="{{ route('auth.signin') }}?insturctor=true">
		        	<img src="{{ asset('img/slide-3.jpg') }}" alt="preview-img" class="banner-invite-instructor">
		        </a>
	        </div>
	      </div>
	      <div class="swiper-pagination"></div>
	    </div>
	</div>

	<div class="clearfix border-left-element bg-white mt-4">
		<div class="float-right mr-3 pt-1">
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

		<h5 class="pt-2">@lang('label.course_package')</h5>
		{{-- <span class="text-title" id="sub-text-course-element">Total Paket Kursus Aktif : {{ $countCourse }} Paket Kursus</span> --}}
	</div>
</div>

<section class="mt-4 mb-5">
	<div class="container">
		<div class="clearfix mt-4">
			<div class="text-center mt-5" id="result-loading-data">
				@lang('label.retrive_data')...

				<div class="spinner-grow spinner-grow-sm" role="status">
				  <span class="sr-only">Loading...</span>
				</div>
			</div>

			<div class="row" id="course-list-results">
				
			</div>

			<div class="clearfix text-center mt-2 mb-5" id="load-more-area" style="display: none;">
				<button class="btn btn-sm btn-primary" id="load-more-course">Lebih Banyak</button>
			</div>
		</div>
	</div>
</section>

{{-- Include File --}}
@include('landing.footer')
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/js/swiper.min.js" integrity="sha256-4sETKhh3aSyi6NRiA+qunPaTawqSMDQca/xLWu27Hg4=" crossorigin="anonymous"></script>

{{-- Course --}}
<script>
	// Global Var
	let nextPageUrl = ''

	$(document).ready(function () {
		// Swiper
		let swiper = new Swiper(".swiper-invite-instructor", {
			autoplay: {
	          delay: 4500,
	          disableOnInteraction: false,
	        },
			pagination: {
			  el: ".swiper-pagination",
			  dynamicBullets: true,
			},
		});

		// Call Function
		showCourse()
	})

	// Show Data
	function showCourse(nextPageUrlParam = '') {
		// Initialize
		let url 	 = $('#not-logged-in-course-list-url').val()
		let myCourse = $('#my-course').val()

		if (nextPageUrlParam) {
			url = nextPageUrlParam
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

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function courseTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let isPublish 	= `<span class="badge badge-warning p-2">Draft</span>`
				let priceVal 	= ``

				if (val.is_publish == '1') {
					isPublish = `<span class="badge badge-success p-2">Publish</span>`
				}

				if (val.course_type == '2') {
					priceVal = `<span class="badge badge-success">Gratis</span>`
				} else {
					if (val.is_private == 0) {
						priceVal = `Rp. ${val.price}`
					}
				}

				template += `<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 mb-4" id="card-course-area-${val.id}">
								<div class="card card-custom h-100">
									<div class="float-right action-course-element">
										<a href="javascript:void(0)" class="btn action-course btn-sm btn-dropdown-area" id="dropdown-action-list" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="fa fa-ellipsis-v"></i>
										</a>

										<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-action-list" style="right: 0px !important; width: 1%;">
										    <a class="dropdown-item" href="${baseUrl}/detail/paket-kursus/${val.slug}">Detail</a>
										</div>
									</div>

									<a href="${baseUrl}/detail/paket-kursus/${val.slug}">
										<img src="${val.thumbnail}" class="card-img-top fix-img-in-card" alt="preview-thumbnail">
									</a>

									<div class="card-body">
										<h5>
											<a href="${baseUrl}/detail/paket-kursus/${val.slug}" class="text-dark">
												${val.name}
											</a>
										</h5>

										<div class="">
											${((val.description).replace( /(<([^>]+)>)/ig, '')).slice(0, 80)}...
										</div>

										<p>
											<div>
												<a href="${baseUrl}/detail/paket-kursus/${val.slug}" class="text-dark">
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
		let url 	 = $('#not-logged-in-course-list-url').val()
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
</script>
@endpush