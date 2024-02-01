@extends('layouts.app')

@push('style')
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
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="not-logged-in-course-list-url" value="{{ route('not.logged.in.courses.with.instructor') }}">
<input type="hidden" id="instructor-id" value="{{ $instructor->id }}">

<div class="container">
	<div class="row mt-4 justify-content-center">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
			<div class="card card-custom">
				<div class="card-body">
					<div class="clearfix text-center">
						<img src="{{ $institution->Logo }}" alt="preview-img" class="avatar-preview">

						<h5 class="mt-3"><b>{{ $institution->Name }}</b></h5>
						<div style="font-size: 12px;">Mentor : {{ $instructor->name }}</div>
					</div>

					<div class="clearfix mt-3 text-center">
						<span><i class="fas fa-book text-color"></i> {{ $countCourse }} @lang('label.course_package')</span>
						<span><i class="fas fa-users text-color ml-2"></i> {{ $userJoined }} @lang('label.students_join')</span>
						{{-- <span><i class="fas fa-star text-color ml-2"></i> @lang('label.rate')</span> --}}
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="clearfix border-left-element bg-white mt-5">
		<div class="float-right mr-3 pt-1">
			<button class="btn btn-sm btn-primary btn-outline" id="dropdown-search-course" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-search"></i>
			</button>

			<div class="dropdown-menu dropdown-menu-right mt-2" aria-labelledby="dropdown-search-course" style="right: 0px !important; width: 14em !important;">
			    <div class="form-group p-2">
			    	<label for="">Cari Kursus</label>
			    	<input type="text" class="form-control" placeholder="Ex: Matematika" id="search-course-input">
			    </div>
			</div>
		</div>

		<h5 class="pt-2">@lang('label.course_package')</h5>
		{{-- <span class="text-title" id="sub-text-course-element">Total Kursus Aktif : {{ $countCourse }} Kursus</span> --}}
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
		let url 	 = $('#not-logged-in-course-list-url').val()
		let myCourse = $('#my-course').val()

		if (nextPageUrlParam) {
			url = nextPageUrlParam
		}

		// Filter By is_admin_confirm Or is_publish
		if (filter) {
			endPoint = $('#not-logged-in-course-list-url').val()
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
		    data: {
		    	user_id: $('#instructor-id').val()
		    },
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

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let isPublish 	= `<span class="badge badge-warning p-2">Draft</span>`
				let priceVal 	= ``

				if (val.is_publish == '1') {
					isPublish = `<span class="badge badge-success p-2">Publish</span>`
				}

				if (val.course_type == '2') {
					priceVal = `<span class="badge badge-info text-white">Gratis</span>`
				} else {
					if (val.is_private == 0) {
						priceVal = `Rp. ${val.price}`
					}
				}

				template += `<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 mb-3" id="card-course-area-${val.id}">
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
											<a href="${baseUrl}/detail/paket-kursus/${val.slug}" class="text-dark">
												<div>${priceVal}</div>
											</a>
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

	// Load More
	$(document).on('click', '#load-more-course', function () {
		// Call Function
		showCourse(nextPageUrl)

		$(this).attr('disabled', true)
	})
</script>
@endpush