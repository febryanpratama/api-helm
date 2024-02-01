@extends('layouts.master')

@push('style')
<style>
	.card-detail {
		border-radius: 5px;
		box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
		border: 0;
	}

	.text-color {
		color: #62ddbd !important;
	}

	.b-bottom {
		border-bottom: 1px solid gray;
	}

	/* Tabs Card */
	.tab-card {
	  border:1px solid #eee;
	}

	.tab-card-header {
	  background:none;
	}

	/* Default mode */
	.tab-card-header > .nav-tabs {
	  border: none;
	  margin: 0px;
	}
	.tab-card-header > .nav-tabs > li {
	  margin-right: 2px;
	}
	.tab-card-header > .nav-tabs > li > a {
	  border: 0;
	  border-bottom:2px solid transparent;
	  margin-right: 0;
	  color: #737373;
	  padding: 2px 15px;
	}

	.tab-card-header > .nav-tabs > li > a.show {
	    border-bottom: 2px solid #007bff;
	    color: #007bff;
	}
	.tab-card-header > .nav-tabs > li > a:hover {
	    color: #62ddbd;
	    border-bottom: #62ddbd !important;
	}

	.tab-card-header > .tab-content {
	  padding-bottom: 0;
	}

	.card-footer {
		border-top: 0px !important;
	}

	.tab-active {
		color: #62ddbd !important;
		border-bottom: 2px solid #62ddbd !important;
	}

	.add-class-btn {
		position: absolute;
	    right: 1em;
	    top: 1em;
	}

	.text-class {
		position: absolute;
	    top: 1em;
	}

	.theory-1 {
		background-color: #f8f8f8;
	}

	.btn-xs {
		padding: 0.1rem 0.5rem;
	    font-size: 0.6999rem;
	    line-height: 1.6;
	    border-radius: 0.2rem;
	}

	.action-course {
		background-color: white !important;
		width: 2.5em;
	}

	.dropdown-menu:before {
		content: '';
		position: absolute;
	    /*top: 0 !important;*/
	    right: 0 !important;
	    border: 10px solid white !important;
	    border-color: transparent !important;
	}

	.dropdown-menu-right-custom-first {
		top: -7em !important;
		left: -2em !important;
	}

	.dropdown-menu-right-custom-last {
		top: -250px !important;
	}

	.progress-bar {
		background-color: #62ddbd !important;
	}

	.rating-bg-color {
		color: #62ddbd !important;
	}

	#post-rating:hover {
		text-decoration: none;
	}

	.avatar-preview {
		width: 100px;
	    height: 80px;
	    -webkit-box-shadow: 0 0 15px rgb(0 0 0 / 20%);
	    box-shadow: 0 0 15px rgb(0 0 0 / 20%);
	    border-radius: 100%;
	}

	.border-custom {
		border: 2px dotted #dee2e6 !important;
	    min-width: 15%;
	    height: 2.6em;
	    text-align: center;
	    border-radius: 11px;
	    font-size: 12px;
	}

	.playlist-features p i {
	    height: 20px;
	    width: 20px;
	    display: inline-block;
	    text-align: center;
	    line-height: 20px;
	    font-size: 14px;
	    border-radius: 50%;
	    margin-right: 10px;
	    background-color: #62DDBD;
	    color: white;
	}

	@media screen and (max-width: 576px) {
		.pb-sm-c-4 {
			padding-bottom: 1.5rem !important;
		}
	}

	@media screen and (max-width: 768px) {
		.pb-sm-c-4 {
			padding-bottom: 1.5rem !important;
		}
	}
</style>
@endpush

@section('content')
{{-- Hidden Input --}}
<input type="hidden" id="rating-url-store" value="{{ route('rating.store') }}">
<input type="hidden" id="class-list-url" value="{{ route('majors.index') }}">
<input type="hidden" id="reviews-list-url" value="{{ route('rating.index') }}">
<input type="hidden" id="course-id" value="{{ $course->id }}">
<input type="hidden" id="cart-store-url" value="{{ route('cart.store') }}">

<div class="container">
	<div class="row mt-4">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12">
			<div class="card card-detail">
				<div class="card-body">
					<div class="row">
						<div class="col-12 col-md-3 col-lg-3 col-xl-3 col-12 pt-2">
							<img src="{{ $course->thumbnail }}" alt="preview-img" class="w-100 fix-img-detail-course-package pb-sm-c-4">
						</div>

						<div class="col-12 col-md-9 col-lg-9 col-xl-9 col-12">
							<div class="float-right">
								@if ($purchased)
									<label for="">@lang('label.learning_progress')</label>
									<div class="progress">
									  <div class="progress-bar" role="progressbar" style="width: {{ ceil($percentage) }}%;" aria-valuenow="{{ ceil($percentage) }}" aria-valuemin="0" aria-valuemax="100">{{ ceil($percentage) }}%</div>
									</div>
								@elseif ($checkoutDt && $checkoutDt->checkout->status_payment == 0)
									<a href="{{ route('member.checkout.show', $checkoutDt->id) }}" class="btn btn-primary btn-sm w-100">
										@lang('label.transaction_detail')
									</a>
								@else
									@if ($course->is_private == 0)
										<a href="{{ route('member.checkout.buy.now', $course->slug) }}" class="btn btn-outline-primary btn-sm">
											@lang('label.buy_now')
										</a>
										
										<button class="btn btn-primary btn-sm" id="add-to-cart" course-id="{{ $course->id }}">
											<i class="fas fa-plus"></i> @lang('label.cart')
										</button>
									@endif
								@endif
							</div>

							<div class="clearfix">
								<h5><b>{{ $course->name }}</b></h5>

								@if($course->course_type == 2)
									<span class="badge badge-success">@lang('label.free')</span>
								@else
									@if ($course->is_private == 0)
										<span><b>Rp. {{ $course->price }}</b></span>
									@else
										@if ($purchased)
											<span><b>Rp. {{ $course->price }}</b></span>
										@endif
									@endif
								@endif
							</div>

							<div class="clearfix mt-3">
								<div class="border-custom p-1 float-left">
									<b><i class="fas fa-clipboard-list text-color"></i> {{ count($course->majors) }} @lang('label.session')</b>
								</div>

								<div class="border-custom p-1 float-left ml-2">
									<b><i class="fas fa-book text-color text-color"></i> {{ $theory }} @lang('label.theory')</b>
								</div>

								<div class="border-custom p-1 float-left ml-2">
									<b class="p-4"><i class="fas fa-users text-color text-color"></i> {{ $totalStudent }} @lang('label.students_join')</b>
								</div>
							</div>

							<div class="clearfix mt-3">
								<div class="border-custom p-1 float-left">
									<b><i class="fas fa-star text-color"></i> {{ ($totalRate) ? substr($totalRate, 0, 3) : '0' }} @lang('label.reviews')</b>
								</div>

								<div class="border-custom p-1 float-left ml-2">
									<b><i class="fas fa-user text-color"></i> <a href="{{ URL::to('/').'/institution/'.$course->user->company->Name }}" class="text-dark" target="_blank">{{ $course->user->company->Name }}</a> <i class="fas fa-question-circle text-color config-tooltip cursor-area" data-toggle="tooltip" data-placement="top" title="@lang('label.institution_name')"></i></b>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
					  <li class="nav-item">
					      <a class="nav-link tab-link-course tab-active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview-tab" aria-selected="true"><b>Overview</b></a>
					  </li>
					  <li class="nav-item">
					      <a class="nav-link tab-link-course" id="curriculum-tab" data-toggle="tab" href="#curriculum" role="tab" aria-controls="curriculum" aria-selected="false"><b>@lang('label.session_and_theory')</b></a>
					  </li>
					  <li class="nav-item tab-link-course">
					      <a class="nav-link" id="review-tab" data-toggle="tab" href="#review" role="tab" aria-controls="review" aria-selected="false"><b>@lang('label.reviews')</b></a>
					  </li>
					</ul>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 mt-4">
			<div class="card card-detail mt-2 mb-4">
				<div class="card-body">
					<div class="tab-content" id="myTabContent">
		              <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
		                <div class="clearfix">
    		                <h5 class="card-title"><b>Deskripsi</b></h5>
    	                	<div class="card-text">
    	                		{!! $course->description !!}
    	                	</div>

    	                	<h5><b>Apa yang kamu pelajari?</b></h5>
    	                	
    	                	<div class="playlist-features mt-3">
    	                		@forelse($course->majors as $val)
	    	                	  <p><i class="fas fa-check"></i> {{ $val->Name }}</p>
	    	                	@empty
	    	                	@endforelse
    	                	</div>
		                </div>
		              </div>

		              <div class="tab-pane fade" id="curriculum" role="tabpanel" aria-labelledby="curriculum-tab">
		              	<div class="clearfix">
	              			<div class="float-left text-class">
	              				<h5 class="card-title"><b>@lang('label.session_and_theory')</b></h5>
	              			</div>
		              	</div>

		              	<div id="class-materi-results" class="mt-2">
		              		<div class="text-center mt-4" id="result-loading-data">
		              			Mengambil Data...

		              			<div class="spinner-grow spinner-grow-sm" role="status">
		              			  <span class="sr-only">Loading...</span>
		              			</div>
		              		</div>
		              	</div>
		              </div>

		              <div class="tab-pane fade" id="review" role="tabpanel" aria-labelledby="review-tab">
		              	<div class="clearfix">
		              		<h5 class="card-title"><b>@lang('label.preview_list')</b></h5>
		              				              			
	              			<div class="row" id="course-list">
	              				<div class="text-center mt-4 w-100" id="result-loading-data">
	              					Mengambil Data...

	              					<div class="spinner-grow spinner-grow-sm" role="status">
	              					  <span class="sr-only">Loading...</span>
	              					</div>
	              				</div>
	              			</div>
		              	</div>
		              </div>
		            </div>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- Include File --}}
@include('components.cart-modal')
@stop

@push('script')
<script>
	$(document).ready(function () {
		// $('#cart-modal').modal('show')
	})
</script>

{{-- Configs --}}
<script>
	$(document).on('click', '.tab-link-course', function () {
		$('.tab-link-course').removeClass('tab-active')
		$(this).addClass('tab-active')
	})
</script>

{{-- Class --}}
<script>
	{{-- Global Var --}}
	let classId = []

	$(document).on('click', '#curriculum-tab', function () {
		// Check Attr
		if ($(this).attr('click')) {
			return 0
		}
		
		$(this).attr('click', true)

		// Call Function
		showClassAsync()
	})

	// Show Data
	async function showClass(nextPageUrlParam = '') {
		// Initialize
		let url = $('#class-list-url').val()

		const result = $.ajax({
		    url: `${url}?course_id=${$('#course-id').val()}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = classTemplate(data)

		    	$('#class-materi-results').html(template)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})

		return result
	}

	async function showClassAsync () {
		const result = await showClass()

		if (classId.length > 0) {
			$.each(classId, function (key, val) {
				// Call Function
				showTheory(val)
			})
		}
	}

	function classTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Adding Value To Var
				classId.push(val.ID)

				// Initialize
				let meetId 			  = Math.floor(Math.random() * 10000)
				let dropdownMenuClass = ``

				if ((data.data).length == 1) {
					dropdownMenuClass = 'dropdown-menu-right-custom-first'
				} else if ((data.data).length == (key + 1)) {
					if (key == 1) {
						dropdownMenuClass = 'dropdown-menu-right-custom-last'
					}
				}

				template += `<div class="clearfix border mt-4 p-3" style="border-radius: 5px;" id="card-majors-area-${val.ID}">
			              		<h5><b>${val.Name}</b></h5>

			              		<div class="clearfix mt-3 mb-3">
			              			${val.Details}
			              		</div>

			              		<div class="clearfix mt-3" id="theory-results-${val.ID}"></div>
			              		<div class="clearfix mt-3" id="meeting-room-results-${val.ID}"></div>
			              	</div>`

			    setTimeout(function () {
			    	// Call Function
			    	meetingRoom(val.ID)
			    }, 2000)
			})
		} else {
			template += `<div class="alert alert-info text-center mt-4">
				<i class="fas fa-info-circle"></i> @lang('label.no_session_yet')
			</div>`
		}

		return template
	}
</script>

{{-- Theory --}}
<script>
	function showTheory(majorId) {
		$(`#theory-results-${majorId}`).html(`<div class="text-center mt-5" id="result-loading-data">
						              			Mengambil Data Materi...

						              			<div class="spinner-grow spinner-grow-sm" role="status">
						              			  <span class="sr-only">Loading...</span>
						              			</div>
						              		</div>`)
		
		$.ajax({
		    url: `${baseUrl}/subject?majorId=${majorId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	setTimeout(function () {
			    	// Initialize
			    	let template = theoryTemplate(data, majorId)
			    	
			    	$(`#theory-results-${majorId}`).html(template)
		    	}, 1000)

		    	setTimeout(function () {
		    		// Call Function
		    		showTask(majorId)
		    	}, 2000)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data Materi gagal dimuat`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function theoryTemplate(data, majorId) {
		let template = `<div class="row justify-content-center">`

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let classBgColor = ``
				let lockIcon 	 = ``
				let iconType 	 = `<i class="fas fa-play-circle text-color ml-2 mr-2 fa-lg"></i>`
				
				// Condition
				if ((key % 2) == 0) {
					classBgColor = 'theory-1'
				}

				if (val.FileType == 1) {
					iconType = `<i class="fas fa-file text-color ml-2 mr-2"></i>`
				}

				if (!val.unlock) {
					lockIcon = `<i class="fas fa-lock text-color"></i>`
				}
				
				// <span class="badge" style="background-color: #dfe8f2;">03:20</span>
				
				if (val.courseExists == 'y') {
					if (!val.unlock) {
						template += `<div class="col-11 p-2 ${classBgColor}" id="theory-result-element-${val.ID}">
		              					<div class="float-right">
		              						<i class="fas fa-lock text-color"></i>
		              					</div>

		              					${iconType} ${val.Name}
			              			</div>`
					} else {
						template += `<div class="col-11 p-2 ${classBgColor}" id="theory-result-element-${val.ID}">
			              				<a href="${baseUrl}/student/course/learn/${val.slug}/overview/${val.ID}" title="" class="text-dark">
			              					<div class="float-right">
			              						${lockIcon}
			              					</div>

			              					${iconType} ${val.Name}
		              					</a>
			              			</div>`
					}
				} else {
					template += `<div class="col-11 p-2 ${classBgColor}" id="theory-result-element-${val.ID}">
	              					<div class="float-right">
	              						<i class="fas fa-lock text-color"></i>
	              					</div>

	              					${iconType} ${val.Name}
		              			</div>`
				}
			})

			template += `
							</div>
						
							<div class="mt-4" id="list-task">
								<h5 class="pl-4"><b>Tugas</b></h5>

								<div class="row justify-content-center" id="results-task-${majorId}">
									<div class="text-center mt-4" id="result-loading-data-tasks">
										Mengambil Data...

										<div class="spinner-grow spinner-grow-sm" role="status">
										  <span class="sr-only">Loading...</span>
										</div>
									</div>
								</div>
							</div>
						`
		} else {
			template = ``
		}

		return template
	}
</script>

{{-- Tasks --}}
<script>
	function showTask(majorId) {
		$.ajax({
		    url: `${baseUrl}/task/index?majorId=${majorId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = tasksTemplate(data, majorId)

		    	$(`#results-task-${majorId}`).html(template)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data Tugas gagal dimuat`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function tasksTemplate(data, majorId) {
		// Initialize
		let template = ``

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let classBgColor = ``
				let iconType 	 = `<i class="fas fa-clipboard-list text-color ml-2 mr-2 fa-lg"></i>`
				
				// Condition
				if ((key % 2) == 0) {
					classBgColor = 'theory-1'
				}

				template += `<div class="col-11 p-2 ${classBgColor}" id="task-result-element-${val.id}">
              					<a href="${baseUrl}/task/show/${val.id}" title="" class="text-dark">
              						${iconType} ${val.name}
              					</a>
	              			</div>`
			})
		} else {
			template += `<div class="col-11 p-2 theory-1">
				Tidak ada Tugas
			</div>`
		}

		return template
	}
</script>

{{-- Rating --}}
<script>
	$(document).on('click', '#review-tab', function () {
		// Check Attr
		if ($(this).attr('click')) {
			return 0
		}
		
		$(this).attr('click', true)

		// Call Function
		showReviews()
	})

	function showReviews() {
		// Initialize
		let url = $('#reviews-list-url').val()

		$.ajax({
		    url: `${url}?course_id=${$('#course-id').val()}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = reviewsTemplate(data)

		    	$('#course-list').html(template)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function reviewsTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data.length) > 0) {
			$.each(data.data, function (key, val) {
				template += `<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-12 text-center mb-3">
              					<img src="${(val.user.avatar) ? val.user.avatar : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg'}" alt="avatar-review" class="avatar-preview">
              				</div>

              				<div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mb-3">
              					<b>${val.user.name}</b>
              					<div class="mt-1">
              						<i class="fas fa-star ${(val.rating >= 1) ? 'text-color' : ''}"></i>
              						<i class="fas fa-star ${(val.rating >= 2) ? 'text-color' : ''}"></i>
              						<i class="fas fa-star ${(val.rating >= 3) ? 'text-color' : ''}"></i>
              						<i class="fas fa-star ${(val.rating >= 4) ? 'text-color' : ''}"></i>
              						<i class="fas fa-star ${(val.rating >= 5) ? 'text-color' : ''}"></i>
              					</div>
              					<span>
              						${val.description}
              					</span>
              				</div>`
			})
		} else {
			template += `<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 text-center">
							<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> @lang('label.no_reviews_yet')</div>
						</div>`
		}

		return template
	}

	$(document).on('click', '.click-star', function () {
		if ($(this).attr('rating-bg-color') && $(this).attr('id') == 'r-1') {
			$('#r-1').removeClass('rating-bg-color')
			$('#r-2').removeClass('rating-bg-color')
			$('#r-3').removeClass('rating-bg-color')
			$('#r-4').removeClass('rating-bg-color')
			$('#r-5').removeClass('rating-bg-color')

			$(this).removeAttr('rating-bg-color')
		} else {
			$(this).addClass('rating-bg-color')
			$(this).attr('rating-bg-color', 'true')

			$('#post-rating').attr('disabled', false)
		}

		if ($(this).attr('id') == 'r-1') {
			$('#r-2').removeClass('rating-bg-color')
			$('#r-3').removeClass('rating-bg-color')
			$('#r-4').removeClass('rating-bg-color')
			$('#r-5').removeClass('rating-bg-color')

			$('#rating-value').val('1')
		} else if ($(this).attr('id') == 'r-2') {
			$('#r-1').addClass('rating-bg-color')
			$('#r-3').removeClass('rating-bg-color')
			$('#r-4').removeClass('rating-bg-color')
			$('#r-5').removeClass('rating-bg-color')

			$('#rating-value').val('2')
		} else if ($(this).attr('id') == 'r-3') {
			$('#r-1').addClass('rating-bg-color')
			$('#r-2').addClass('rating-bg-color')
			$('#r-4').removeClass('rating-bg-color')
			$('#r-5').removeClass('rating-bg-color')
			
			$('#rating-value').val('3')
		} else if ($(this).attr('id') == 'r-4') {
			$('#r-1').addClass('rating-bg-color')
			$('#r-2').addClass('rating-bg-color')
			$('#r-3').addClass('rating-bg-color')
			$('#r-5').removeClass('rating-bg-color')
			
			$('#rating-value').val('4')
		} else if ($(this).attr('id') == 'r-5') {
			$('#r-1').addClass('rating-bg-color')
			$('#r-2').addClass('rating-bg-color')
			$('#r-3').addClass('rating-bg-color')
			$('#r-4').addClass('rating-bg-color')
			
			$('#rating-value').val('5')
		}
	})

	$(document).on('click', '#post-rating', function (e) {
		e.preventDefault()

		// Initialize
		let url 		= $('#rating-url-store').val()
		let rating 		= $('#rating-value').val()
		let description = $('#rating-description').val()

		// Validate
		if (!rating) {
			toastr.error(`Rating harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if ($(this).attr('action') == 'edit') {
			url = `${baseUrl}/rating/update/${$('#rating-id').val()}`
		}

		// Disabled Button True
		$(this).attr('disabled', true)

		$.ajax({
		    url: `${url}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	courseId: $('#course-id').val(),
		    	rating: rating,
		    	description: description
		    },
		    success: data => {
				toastr.success(data.message, 'Sukses!', {closeButton:!0, tapToDismiss:!1, rtl:o})

				setTimeout(function () {
					location.reload()
				}, 1000)
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(this).attr('disabled', false)

		        // Check Action
		        if ($('#action-form').val() == 'edit') {
		        	toastr.error(`Data gagal diperbaharui`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		        } else {
		        	toastr.error(`Data gagal disimpan`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		        }
		    }
		})
	})

	$(document).on('click', '#edit-review', function (e) {
		e.preventDefault()

		$('#rating-exists').css('display', 'none')
		$('#rating-edit').css('display', '')
		$('#delete-rating-area').html(`<button type="button" class="btn btn-link text-color" id="post-rating" action="edit"><b>POSTING</b></button>`)
	})

	$(document).on('click', '#delete-rating', function (e) {
		e.preventDefault()

		$(this).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/rating/delete/${$(this).attr('rating-id')}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		        toastr.success(`${data.message}`, 'Sukses!', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(this).attr('disabled', false)

		        toastr.error(`500 Internal server error!`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>

{{-- Add To Cart --}}
<script>
	$(document).on('click', '#add-to-cart', function (e) {
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
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(this).attr('disabled', false)

		        toastr.error(`500 Internal server error!`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>

{{-- Meeting Room --}}
<script>
	function meetingRoom (sessionId) {
		$.ajax({
		    url: `${baseUrl}/meeting-room?sessionId=${sessionId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = meetingRoomTemplate(data)

		    	$(`#meeting-room-results-${sessionId}`).html(template)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function meetingRoomTemplate(data) {
		// Initialize
		let template = `<h5 class="pl-4-custom"><b>Meeting Room</b></h5>
						<div class="row justify-content-center">`

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let classBgColor = ``
				let iconType 	 = `<i class="fa-solid fa-chalkboard-user text-color"></i>`
				
				// Condition
				if ((key % 2) == 0) {
					classBgColor = 'theory-1'
				}

				template += `<div class="col-11 p-2 ${classBgColor}" id="meeting-room-result-element-${val.id}">
              					<a href="${baseUrl}/meeting-room/show/${val.id}" title="" class="text-dark intro-show-meeting-room-new-account">
              						${iconType} ${val.name}
              					</a>
	              			</div>`
			})
		} else {
			template += `<div class="col-11 p-2 theory-1">
							Tidak ada Meeting Room
						</div>`
		}

		template += `</div>`

		return template
	}
</script>
@endpush