@extends('layouts.app')

@push('style')
<style>
	.border-left-element {
		border-left: 2px solid #62ddbd;
		padding-left: 10px;
	}

	.card-container {
		background-color: white;
		border-radius: 5px;
        box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
		color: black;
		padding-top: 30px;
		position: relative;
		width: 350px;
		max-width: 100%;
		text-align: center;
	}

	.card-container .pro {
		color: #231E39;
		background-color: #FEBB0B;
		border-radius: 3px;
		font-size: 14px;
		font-weight: bold;
		padding: 3px 7px;
		position: absolute;
		top: 30px;
		left: 30px;
	}

	.avatar-preview {
		width: 100px;
		height: 100px;
	    border-radius: 100%;
	    border: 1px solid #62DDBD;
	}

	.skills {
		background-color: white;
        box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
		text-align: left;
		padding: 0px 15px 15px 15px;
		margin-top: 30px;
	}

	.skills ul {
		list-style-type: none;
		margin: 0;
		padding: 0;
	}

	.skills ul li {
		border: 1px solid #2D2747;
		border-radius: 2px;
		display: inline-block;
		font-size: 12px;
		margin: 0 7px 7px 0;
		padding: 7px;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="not-logged-in-instructor-list-url" value="{{ route('not.logged.in.instructor') }}">

<div class="container">
	<div class="clearfix border-left-element bg-white mt-5">
		<div class="float-right mr-3 mt-3">
			<button class="btn btn-sm btn-primary btn-outline" id="dropdown-search-instructor" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-search"></i>
			</button>

			<div class="dropdown-menu dropdown-menu-right mt-2" aria-labelledby="dropdown-search-instructor" style="right: 0px !important; width: 14em !important;">
			    <div class="form-group p-2">
			    	<label for="">@lang('label.search_mentor')</label>
			    	<input type="text" class="form-control" placeholder="Masukkan Nama Lembaga" id="search-instructor-input">
			    </div>
			</div>
		</div>

		<h5 class="pt-2">@lang('label.search_mentor')</h5>
		<span class="text-title" id="sub-text-course-element">Total Lembaga : {{ $totalInstructor }} Lembaga</span>
	</div>

	<div class="clearfix mt-4">
		<div class="text-center mt-5" id="result-loading-data">
			@lang('label.retrive_data')...

			<div class="spinner-grow spinner-grow-sm" role="status">
			  <span class="sr-only">Loading...</span>
			</div>
		</div>

		<div class="row" id="results-data">
			
		</div>
	</div>
</div>

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
		showInstructor()
	})

	// Show Data
	function showInstructor(keyword = '') {
		// Initialize
		let url = $('#not-logged-in-instructor-list-url').val()

		if (keyword) {
			url = `${url}?q=${keyword}`
		}

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = instructorTemplate(data)

		    	$('#results-data').html(template)
				$('#load-more-course').attr('disabled', false)
				$('#result-loading-data').hide()
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function instructorTemplate(data) {
		// Initialize
		let template = ``

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				template += `<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-12 mb-3">
								<div class="card-container">
									<img class="avatar-preview" src="${val.avatar}" alt="user" />
									<h3 class="mt-2">
										${val.company_name}
									</h3>
									
									<h6>
										<a href="${baseUrl}/institution/${val.company_name}" title="" class="text-dark">
											${val.instructor_name}
										</a>
									</h6>
									
									<div class="clearifx mt-4">
										<span class=""><i class="fa fa-book text-color"></i> ${val.course_package} Kursus</span>
										<span class=""><i class="fa fa-users text-color ml-2"></i> ${val.student_joined} Murid</span>
									</div>

									<hr>
								</div>
							</div>`
			})
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
	$(document).on('keyup', '#search-instructor-input', function () {
		// Initialize
		let keyword = $('#search-instructor-input').val()
		
		setTimeout(function () {
			// Initialize
			let q = $('#search-instructor-input').val()

			if (keyword == q) {
				// Remove Value In Var
				nextPageUrl = ''

				// Call Function
				showInstructor(q)

				// Dom Manipulation
				$('#load-more-area').css('display', 'none')
				$('#result-loading-data').show()
				$('#results-data').html('')
			}
		}, 1000)
	})

	// Load More
	$(document).on('click', '#load-more-course', function () {
		// Call Function
		showInstructor(nextPageUrl)

		$(this).attr('disabled', true)
	})
</script>
@endpush