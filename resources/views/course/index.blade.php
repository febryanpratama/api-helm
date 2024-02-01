@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
<input type="hidden" id="category-list-url" value="{{ route('category.index') }}">
<input type="hidden" id="course-list-url" value="{{ route('course.index') }}">
<input type="hidden" id="course-store-url" value="{{ route('course.store') }}">
<input type="hidden" id="company-id" value="{{ auth()->user()->company->ID }}">
<input type="hidden" id="company-name" value="{{ Str::slug(auth()->user()->company->Name) }}">
<input type="hidden" id="phone-company-private" value="{{ auth()->user()->company->Phone }}">
<input type="hidden" id="email-company-private" value="{{ auth()->user()->company->Email }}">
<input type="hidden" id="logo-company-private" value="{{ auth()->user()->company->Logo }}">
<input type="hidden" id="create-course-package" value="{{ ($createCoursePackage) ? true : '' }}">
<input type="hidden" id="hint-widget-store-url" value="{{ route('hint.widget.store') }}">

@php
	// Initialize
	$sosmedExists = false;

	if (auth()->user()->company->facebook || auth()->user()->company->instagram || auth()->user()->company->youtube || auth()->user()->company->linkedin) {
		$sosmedExists = true;
	}
@endphp

<input type="hidden" id="sosmed-exists" value="{{ $sosmedExists }}">

<div class="container">
	<section class="mb-5">
		<div class="clearfix bg-white">
			<div class="row">
				<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
					<div class="border-left-element">
						<h5 class="pt-2">@lang('label.course_package')</h5>
						<span class="text-title" id="sub-text-course-element">Filter : @lang('label.all_course_package')</span>
					</div>
				</div>
				
				<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
					@if (auth()->user()->company->Phone != '-' && auth()->user()->company->Email != '-')
						<div class="float-right" id="search-course">
							<div class="row">
								<div class="col-sm-12 col-md-9 col-lg-9 col-xl-9 col-12">
							   		<div class="float-left mt-3 pl-2 pr-2">
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

								<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12">
									<div class="float-left mr-1 mt-3">
										<button class="btn btn-sm btn-primary btn-outline" id="dropdown-filter-course" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="fas fa-filter"></i>
										</button>

										<div class="dropdown-menu dropdown-menu-right mt-2" aria-labelledby="dropdown-filter-course" style="right: 0px !important; width: 14em !important;">
										    <a class="dropdown-item filter-course" href="javascript:void(0)" id="all-course">@lang('label.all_course_package')</a>
										    <a class="dropdown-item filter-course" href="javascript:void(0)" id="course-publish">@lang('label.publish_course')</a>
										    <a class="dropdown-item filter-course" href="javascript:void(0)" id="course-draft">@lang('label.course_package_not_register')</a>
										    <hr>
										    <a class="dropdown-item filter-course" href="javascript:void(0)" id="paid-course">@lang('label.paid_course_package')</a>
										    <a class="dropdown-item filter-course" href="javascript:void(0)" id="free-course">@lang('label.free_course_package')</a>
										</div>
									</div>

									<div class="float-left mt-3 intro-add-course-package">
										<button class="btn btn-sm btn-primary btn-outline" id="add-course">
											<i class="fas fa-plus"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					@endif
				</div>
			</div>
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
@include('components.course-modal')
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
<script src="https://cdn.tiny.cloud/1/9r22aawjna4i5xiq305h1avqyndi0pzuxu0aysqdgkijvnwh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/intro.min.js" integrity="sha512-B0B1bdTGi9XHtoCINbtR6z9Vi8IYvvtImIE8KWMtqWAVUt2Wn2k69GxM7ya/3rQcz/Pgm/o1WcDU347/5k202A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Tour Widget --}}
<script>
	$(document).ready(function () {
		// Initialize
		let createCoursePackage = $('#create-course-package').val()
		
		if (!createCoursePackage) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					steps: [{
						title: '',
					    element: document.querySelector('.intro-add-course-package'),
					    intro: 'Klik Disini untuk membuat Paket Kursus.'
					}]
				}).start()
			}, 2000)
		}
	})

	$(document).on('click', '.introjs-donebutton', function (e) {
		// Initialize
		let url = $('#hint-widget-store-url').val()

	    $.ajax({
	        url: `${url}`,
	        type: 'POST',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {},
	        error: e => {
	            console.log(e)
	        }
	    })
	})
</script>

{{-- Configs --}}
<script>
	// TinyMce
	tinymce.init({
        selector: '#course-description',
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

{{-- Is Private or Public --}}
<script>
	$(document).on('change', '#is-private', function () {
		if (this.value == 1) {
			$('.is_private').css('display', '')
			$('.is_public').css('display', 'none')
			$('#course-price-area').css('display', '')
		} else {
			$('.is_private').css('display', 'none')
			$('.is_public').css('display', '')
			$('.commission_type_1').css('display', 'none')
		}
	})

	$(document).on('change', '#commission-type', function () {
		if (this.value == 1) {
			$('.commission_type_1').css('display', '')
		} else {
			$('.commission_type_1').css('display', 'none')
		}
	})
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
		let url = $('#course-list-url').val()

		if (nextPageUrlParam) {
			url = nextPageUrlParam
		}

		// Filter By is_admin_confirm Or is_publish
		if (filter) {
			endPoint = $('#course-list-url').val()
			url = `${endPoint}?filter=${filter}`
		}

		// Search Course
		if (keyword) {
			url = `${url}?search=${keyword}`
		}

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = courseTemplate(data)

		    	if (action == 'add' || action == 'edit') {
			    	$('#course-list-results').html(template)
		    	} else if (nextPageUrlParam) {
			    	$('#course-list-results').append(template)
		    	} else {
			    	$('#course-list-results').html(template)
		    	}

		    	// Next Page
		    	nextPageUrl = data.data.next_page_url

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
		let template  = ``
		let companyId = $('#company-id').val()
		let companyNm = $('#company-name').val()

		if ((data.data).length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let isPublish 		= `<span class="badge badge-warning p-2">Draft</span>`
				let categoryType 	= ``
				let priceVal 		= ``

				if (val.is_publish == '1') {
					isPublish = `<span class="badge badge-success p-2">Publish</span>`
				}

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
					priceVal = `Rp. ${val.price}`
				}

				template += `<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12 mb-4" id="card-course-area-${val.id}">
								<input type="hidden" id="link-${val.id}" name="link-course-package" value="${baseUrl}/student/course/show/member/${val.slug}">
								
								<div class="card card-profile h-100">
									<div class="float-left course-is-publish ml-2 mt-2">
										${isPublish} ${categoryType}
									</div>

									<div class="float-right action-course-element">
										<a href="javascript:void(0)" class="btn action-course btn-sm btn-dropdown-area" id="dropdown-action-list" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="fa fa-ellipsis-v"></i>
										</a>

										<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-action-list" style="right: 0px !important; width: 1%;">
										    <a class="dropdown-item" href="${baseUrl}/course/show/${val.id}">Detail</a>
											<a class="dropdown-item edit-course" href="javascript:void(0)" id="${val.id}">Edit</a>
										    <a class="dropdown-item text-danger delete-course" href="javascript:void(0)" id="${val.id}" name="${val.name}">Hapus</a>
										    <a class="dropdown-item copy-link" href="javascript:void(0)" id="${val.id}">Copy Link</a>
										</div>
									</div>

									<a href="${baseUrl}/course/show/${val.id}">
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
		} else {
			if (!$('#sosmed-exists').val()) {
				template += `<div class="col-12">
								<div class="alert alert-info text-center m-auto">
									<i class="fa fa-info-circle"></i> Silakan <a href="${baseUrl}/${companyNm}/company/edit?company=${companyId}">Lengkapi Profil Lembaga Kursus dan Data Anda</a> Agar Bisa Membuat Paket-Paket Kursus
								</div>
							</div>`
			} else {
				template += `<div class="col-12">
								<div class="alert alert-info text-center m-auto">
									<i class="fa fa-info-circle"></i> Segera Buat Paket-Paket Kursus Klik Tombol di Kanan Atas
								</div>
							</div>`
			}

		}

		return template
	}

	$(document).on('click', '#add-course', function () {
		$('#course-modal-aria').html("@lang('label.add_course_package')")
		$('#action-form').val('add')
		$('#course-modal form')[0].reset()

		// Call Function
		showCategory()

		$('#course-modal').modal('show')
	    // $('#course-modal').modal({backdrop: 'static', keyboard: false})
	})

	function showCategory(courseId = '') {
		// Initialize
		let url = $('#category-list-url').val()

		if (courseId) {
			url = `${url}?course-id=${courseId}`
		}

	    $.ajax({
	        url: `${url}`,
	        type: 'GET',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	// Initialize
	        	let html = ``

	        	$.each(data.data.category, function (key, val) {
	        		html += `<option value="${val.id}" ${((data.data.courseCategory).includes(val.id)) ? 'selected' : ''}>${val.name}</option>`
	        	})

	        	$('#category-id').html(html)

	        	// Call Function
	        	configSelect2()
	        },
	        error: e => {
	            console.log(e)

		    	toastr.error(`Kategori gagal didapatkan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	        }
	    })
	}

	function configSelect2() {
		$('#category-id').select2({
			dropdownParent: $('#course-modal .modal-content')
		})
	}

	$(document).on('change', '#periode-type', function () {
		// Initialize
		const text = $('#periode-type option:selected').text()

		// if (this.value != 0) {
		// 	$('#detail-periode').html(`(${text})`)
		// } else {
		// 	$('#detail-periode').html('')
		// }
	})

	// Course Type
	$(document).on('change', '#course-type', function () {
		if (this.value == 2) {
			$('#course-price-area').css('display', 'none')
		} else {
			$('#course-price-area').css('display', '')
		}
	})

	// Trigger Input Type File
	$(document).on('click', '#course-btn-file', function () {
	    $('#file-course').click()
	})

	// Get Full Path
	$(document).on('change', '#file-course', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-course').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-course').html('<i>*Tidak ada file yang dipilih</i>')
	    }
	})

	$(document).on('keyup', '#course-price', function () {
		// Initialize
	    let currentValue  = $("#course-price").val()
	    let html 		  = `* Komisi RuangAjar`
	    let html2 		  = `* Nominal pendapatan`
	    let cType 		  = $('#commission-type option:selected').val()
	    let currentValNum = currentValue.replace('.','')
	    let originalVal   = this.value
        let isPrivate 	  = $('#is-private option:selected').val()

	    $(this).val(formatRupiah(currentValue, 'Rp.'))

	    // Formula Commision
	    let commission  = 5 // %
	    let total 		= (commission / 100) * (currentValNum.replace('.',''))
	    totalFinal		= (currentValNum.replace('.','')) - total

        if (isPrivate == 1) {
        	if (cType == 0) {
	        	// Initialize
	    		html  = `* Komisi RuangAjar Per Peserta`
	    		html2 = `* Nominal pendapatan Per Peserta`

	    		$('#nominal-accepted').html(`${html} <b class="text-info">Rp.${formatRupiah((Math.floor(total)).toString(), 'Rp.')}</b> <br> ${html2} <b class="text-info">Rp.${formatRupiah(totalFinal.toString(), 'Rp.')}</b>`)
        	} else {
        		html  = `* Komisi akan dihitung dari banyaknya murid bergabung`
        		html2 = ``

        		$('#nominal-accepted').html(`${html}`)
        	}
        } else {
        	$('#nominal-accepted').html(`${html} <b class="text-info">Rp.${formatRupiah((Math.floor(total)).toString(), 'Rp.')}</b> <br> ${html2} <b class="text-info">Rp.${formatRupiah(totalFinal.toString(), 'Rp.')}</b>`)
        }
	})

	// Store
	$(document).on('submit', '#course-modal form', function (e) {
	    e.preventDefault()

	    // Initialize
	    let url 		 		= $('#course-store-url').val()
	    let courseName 	 		= $('#course-name').val()
	    let courseDescription 	= $('#course-description').val()
	    let periodeType  		= $('#periode-type option:selected').val()
	    let periode 	 		= $('#course-periode').val()
	    let courseType 	 		= $('#course-type option:selected').val()
	    let price 	 	 		= $('#course-price').val()
	    let isPrivate 			= $('#is-private option:selected').val()
	    let coursePackageC 		= $('#course-package-category option:selected').val()

	    // Validate
	    if (!courseName) {
	    	toastr.error(`Nama Paket Kursus harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (!courseDescription) {
	    	toastr.error(`Deskripsi harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (!periode) {
	    	toastr.error(`Masa Berlaku Kursus harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (periodeType == 0) {
	    	toastr.error(`Periode harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    if (isPrivate == 1) {
	    	// Initialize
	    	let commissionType = $('#commission-type option:selected').val()

	    	if (commissionType == 1) {
	    		// Initialize
	    		let minUserJoined 	= $('#min-user-joined').val()
	    		let cMinUserJoined 	= $('#commission-min-user-joined').val()
	    		let maxUserJoined 	= $('#commission-max-user-joined').val()
	    		let cMaxUserJoined 	= $('#commission-max-user-joined').val()

	    		if (!minUserJoined) {
			    	toastr.error(`Minimal Peserta Bergabung harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	    		
	    			return 0
	    		}

	    		if (!cMinUserJoined) {
			    	toastr.error(`Komisi Minimal Peserta Bergabung harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	    		
	    			return 0
	    		}

	    		if (!maxUserJoined) {
			    	toastr.error(`Maksimal Peserta Bergabung harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	    		
	    			return 0
	    		}

	    		if (!cMaxUserJoined) {
			    	toastr.error(`Komisi Maksimal Peserta Bergabung harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	    		
	    			return 0
	    		}
	    	}

    		if (!price) {
		    	toastr.error(`Harga harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
    		
    			return 0
			}
	    } else {
			if (courseType == 1) {
	    		if (!price) {
			    	toastr.error(`Harga harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	    		
	    			return 0
				}
		    }
	    }

	    if (!coursePackageC) {
	    	toastr.error(`Tipe Kategori Paket Kursus harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    	return 0
	    }

	    // Disabled Button True
	    $('#course-btn-loading').attr('disabled', true)

	    // Check Action
	    if ($('#action-form').val() == 'edit') {
	    	url = `${baseUrl}/course/update/${$('#course-id-form').val()}`
	    }

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
	        	// Disabled Button False
	        	$('#course-btn-loading').attr('disabled', false)
	        	
	        	if (!data.status) {
		    		toastr.error(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		    		return 0
	        	}

	           	$('#course-modal').modal('hide')
	           	$('#course-modal form')[0].reset()
	           	$('#span-name-file-course').html(`<i>*Tidak ada file yang dipilih</i>`)
				$('#course-price-area').css('display', '')

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		// Check Action
	    		if ($('#action-form').val() == 'add') {
		    		location.replace(`${baseUrl}/course/show/${data.course_id}`)
	    		} else {
		    		// Call Function
		    		showCourse('','edit')
	    		}
	        },
	        error: e => {
	            console.log(e)

	            // Disabled Button False
	            $('#course-btn-loading').attr('disabled', false)

	            // Check Action
	            if ($('#action-form').val() == 'edit') {
	            	toastr.error(`Data gagal diperbaharui`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            } else {
	            	toastr.error(`Data gagal disimpan`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            }
	        }
	    })
	})

	// Edit
	$(document).on('click', '.edit-course', function (e) {
		e.preventDefault()

		$('#course-modal-aria').html("@lang('label.edit_course_package')")
		$('#course-id-form').val($(this).attr('id'))
		$('#action-form').val('edit')
		$('#course-btn-loading').html(`@lang('label.edit')`)

		// Call Function
		editCourse($(this).attr('id'))
		showCategory($(this).attr('id'))
	})

	function editCourse(courseId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/course/edit/${courseId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (data.status == false) {
		        	toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		    		return 0
		    	}

		    	// Delete Attr
		    	$('#periode-type option').removeAttr('selected')
		    	$('#course-type option').removeAttr('selected')
		    	$('#is-private option').removeAttr('selected')
		    	$('#commission-type option').removeAttr('selected')
		    	$('#course-package-category option').removeAttr('selected')

		    	// Append Value
		    	$('#course-name').val(data.data.name)
		    	$('#course-periode').val(data.data.periode)
		    	$('#course-price').val(data.data.price)
		    	
		    	// tinyMCE
		    	tinyMCE.get('course-description').setContent(data.data.description)

		    	$('#periode-type > option').each(function() {
		    		if (this.value == data.data.periode_type) {
		    			$(this).attr('selected', true)
		    		}
		    	})

		    	$('#course-type > option').each(function() {
		    		if (this.value == data.data.course_type) {
		    			$(this).attr('selected', true)

		    			if (this.value == 2) {
			    			$('#course-price-area').css('display', 'none')
		    			} else {
			    			$('#course-price-area').css('display', '')
		    			}
		    		}
		    	})

    	    	$('#is-private > option').each(function() {
    	    		if (this.value == data.data.is_private) {
    	    			$(this).attr('selected', true)

    	    			$('.is_private').css('display', '')
    	    			$('.is_public').css('display', 'none')

    	    			// If commission_type 1
		    	    	$('#commission-type > option').each(function() {
		    	    		if (this.value == data.data.commission_type) {
		    	    			$(this).attr('selected', true)

		    	    			if (this.value == 1) {
		    	    				$('.commission_type_1').css('display', '')
		    	    			} else {
		    	    				$('.commission_type_1').css('display', 'none')
		    	    			}
		    	    		}
    	    			})
    	    		} else {
    	    			$('.is_public').css('display', '')
    	    			$('.is_private').css('display', 'none')
    	    			$('.commission_type_1').css('display', 'none')
    	    		}
    	    	})

    	    	$('#course-package-category > option').each(function() {
    	    		if (this.value == data.data.course_package_category) {
    	    			$(this).attr('selected', true)
    	    		}
    	    	})

	    		$('#course-modal').modal({backdrop: 'static', keyboard: false})
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	// Validate Destroy Course
	$(document).on('click', '.delete-course', function (e) {
	    e.preventDefault()

	    // Initialize
	    let courseId   	= $(this).attr('id')
	    let courseName = $(this).attr('name')

	    // Validate
	    Swal.fire({
	        text: `@lang('label.delete_course_package') ${courseName}?`,
	        icon: 'warning',
	        showCancelButton: true,
	        confirmButtonColor: '#3085d6',
	        cancelButtonColor: '#d33',
	        cancelButtonText: 'Batal',
	        confirmButtonText: 'Oke'
	    }).then((result) => {
	      if (result.isConfirmed) {
	        // Call Function
	        destroyCourse(courseId)
	      }
	    })
	})

	// Destroy Course
	function destroyCourse (courseId) {
	    $.ajax({
	        url: `${baseUrl}/course/delete/${courseId}`,
	        type: 'DELETE',
	        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
	        success: data => {
	        	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
	        	}

	    		toastr.success(data.message, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

	    		$(`#card-course-area-${courseId}`).remove()
	        },
	        error: e => {
	            console.log(e)

		        toastr.error(`Data gagal dihapus. `, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
	            
	            return 0
	        }
	    })
	}

	// Filter Course
	$(document).on('click', '.filter-course', function (e) {
		e.preventDefault()

		// Remove Value In Var
		nextPageUrl = ''

		// Initialize
		let filter 	= $(this).attr('id')
		let text 	= $(this).text()

		// Call Function
		showCourse('', '', filter)
		countCourse(filter, text)

		// Dom Manipulation
		$('#load-more-area').css('display', 'none')
		$('#result-loading-data').show()
		$('#course-list-results').html('')
	})

	// Count Course
	function countCourse(filter, text) {
		$.ajax({
		    url: `${baseUrl}/course/count?filter=${filter}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
				$('#sub-text-course-element').html(`Total ${text} : ${data.data} Kursus`)
		    },
		    error: e => {
		        console.log(e)

		        toastr.error(`Data Total Kursus gagal dimuat`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}

	// Search
	$(document).on('click', '.category-data', function () {
		$('#input-category-id').val($(this).attr('id'))
		$('#search_concept').html($(this).attr('course-name'))
	})

	$(document).on('submit', '#search-course form', function (e) {
		e.preventDefault()

		// Disabled True
		$('#btn-search-loading').attr('disabled', true)

		// Initialize
		let url 	 = $('#course-list-url').val()
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
		    	// nextPageUrl = data.meta.next_page_url

		    	// if (nextPageUrl) {
		    	// 	$('#load-more-area').css('display', '')
		    	// } else {
		    	// 	$('#load-more-area').css('display', 'none')
		    	// }
		    },
		    error: e => {
		        console.log(e)

           		// Disabled False
        		$('#btn-search-loading').attr('disabled', false)

		        toastr.error(`Data gagal dimuat, silahkan refresh kembali halaman anda.`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})

	function formatRupiah(angka, prefix) {
	    var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
	}

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
@endpush