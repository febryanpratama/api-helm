@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/introjs.min.css" integrity="sha512-iaYE9B9u4GU8+KkRTOdRdZuzKdYw1X0hOAa4GwDV/uwdXgoX/ffT3ph1+HG1m4LPZD/HV+dkuHvWFLZtPviylQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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

	.pl-4-custom {
		padding-left: 1.7rem !important;
	}

	@media screen and (max-width: 576px) {
		.dropdown-menu-right-custom-first {
		    top: 8em !important;
		    left: -2em !important;
		}

		.dropdown-menu-right-custom-last {
			top: -190px !important;
		}

		.pb-sm-c-4 {
			padding-bottom: 1.5rem !important;
		}
	}

	@media screen and (max-width: 768px) {
		.dropdown-menu-right-custom-first {
			top: 8em !important;
			left: 4em !important;
		}

		.dropdown-menu-right-custom-last {
			top: -190px !important;
		}

		.pb-sm-c-4 {
			padding-bottom: 1.5rem !important;
		}
	}

	@media screen and (max-width: 992px) {
		.dropdown-menu-right-custom-first {
			top: 8em !important;
			left: -2em !important;
		}

		.dropdown-menu-right-custom-last {
			top: -190px !important;
		}
	}

	@media screen and (min-width: 1200px) and (max-width: 1500px) {
		.dropdown-menu-right-custom-first {
			top: 8px !important;
			left: -2em !important;
		}

		.dropdown-menu-right-custom-last {
			top: -190px !important;
		}
	}

	@media screen and (min-width: 1920px) and (max-width: 2000px) {
		.dropdown-menu-right-custom-first {
			top: -7em !important;
			left: -2em !important;
		}

		.dropdown-menu-right-custom-last {
			top: -190px !important;
		}
	}

	.customTooltip .introjs-tooltip-title {
		color: #62ddbd;
	}

	.progress-bar {
		background-color: #62ddbd !important;
	}
</style>
@endpush

@section('content')
{{-- Hidden Input --}}
<input type="hidden" id="class-list-url" value="{{ route('majors.index') }}">
<input type="hidden" id="class-store-url" value="{{ route('majors.store') }}">
<input type="hidden" id="theory-store-url" value="{{ route('subject.store') }}">
<input type="hidden" id="course-id" value="{{ $course->id }}">
<input type="hidden" id="reviews-list-url" value="{{ route('rating.index') }}">
<input type="hidden" id="count-data-url" value="{{ route('course.count.data') }}">
<input type="hidden" id="param" value="{{ request('tags') }}">
<input type="hidden" id="hint-widget-store-url" value="{{ route('hint.widget.store') }}">
<input type="hidden" id="val-session-and-theory-tab" value="{{ $sessionAndTheoryTab }}">
<input type="hidden" id="val-create-session-btn" value="{{ $addSessionBtn }}">
<input type="hidden" id="val-show-course-package-more-btn" value="{{ $moreBtn }}">
<input type="hidden" id="val-show-course-publish-btn" value="{{ $publishBtn }}">
<input type="hidden" id="val-show-course-package-details-theory" value="{{ $detailsTheory }}">
<input type="hidden" id="partner-store-url" value="{{ route('partner.store') }}">

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
								@if ($course->is_publish == 0)
									@if (count($course->majors) > 0 && $theory > 0)
										<button class="btn btn-primary btn-sm w-100 cursor-area intro-publish-course-package-new-account" id="publish-course" course-id="{{ $course->id }}">@lang('label.course_register')</button>
									@else
										<button class="btn btn-primary btn-sm w-100 cursor-area" disabled="" id="publish-course" course-id="{{ $course->id }}">@lang('label.course_register')</button>
									@endif
								@else
									<button class="btn btn-primary btn-sm w-100 cursor-area" id="unpublished" course-id="{{ $course->id }}">@lang('label.close_course_package')</button>
								@endif
							</div>

							{{-- Create Chat --}}
							@if (!$courseChat)
							<div class="float-right mr-2">
								<button class="btn btn-primary btn-sm w-100 cursor-area" id="create-new-group-chat" course-id="{{ $course->id }}">@lang('label.create_new_group_chat')</button>
							</div>
							@endif

							<div class="clearfix">
								<h5><b>{{ $course->name }}</b></h5>

								@if($course->course_type == 2)
									<span class="badge badge-success">@lang('label.free')</span>
								@else
									<span><b>Rp. {{ $course->price }}</b></span>
								@endif
							</div>

							<div class="clearfix mt-3">
								<div class="border-custom pt-1 float-left">
									<b><i class="fas fa-clipboard-list text-color"></i> <span id="total-session">{{ count($course->majors) }}</span> @lang('label.session')</b>
								</div>

								<div class="border-custom pt-1 float-left ml-2">
									<b><i class="fas fa-book text-color text-color"></i> <span id="total-theory">{{ $theory }}</span> @lang('label.theory')</b>
								</div>

								<div class="border-custom pt-1 float-left ml-2">
									<b class="p-4"><i class="fas fa-users text-color text-color"></i> {{ $totalStudent }} @lang('label.students_join')</b>
								</div>
							</div>

							<div class="clearfix mt-3">
								<div class="border-custom pt-1 float-left">
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
					      <a class="nav-link tab-link-course {{ (request('tags') == 'session-and-theory') ? '' : 'tab-active' }}" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview-tab" aria-selected="true"><b>Overview</b></a>
					  </li>
					  <li class="nav-item intro-show-course-package-new-account">
					      <a class="nav-link tab-link-course {{ (request('tags') == 'session-and-theory') ? 'tab-active' : '' }}" id="curriculum-tab" data-toggle="tab" href="#curriculum" role="tab" aria-controls="curriculum" aria-selected="false"><b>@lang('label.session_and_theory')</b></a>
					  </li>
					  <li class="nav-item">
					      <a class="nav-link tab-link-course" id="review-tab" data-toggle="tab" href="#review" role="tab" aria-controls="review" aria-selected="false"><b>@lang('label.reviews')</b></a>
					  </li>
					  @if($course->is_private == 1)
						  <li class="nav-item">
						      <a class="nav-link tab-link-course" id="partner-tab" data-toggle="tab" href="#partner" role="tab" aria-controls="partner" aria-selected="false"><b>@lang('label.partner')</b></a>
						  </li>
					  @endif
					</ul>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 mt-4 mb-5">
			<div class="card card-detail mt-2 mb-4">
				<div class="card-body">
					<div class="tab-content" id="myTabContent">
		              	<div class="tab-pane fade {{ (request('tags') && request('tags') == 'session-and-theory') ? '' : 'show active' }}" id="overview" role="tabpanel" aria-labelledby="overview-tab">
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

		              	<div class="tab-pane fade {{ (request('tags') && request('tags') == 'session-and-theory') ? 'show active' : '' }}" id="curriculum" role="tabpanel" aria-labelledby="curriculum-tab">
			              	<div class="clearfix">
			              		<div class="float-right add-class-btn">
		              				<button class="btn btn-sm btn-primary intro-create-session-new-account" id="add-class">@lang('label.add_session')</button>
		              			</div>

		              			<div class="float-left text-class">
		              				<h5 class="card-title"><b>@lang('label.session_and_theory')</b></h5>
		              			</div>
			              	</div>

			              	<div id="class-materi-results" class="mt-5">
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

						<div class="tab-pane fade" id="partner" role="tabpanel" aria-labelledby="partner-tab">
							<div class="clearfix">
								<div class="float-right">
									<button class="btn btn-sm btn-primary" id="add-partner">@lang('label.add_partner')</button>
								</div>
								<h5 class="card-title"><b>@lang('label.partner_list')</b></h5>

								<div class="table-responsive" id="partner-list">
									<div class="text-center mt-4 w-100" id="result-loading-partner-data">
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
@include('components.class-modal')
@include('components.theory-modal')
@include('components.partner-modal')
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdn.tiny.cloud/1/9r22aawjna4i5xiq305h1avqyndi0pzuxu0aysqdgkijvnwh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/intro.min.js" integrity="sha512-B0B1bdTGi9XHtoCINbtR6z9Vi8IYvvtImIE8KWMtqWAVUt2Wn2k69GxM7ya/3rQcz/Pgm/o1WcDU347/5k202A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Intro JS --}}
<script>
	$(document).ready(function () {
		if ($('#val-session-and-theory-tab').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					skipLabel: '',
					steps: [{
						title: 'Buat Sesi dan Materi',
					    element: document.querySelector('.intro-show-course-package-new-account'),
					    intro: 'Klik disini, untuk membuat Sesi dan Materi.'
					}]
				}).start()
			}, 2000)
		}
	})

	$(document).on('click', '#curriculum-tab', function () {
		if ($('#val-session-and-theory-tab').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					steps: [{
						title: 'Buat Sesi',
					    element: document.querySelector('.intro-create-session-new-account'),
					    intro: 'Yuk! buat Sesi pertamamu sekarang!'
					}]
				}).start()
			}, 2000)

			// Call Function
			insertIntroJs('session-and-theory-tab')
		}
	})

	function showIntroJsForMoreBtn () {
		if ($('#val-show-course-package-more-btn').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					skipLabel: '',
					steps: [{
						title: 'Tambah Materi, Tugas dan Meeting Room',
					    element: document.querySelector('.intro-more-btn-new-account'),
					    intro: 'Disini terdapat beberapa menu untuk kamu membuat Materi, Tugas, Meeting Room serta Edit dan Hapus Sesi.'
					}]
				}).start()
			}, 2000)
		}

		setTimeout(function () {
			$('#val-show-course-package-more-btn').val(1)
		}, 1000)
	}

	function showIntroJsForPublishCoursePackage () {
		setTimeout(function () {
			introJs().setOptions({
				tooltipClass: 'customTooltip',
				prevLabel: 'Sebelumnya',
				nextLabel: 'Selanjutnya',
				doneLabel: 'OK',
				skipLabel: '',
				steps: [{
					title: 'Daftarkan Paket Kursus',
				    element: document.querySelector('#publish-course'),
				    intro: 'Daftarkan Paket Kursus kamu, supaya bisa di lihat dan di beli oleh para murid.'
				}]
			}).start()
		}, 2000)
	}

	function showIntroForDetailsTheory () {
		if ($('#val-show-course-package-details-theory').val() == 0) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'Selesai',
					skipLabel: '',
					steps: [
					{
					 	title: 'Preview Materi',
					    element: document.querySelector('.intro-show-theory-new-account'),
					    intro: 'Kamu bisa melihat Preview Materi yang sudah di tambahkan disini'
					},
					{
					   title: 'Daftarkan Paket Kursus',
					   element: document.querySelector('#publish-course'),
					   intro: 'Daftarkan Paket Kursus kamu, supaya bisa di lihat dan di beli oleh para murid.'
					}]
				}).start();
			}, 2000)
		}
	}

	$(document).on('click', '#dropdown-action-list', function () {
		if ($('#val-show-course-package-more-btn').val() == 0) {
			insertIntroJs('show-course-package-more-btn')
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

{{-- Configs --}}
<script>
	$(document).on('click', '.tab-link-course', function () {
		$('.tab-link-course').removeClass('tab-active')
		$(this).addClass('tab-active')
	})

	// TinyMce
	tinymce.init({
        selector: '#class-details',
        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
        image_title: true,
        automatic_uploads: true,
        images_upload_url: `{{ route('tinymce.upload.image', ['_token' => csrf_token()]) }}`,
        file_picker_types: 'image',
        file_picker_callback: function(cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function() {
                var file = this.files[0];

                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function () {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
            };
            input.click();
        }
    })
</script>

{{-- Class --}}
<script>
	{{-- Global Var --}}
	let classId = []

	$(document).ready(function () {
		if ($('#param').val() && $('#param').val() == 'session-and-theory') {
			// Call Function
			showClassAsync()
			
			$('#curriculum-tab').click()
		}
	})

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

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
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
			              		<div class="float-right">
			              			<a href="javascript:void(0)" class="btn action-course btn-sm btn-dropdown-area intro-more-btn-new-account" id="dropdown-action-list" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			              				<i class="fa fa-ellipsis-v"></i>
			              			</a>

			              			<div class="dropdown-menu dropdown-menu-right ${dropdownMenuClass}" aria-labelledby="dropdown-action-list" style="right: 0px !important; width: 1%; top: -23em !important">
			              				<a class="dropdown-item edit-class" href="javascript:void(0)" id="${val.ID}">Edit Sesi</a>
			              			    <a class="dropdown-item text-danger delete-class" href="javascript:void(0)" id="${val.ID}" name="${val.Name}">Hapus Sesi</a>

			              			    <div class="dropdown-divider"></div>
			              				<a class="dropdown-item add-theory" href="javascript:void(0)" id="${val.ID}">Tambah Materi</a>
			              				<a class="dropdown-item" href="${baseUrl}/task/create?majorId=${val.ID}">Tambah Tugas</a>

			              				<div class="dropdown-divider"></div>
			              				<a class="dropdown-item" href="${baseUrl}/meeting-room/create/${val.ID}" id="${val.ID}">Meeting Room</a>
			              			</div>
			              		</div>

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

			// ${baseUrl}/meet/${val.Name}-${meetId}
			// <a class="dropdown-item" href="javascript:void(0)" id="${val.ID}">Tambah Ujian</a>
		} else {
			template += `<div class="alert alert-info text-center mt-4">
				<i class="fas fa-info-circle"></i> @lang('label.no_session_yet')
			</div>`
		}

		return template
	}

	$(document).on('click', '#add-class', function (e) {
		e.preventDefault()

		$('#class-modal-aria').html("@lang('label.add_session')")
		$('#class-action').val('add')
		$('#class-btn-loading').html(`@lang('label.add')`)
	    $('#class-modal').modal({backdrop: 'static', keyboard: false})

		if ($('#val-create-session-btn').val() == 0) {
		    // Call Function
			insertIntroJs('create-session-btn')
		}
	})

	// Store
	$(document).on('submit', '#class-modal form', function (e) {
		e.preventDefault()

		// Initialize
		let url 		= ``
		let action 		= $('#class-action').val()
		let className 	= $('#class-name').val()
		let classDetail	= $('#class-details').val()

		if (action == 'add') {
			url = $('#class-store-url').val()
		} else {
			let id  = $('#class-id').val()
			url = `${baseUrl}/majors/update/${id}`
		}

		// Validate
		if (!className) {
			toastr.error(`Nama Sesi harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!classDetail) {
			toastr.error(`Detail Sesi harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$('#class-btn-loading').attr('disabled', true)

		// Initialize
		let fd = new FormData(this)
		fd.append('course_id', $('#course-id').val())

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
		    	$('#class-modal').modal('hide')
		    	$('#class-modal form')[0].reset()

		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        // Disabled Button False
		        $('#class-btn-loading').attr('disabled', false)

		        // Call Function
		        showClassAsync()
		        showCountSessionTheory()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#class-btn-loading').attr('disabled', false)

		        toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	// Edit
	$(document).on('click', '.edit-class', function (e) {
		e.preventDefault()

		$('#class-action').val('edit')
		$('#class-modal-aria').html('Edit Sesi')
		$('#class-btn-loading').html(`@lang('label.edit')`)

	    // Call Function
		editClass($(this).attr('id'))
	})

	function editClass(classId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/majors/edit/${classId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Append Value
		    	$('#class-id').val(data.data.ID)
		    	$('#class-name').val(data.data.Name)

		    	// tinyMCE
		    	tinyMCE.get('class-details').setContent(data.data.Details)

	    		$('#class-modal').modal({backdrop: 'static', keyboard: false})
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				$(`#${classId}`).attr('disabled', false)
		    }
		})
	}

	// Validate Destroy Class
	$(document).on('click', '.delete-class', function (e) {
	    e.preventDefault()

	    // Initialize
	    let classId   = $(this).attr('id')
	    let className = $(this).attr('name')

	    // Validate
	    Swal.fire({
	        text: `Hapus Sesi ${className}?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyClass(classId)
	      }
	    })
	})

	// Destroy Class
	function destroyClass (classId) {
	    $.ajax({
	        url: `${baseUrl}/majors/delete/${classId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        	return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#card-majors-area-${classId}`).remove()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}

	function showCountSessionTheory() {
		// Initialize
		let url 	 = $('#count-data-url').val()
		let courseId = $('#course-id').val()

		$.ajax({
		    url: `${url}?courseId=${courseId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	$('#total-session').html(data.data.session)
		    	$('#total-theory').html(data.data.theory)

		    	// Call Function
		    	showIntroJsForMoreBtn()

		    	if (data.data.session > 0 && data.data.theory > 0) {
		    		$('#publish-course').attr('disabled', false)

		    		if ($('#val-show-course-publish-btn').val() == 0) {
			    		// Call Function
			    		// showIntroJsForPublishCoursePackage('publish-course')
		    		}
		    	}
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`500 Internal Server Error`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}
</script>

{{-- Theory --}}
<script>
	$(document).on('click', '.add-theory', function (e) {
		e.preventDefault()

		$('#theory-modal-aria').html('Tambah Materi')
		$('#theory-action').val('add')
		$('#majors-id').val($(this).attr('id'))
		$('#theory-btn-loading').html(`@lang('label.add')`)
	    $('#theory-modal form')[0].reset()
	    $('#span-name-file-theory').html('<i>*Tidak ada file yang dipilih</i>')

	    $('#theory-modal').modal('show')
	    // $('#theory-modal').modal({backdrop: 'static', keyboard: false})
	})

	// Trigger Input Type File
	$(document).on('click', '#theory-btn-file', function () {
	    $('#file-theory').click()
	})

	$(document).on('click', '#theory-thumbnail-btn-file', function () {
	    $('#file-theory-thumbnail').click()
	})

	// Get Full Path
	$(document).on('change', '#file-theory', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-theory').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-theory').html('<i>*Tidak ada file yang dipilih</i>')
	    }
	})

	$(document).on('change', '#file-theory-thumbnail', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-theory-thumbnail').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-theory-thumbnail').html('<i>*Tidak ada file yang dipilih</i>')
	    }
	})

	// Store
	$(document).on('submit', '#theory-modal form', function (e) {
	    e.preventDefault()

	    // Initialize
	    let url 		= $('#theory-store-url').val()
	    let theoryName 	= $('#theory-name').val()
	    let fileTheory 	= $('#file-theory')[0].files
	    let actionForm  = $('#theory-action').val()

	    // Validate
	    if (!theoryName) {
	    	toastr.error(`Nama Materi harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (actionForm != 'edit') {
		    if (fileTheory.length == 0) {
		    	toastr.error(`File Materi harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		    	return 0
		    }
	    }

	    // Disabled Button True
	    $('#theory-btn-loading').attr('disabled', true)

	    // Check Action
	    if (actionForm == 'edit') {
	    	url = `${baseUrl}/subject/update/${$('#theory-id').val()}`
	    }

	    $.ajax({
	    	xhr: function() {
	    		// Initialize
                let xhr = new window.XMLHttpRequest()

                // Dom Manipulation
                $('#progress-bar-uploaded-area').css('display', '')
                
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                    	// Initialize
                        let percentComplete = ((evt.loaded / evt.total) * 100);

                        $('#progress-bar-uploaded').width((percentComplete).toFixed() + '%');
                        $('#progress-bar-uploaded').html(`@lang('label.upload_process') ${(percentComplete).toFixed()}%`);
                    }
                }, false);

                return xhr;
            },
	        url: `${url}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        data: new FormData(this),
	        contentType: false,
	        cache: false,
	        processData: false,
	        dataType: 'json',
	        success: data => {
	        	// Dom Manipulation
	        	$('#theory-btn-loading').attr('disabled', false)
	        	$('#progress-bar-uploaded-area').css('display', 'none')

	        	if (data.status == false) {
	    			toastr.error(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    			return 0
	        	}

	        	$('#span-name-file-theory').html(`<i>*Tidak ada file yang dipilih</i>`)
	        	$('#span-name-file-theory-thumbnail').html(`<i>*Tidak ada file yang dipilih</i>`)

	    		// Call Function
	    		showTheory($('#majors-id').val())

	    		$('#theory-modal').modal('hide')
	    		$('#theory-modal form')[0].reset()

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		// Call Function
	    		showCountSessionTheory()
	    		insertIntroJs('show-course-package-details-theory')
	        },
	        error: e => {
	            console.log(e)

	            // Disabled Button False
	            $('#theory-btn-loading').attr('disabled', false)

	            // Check Action
	            if ($('#action-form').val() == 'edit') {
	            	toastr.error(`Data gagal diperbaharui`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            } else {
	            	toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            }
	        }
	    })
	})

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

		        toastr.error(`Data Materi gagal dimuat`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function theoryTemplate(data, majorId) {
		let template = `<h5 class="pl-4-custom"><b>Materi</b></h5>
						<div class="row justify-content-center">
						`

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let classBgColor = ``
				let iconType 	 = `<i class="fas fa-play-circle text-color ml-2 mr-2 fa-lg"></i>`
				
				// Condition
				if ((key % 2) == 0) {
					classBgColor = 'theory-1'
				}

				if (val.FileType == 1) {
					iconType = `<i class="fas fa-file text-color ml-2 mr-2"></i>`
				}
				
				// <span class="badge" style="background-color: #dfe8f2;">03:20</span>

				template += `<div class="col-11 p-2 ${classBgColor}" id="theory-result-element-${val.ID}">
              					<div class="float-right">
              						<a href="javascript:void(0)" class="text-info edit-theory" id="${val.ID}" major-id="${val.MajorId}"><i class="fas fa-edit"></i></a>
              						<a href="javascript:void(0)" class="text-danger delete-theory" id="${val.ID}" name="${val.Name}"><i class="fas fa-trash"></i></a>
              					</div>

              					<a href="${baseUrl}/course/preview/${val.slug}/overview/${val.ID}" title="" class="text-dark intro-show-theory-new-account">
              						${iconType} ${val.Name}
              					</a>
	              			</div>`
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

			// Call Function
			showIntroForDetailsTheory()
		} else {
			template = ``
		}

		return template
	}

	// Edit
	$(document).on('click', '.edit-theory', function (e) {
		e.preventDefault()

		$('#theory-action').val('edit')
		$('#theory-modal-aria').html('Edit Materi')
		$('#theory-btn-loading').html(`@lang('label.edit')`)
		$('#theory-modal form')[0].reset()

	    // Call Function
		editTheory($(this).attr('id'), $(this).attr('major-id'))
	})

	function editTheory(classId, majorId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/subject/edit/${classId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Append Value
		    	$('#theory-id').val(data.data.ID)
		    	$('#theory-name').val(data.data.Name)
		    	$('#majors-id').val(majorId)
		    	$('#span-name-file-theory').html(data.data.Path)

	    		$('#theory-modal').modal('show')
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				$(`#${classId}`).attr('disabled', false)
		    }
		})
	}

	// Validate Destroy Theory
	$(document).on('click', '.delete-theory', function (e) {
	    e.preventDefault()

	    // Initialize
	    let theoryId   = $(this).attr('id')
	    let theoryName = $(this).attr('name')

	    // Validate
	    Swal.fire({
	        text: `Hapus Materi ${theoryName}?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyTheory(theoryId)
	      }
	    })
	})

	// Destroy Theory
	function destroyTheory (theoryId) {
	    $.ajax({
	        url: `${baseUrl}/subject/delete/${theoryId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        	return 0
	        	}
	        	        	
	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#theory-result-element-${theoryId}`).remove()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
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

		        toastr.error(`Data Tugas gagal dimuat`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
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
              					<div class="float-right">
              						<a href="${baseUrl}/task/edit/${val.id}" class="text-info edit-task" id="${val.id}"><i class="fas fa-edit"></i></a>
              						<a href="javascript:void(0)" class="text-danger delete-task" id="${val.id}" name="${val.name}" major-id="${majorId}"><i class="fas fa-trash"></i></a>
              					</div>

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

	// Validate Destroy Task
	$(document).on('click', '.delete-task', function (e) {
	    e.preventDefault()

	    // Initialize
	    let taskId   = $(this).attr('id')
	    let taskName = $(this).attr('name')
	    let majorId  = $(this).attr('major-id')

	    // Validate
	    Swal.fire({
	        text: `Hapus Tugas ${taskName}?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyTask(taskId, majorId)
	      }
	    })
	})

	// Destroy Task
	function destroyTask (taskId, majorId) {
	    $.ajax({
	        url: `${baseUrl}/task/delete/${taskId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        	return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		// Call Function
	    		showTask(majorId)
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>

{{-- Reviews --}}
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

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
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
			template += `
				<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-12 text-center">
					<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> @lang('label.no_reviews_yet')</div>
				</div>
			`
		}

		return template
	}
</script>

{{-- Publish/Unpublished --}}
<script>
	$(document).on('click', '#publish-course', function (e) {
		// Disabled Button True
		$(this).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/course/publish/${$(this).attr('course-id')}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	status: 1
		    },
		    success: data => {
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	location.reload()
		        }, 2000)

		        // Call Function
		        insertIntroJs('show-course-publish-btn')
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Kursus gagal dipublish`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	$(document).on('click', '#unpublished', function (e) {
		// Disabled Button True
		$(this).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/course/publish/${$(this).attr('course-id')}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	status: 0
		    },
		    success: data => {
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	location.reload()
		        }, 2000)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Kursus gagal dipublish`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
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
              					<div class="float-right">
              						<a href="${baseUrl}/meeting-room/edit/${val.id}" class="text-info" id="${val.id}" major-id="${val.session_id}"><i class="fas fa-edit"></i></a>
              						<a href="javascript:void(0)" class="text-danger delete-meeting-room" id="${val.id}" name="${val.name}"><i class="fas fa-trash"></i></a>
              					</div>

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

	// Validate Destroy Task
	$(document).on('click', '.delete-meeting-room', function (e) {
	    e.preventDefault()

	    // Initialize
	    let meetId   = $(this).attr('id')
	    let meetName = $(this).attr('name')

	    // Validate
	    Swal.fire({
	        text: `Hapus Meeting Room ${meetName}?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyMeetingRoom(meetId)
	      }
	    })
	})

	// Destroy Task
	function destroyMeetingRoom (meetId) {
	    $.ajax({
	        url: `${baseUrl}/meeting-room/delete/${meetId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        	return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#meeting-room-result-element-${meetId}`).remove()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>

{{-- Create New Group Chat --}}
<script>
	$(document).on('click', '#create-new-group-chat', function (e) {
		// Disabled Button True
		$(this).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/course/new-group-chat/${$(this).attr('course-id')}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	window.location = `${baseUrl}/chat`
		        }, 2000)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        // Disabled Button False
		        $(this).attr('disabled', false)
		    }
		})
	})
</script>

{{-- Partner --}}
<script>
	$(document).on('click', '#partner-tab', function () {
		// Check Attr
		if ($(this).attr('click')) {
			return 0
		}
		
		$(this).attr('click', true)

		// Initialze
		let courseId = $('#course-id').val()

		// Call Function
		partners(courseId)
	})

	$(document).on('click', '#add-partner', function (e) {
		e.preventDefault()

		$('#partner-modal-aria').html('Tambah Mitra')
		$('#partner-modal').modal('show')
		$('#partner-modal form')[0].reset()
		$('#partner-action').val('add')
    	$('#pic-email').attr('readonly', false)
    	$('#partner-btn-loading').html('Tambah')
	})

	// Store
	$(document).on('submit', '#partner-modal form', function (e) {
		e.preventDefault()

		// Initialize
		let url 		= ``
		let action 		= $('#partner-action').val()
		let partnerName = $('#partner-name').val()
		let pic			= $('#pic-name').val()
		let picEmail  	= $('#pic-email').val()
		let picPhone  	= $('#pic-phone').val()

		if (action == 'add') {
			url = $('#partner-store-url').val()
		} else {
			let id = $('#partner-id').val()
			url    = `${baseUrl}/partner/update/${id}`
		}

		// Validate
		if (!partnerName) {
			toastr.error(`Nama Mitra harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!pic) {
			toastr.error(`Nama PIC harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!picEmail) {
			toastr.error(`Email PIC harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!picPhone) {
			toastr.error(`Nomor Telepon PIC harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		// Disabled Button True
		$('#partner-btn-loading').attr('disabled', true)

		// Initialize
		let fd = new FormData(this)
		fd.append('course_id', $('#course-id').val())

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
		    	// Disabled Button False
		    	$('#partner-btn-loading').attr('disabled', false)

		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
		    	}

		    	$('#partner-modal').modal('hide')
		    	$('#partner-modal form')[0].reset()

		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        // Call Function
		        partners($('#course-id').val())
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $('#partner-btn-loading').attr('disabled', false)

		        toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	function partners (courseId) {
		$.ajax({
		    url: `${baseUrl}/partner?courseId=${courseId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = partnersTemplate(data)

		    	$(`#partner-list`).html(template)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	function partnersTemplate(data) {
		// Initialize
		let template = `<table class="table table-borderless">
						  <thead>
						    <tr>
						      <th scope="col">No</th>
						      <th scope="col">Nama Mitra</th>
						      <th scope="col">PIC</th>
						      <th scope="col">Email PIC</th>
						      <th scope="col">Nomor Telepon PIC</th>
						      <th scope="col">Kirim Invoice</th>
						      <th scope="col">Opsi</th>
						    </tr>
						  </thead>
						  <tbody>`

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let disabled = ''

				if (val.email_course > 0) {
					disabled = `disabled="disabled"`
				}

				template += `<tr id="partner-area-${val.id}">
								<td>${key + 1}</td>
								<td><a href="${baseUrl}/partner/list/users/${val.id}/${val.course_id}" class="text-color">${val.name}</a></td>
								<td>${val.pic}</td>
								<td>${val.useremail}</td>
								<td>${val.phone}</td>
								<td>
									<button class="btn btn-sm btn-primary send-invoice" id="partner-id-${val.id}" partner-id="${val.id}" course-id="${val.course_id}" ${disabled}>Kirim Invoice</button>
								</td>
								<td>
								    <a href="javascript:void(0)" class="text-info edit-partner" id="${val.id}"><i class="fas fa-edit"></i></a>
								    <a href="javascript:void(0)" class="text-danger delete-partner" id="${val.id}" name="${val.name}"><i class="fas fa-trash"></i></a>
								</td>
							</tr>`
			})
		} else {
			template += `<tr>
							<td colspan="6" class="text-center">Tidak ada data</td>
						</tr>`
		}

		template += `</tbody></table>`

		return template
	}

	// Edit
	$(document).on('click', '.edit-partner', function (e) {
		e.preventDefault()
		
		$('#partner-modal form')[0].reset()
		$('#partner-action').val('edit')

		// Initialize
		let partnerId = $(this).attr('id')

		// Call Function
		editPartner(partnerId)
	})

	function editPartner(partnerId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/partner/edit/${partnerId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Append Value
		    	$('#partner-id').val(data.data.id)
		    	$('#partner-name').val(data.data.name)
		    	$('#pic-name').val(data.data.pic)
		    	$('#pic-email').val(data.data.user.email)
		    	$('#pic-phone').val(data.data.phone)

		    	$('#pic-email').attr('readonly', true)
		    	
		    	$('#partner-btn-loading').html('Edit')
		    	$('#partner-modal-aria').html('Edit Mitra')
		    	$('#partner-modal').modal('show')
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`${e.statusText}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				$(`#${classId}`).attr('disabled', false)
		    }
		})
	}

	// Validate Destroy Course
	$(document).on('click', '.delete-partner', function (e) {
	    e.preventDefault()

	    // Initialize
	    let partnerId   = $(this).attr('id')
	    let partnerName = $(this).attr('name')

	    // Validate
	    Swal.fire({
	    	title: `Hapus Mitra ${partnerName}?`,
	        text: `Data Mitra dan seluruh peserta dari Mitra ${partnerName} akan dihapus, termasuk dari Pesan Grup.`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyPartner(partnerId)
	      }
	    })
	})

	// Destroy Course
	function destroyPartner (partnerId) {
		// Initialize
		let courseId = $('#course-id').val()
		
	    $.ajax({
	        url: `${baseUrl}/partner/delete/${partnerId}/${courseId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#partner-area-${partnerId}`).remove()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}
</script>

{{-- Send Invoice --}}
<script>
	$(document).on('click', '.send-invoice', function () {
		// Initialize
		let id 		 = $(this).attr('partner-id')
		let courseId = $(this).attr('course-id')

	    $(`#partner-id-${id}`).attr('disabled', true)

	    $.ajax({
	        url: `${baseUrl}/partner/send-invoice/${id}/${courseId}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		    		$(`#partner-id-${id}`).attr('disabled', false)

			        return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#partner-id-${id}`).attr('disabled', true)
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`${e.responseJSON.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    	
		    	$(`#partner-id-${id}`).attr('disabled', false)
	            
	            return 0
	        }
	    })
	})
</script>
@endpush