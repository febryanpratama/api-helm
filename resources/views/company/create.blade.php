@extends('layouts.master')

@push('style')
{{-- SRC --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/introjs.min.css" integrity="sha512-iaYE9B9u4GU8+KkRTOdRdZuzKdYw1X0hOAa4GwDV/uwdXgoX/ffT3ph1+HG1m4LPZD/HV+dkuHvWFLZtPviylQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
	.avatar-preview {
		width: 100px;
		height: 100px;
	    border-radius: 100%;
	    border: 1px solid gray;
	}

	.card-header {
		background-color: white !important;
	}

	.border-left-element {
		border-left: 2px solid #62DDBD;
		padding-left: 5px;
	}

	.customTooltip .introjs-tooltip-title {
		color: #62ddbd;
	}
</style>
@endpush

@section('content')
{{-- Hidden --}}
<input type="hidden" id="company-id" value="{{ auth()->user()->company->ID }}">
<input type="hidden" id="logo-company-private" value="{{ auth()->user()->company->Logo }}">
<input type="hidden" id="avatar-private" value="{{ auth()->user()->avatar }}">
<input type="hidden" id="page-package-course" value="{{ $pagePackageCourse }}">

<div class="container">
	@php
		// Initialize
		$sosmedExists = false;

		if (auth()->user()->company->facebook || auth()->user()->company->instagram || auth()->user()->company->youtube || auth()->user()->company->linkedin) {
			$sosmedExists = true;
		}
	@endphp

	@if(!auth()->user()->phone || !$sosmedExists)
		<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> @lang('label.info_complated_data')</div>
	@endif

	<div class="row">
		<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-12 mb-4">
			<div class="card card-custom">
				<div class="card-body">
					<div class="clearfix text-center">
						<img src="{{ companyLogo() }}" alt="preview-img" class="avatar-preview">

						<div class="mt-2"><b>{{ auth()->user()->company->Name }}</b></div>
					</div>

					<div class="clearfix mt-3">
						<div class="text-card">
							<b>@lang('label.institution_phone')</b>

							<div class="text-color">
								{{ auth()->user()->company->Phone }}
							</div>
						</div>
						<div class="text-card">
							<b>@lang('label.institution_email')</b>
							
							<div class="text-color">
								{{ (auth()->user()->company->Email) ? auth()->user()->company->Email : '-' }}
							</div>
						</div>
						<div class="text-card">
							<b>@lang('label.total_course_package')</b>

							<div class="text-color">
								{{ $totalCourse }} @lang('label.course_package')
							</div>
						</div>
						<div class="text-card">
							<b>@lang('label.total_students_join')</b>

							<div class="text-color">
								{{ $totalSJoin }} @lang('label.students_join')
							</div>
						</div>
						<div class="text-card">
							<b>@lang('label.referral_code')</b>

							<div class="text-color">
								<span id="referral-element-area">{{ auth()->user()->referral_code }}</span>

								<i class="fas fa-copy cursor-area" id="referral-copy-icon"></i>
							</div>
						</div>
						<div class="text-card">
							<b>@lang('label.join_date')</b> <i class="fas fa-question-circle text-color cursor-area config-tooltip" data-toggle="tooltip" data-placement="top" title="@lang('label.join_date_description')"></i>

							<div class="text-color">
								{{ auth()->user()->created_at->format('d F Y ') }}
							</div>
						</div>
						<div class="text-card">
							<b>@lang('label.account_profile_link')</b>

							<div class="text-color">
								<span id="institution-element-area">{{ (auth()->user()->company) ? ENV('SITE_URL').'/institution/'.auth()->user()->company->Name : '-' }}</span>

								<i class="fas fa-copy cursor-area" id="institution-link-icon"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-12 mb-4">
			<div class="card card-custom" id="form-profile">
				<form method="POST" enctype="multipart/form-data">
					<div class="card-body">
						<div class="clearfix border-left-element mb-2 intro-one-new-account">
							<b>@lang('label.account_profile')</b>
						</div>

						<div class="form-group">
							<label for="">@lang('label.name')</label> <span class="text-danger">*</span>

							<input id="profil-name" type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" placeholder="@lang('label.enter_name')">
						</div>

						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
								<div class="form-group">
									<label for="">@lang('label.phone')</label> <span class="text-danger">*</span>

									<input type="number" class="form-control" name="phone" id="account-phone" value="{{ auth()->user()->phone }}" placeholder="@lang('label.enter_phone')" >
								</div>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
								<div class="form-group">
									<label for="">@lang('label.email')</label> <span class="text-danger">*</span>

									<input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" placeholder="@lang('label.enter_email')" readonly="">
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="" class="">@lang('label.profile_picture')</label> @if(!auth()->user()->avatar) <span class="text-danger">*</span> @endif
							<br>
							<button class="btn btn-info btn-sm text-white" id="avatar-btn-file" type="button"><i class="fa fa-camera"></i></button>
							<span id="span-name-file-avatar" class="pl-2"><i>*@lang('label.not_file_selected')</i></span>
							<input type="file" name="avatar" class="form-control hide-element" id="file-avatar" accept="image/png,image/jpg,image/jpeg">
						</div>

						<div class="clearfix border-left-element mb-2">
							<b>@lang('label.agency_profile')</b>
						</div>

				        <div class="form-group">
			        	    <label for="" class="">@lang('label.institution_name')</label> <span class="text-danger">*</span>
			        			
			        		<input id="company-name" type="text" class="form-control" name="institution_name" value="{{ auth()->user()->company->Name }}" placeholder="">
				        </div>

				        <div class="row">
					        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
					        	<div class="form-group">
					        		<label for="" class="">@lang('label.institution_phone')</label> <span class="text-danger">*</span>

					        		<input id="company-phone" type="number" class="form-control" name="institution_phone" value="{{ auth()->user()->company->Phone }}" placeholder="@lang('label.enter_institution_phone')">
					        	</div>
					        </div>

					        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
						        <div class="form-group">
						        	<label for="" class="">@lang('label.institution_email')</label> <span class="text-danger">*</span>

						        	<input id="company-email" type="email" class="form-control" name="institution_email" value="{{ (auth()->user()->company->Email == '-') ? '' : auth()->user()->company->Email }}" placeholder="@lang('label.enter_institution_email')">
						        </div>
						    </div>
						</div>

						<div class="form-group">
							<label for="">@lang('label.institution_address')</label>

							<textarea name="institution_address" id="institution-address" rows="3" class="form-control" placeholder="@lang('label.enter_institution_address')">{{ auth()->user()->company->Address }}</textarea>
						</div>

				        <div class="form-group">
				        	<label for="" class="">@lang('label.logo')</label> @if(!auth()->user()->company->Logo) <span class="text-danger">*</span> @endif <br>

				        	<button class="btn btn-info btn-sm text-white" id="logo-btn-file" type="button"><i class="fa fa-camera"></i></button>
				        	<span id="span-name-file-logo" class="pl-2"><i>*@lang('label.not_file_selected')</i></span>
				        	<input type="file" name="logo" class="form-control hide-element" id="file-logo" accept="image/png,image/jpg,image/jpeg">
				        </div>

				        <div class="row">
					        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
				        		<div class="form-group">
				        			<label for="">Link Facebook (Profil/Halaman/Grup)</label>
				        			<input type="text" name="facebook" class="form-control" id="facebook-url" placeholder="Ex: https://facebook.com" value="{{ auth()->user()->company->facebook }}">
				        		</div>
				        	</div>

					        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
					        	<div class="form-group">
					        		<label for="">Link Instagram</label>
					        		<input type="text" name="instagram" class="form-control" placeholder="Ex: https://instagram.com" value="{{ auth()->user()->company->instagram }}" id="instagram-url">
					        	</div>
					        </div>
				        </div>

		                <div class="row">
		        	        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
		        	        	<div class="form-group">
		        	        		<label for="">Link Youtube</label>
		        	        		<input type="text" name="youtube" class="form-control" placeholder="Ex: https://youtube.com" value="{{ auth()->user()->company->youtube }}" id="youtube-url">
		        	        	</div>
		        	        </div>

		        	        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
		        	        	<div class="form-group">
		        	        		<label for="">Link LinkedIn</label>
		        	        		<input type="text" name="linkedin" class="form-control" placeholder="Ex: https://www.linkedin.com" value="{{ auth()->user()->company->linkedin }}" id="linkedin-url">
		        	        	</div>
		        	        </div>
		        	    </div>

		        	    <button class="btn btn-sm btn-company" id="company-btn-loading">@lang('label.save')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

{{-- Include File --}}
@include('components.company-modal')
@stop

@push('script')
{{-- SRC --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/5.1.0/intro.min.js" integrity="sha512-B0B1bdTGi9XHtoCINbtR6z9Vi8IYvvtImIE8KWMtqWAVUt2Wn2k69GxM7ya/3rQcz/Pgm/o1WcDU347/5k202A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Intro JS --}}
<script>
	$(document).ready(function () {
		// Initialize
		let complatedData 	  = $('#logo-company-private').val()
		let pagePackageCourse = $('#page-package-course').val()
		
		if (!complatedData) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'Selesai',
					steps: [{
						title: 'Selamat Datang!',
					    intro: 'Selamat Datang di <b>RuangAjar</b>! 👋 <br> Disini kamu bisa mengajarkan keahlian yang kamu miliki atau minati kepada banyak orang yang membutuhkannya untuk pekerjaan atau usahanya. Dan kamu mendapatkan cuan ^_^.'
					},
					{
						title: 'Lengkapi Data',
					    element: document.querySelector('.intro-one-new-account'),
					    intro: 'Sebelum menjelajahi <b>RuangAjar</b> lebih jauh, Yuk! lengkapi terlebih dahulu Profil Akun kamu.'
					}]
				}).start()
			}, 2000)
		}

		if (complatedData && !pagePackageCourse) {
			setTimeout(function () {
				introJs().setOptions({
					tooltipClass: 'customTooltip',
					prevLabel: 'Sebelumnya',
					nextLabel: 'Selanjutnya',
					doneLabel: 'OK',
					steps: [{
						title: 'Buat Paket Kursus',
					    element: document.querySelector('.intro-two-new-account'),
					    intro: 'Klik disini, untuk membuat Paket Kursus.'
					}]
				}).start()
			}, 2000)
		}
	})
</script>

{{-- Company --}}
<script>
	$(document).on('click', '.edit-profil-company', function () {
		$('#company-modal-aria').html('Edit Data Lembaga Kursus')
	    $('#company-modal').modal({backdrop: 'static', keyboard: false})
	})

	// Trigger Input Type File
	$(document).on('click', '#logo-btn-file', function () {
	    $('#file-logo').click()
	})

	$(document).on('click', '#avatar-btn-file', function () {
	    $('#file-avatar').click()
	})

	// Get Full Path
	$(document).on('change', '#file-logo', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-logo').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-logo').html("<i>*@lang('label.not_file_selected')</i>")
	    }
	})

	$(document).on('change', '#file-avatar', function () {
	    // Validate
	    if (this.files[0]) {
	        $('#span-name-file-avatar').html(`${this.files[0].name}`)
	    } else {
	        $('#span-name-file-avatar').html("<i>*@lang('label.not_file_selected')</i>")
	    }
	})

	// Update
	$(document).on('submit', '#form-profile form', function (e) {
		e.preventDefault()

		// Initalize
		let actPhone 	= $('#account-phone').val()
		let name 		= $('#company-name').val()
		let phone 		= $('#company-phone').val()
		let email 		= $('#company-email').val()
		let avatar 		= $('#file-avatar')[0].files
		let logo 		= $('#file-logo')[0].files
		let companyId 	= $('#company-id').val()
		let logoVal		= $('#logo-company-private').val()
		let avatarVal	= $('#avatar-private').val()
	    let facebook 	= $('#facebook-url').val()
	    let instagram 	= $('#instagram-url').val()
	    let youtube 	= $('#youtube-url').val()
	    let linkedin 	= $('#linkedin-url').val()

		// Validate
		if (!actPhone) {
			toastr.error(`Nomor Telepon harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			$('#account-phone').focus()

			return 0
		}

		if (!name) {
			toastr.error(`Nama harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!phone) {
			toastr.error(`Nomer Hp harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			return 0
		}

		if (!avatarVal) {
			if (avatar.length == 0) {
				toastr.error(`Foto Profil harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				return 0
			}
		}

		if (!email) {
			toastr.error(`Email harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			
			$('#company-email').focus()

			return 0
		}

		if (!logoVal) {
			if (logo.length == 0) {
				toastr.error(`Logo harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				return 0
			}
		}

		if (!facebook || !instagram || !youtube || !linkedin) {
			if (facebook || instagram || youtube || linkedin) {

			} else {
				toastr.error(`Salah satu sosial media harus diisi!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

				$('#facebook-url').focus()

				return 0
			}
		}

		// Disabled Button True
		$('#company-btn-loading').attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/company/update/${companyId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: new FormData(this),
		    contentType: false,
		    cache: false,
		    processData: false,
		    dataType: 'json',
		    success: data => {
		    	// Disabled Button False
		    	$('#company-btn-loading').attr('disabled', false)

		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			        return 0
		    	}

		        toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		        setTimeout(function () {
		        	window.location = `${baseUrl}/${data.company_name}/company/edit?company=${companyId}`
		        }, 1000)
		    },
		    error: e => {
		        console.log(e.responseJSON.message)

		        // Disabled Button False
		        $('#company-btn-loading').attr('disabled', false)

		        toastr.error(`${e.responseJSON.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	})
</script>

{{-- Copy Text To Clipboard --}}
<script>
	$(document).on('click', '#referral-copy-icon', function () {
		// Call Function
		copyToClipboard($('#referral-element-area').html())
	})

	$(document).on('click', '#institution-link-icon', function () {
		// Initialize
		let text = $('#institution-element-area').html()

		// Call Function
		copyToClipboard(text.replace(' ', '%20'))
	})

	function copyToClipboard(element) {
		// Notification
		toastr.success(`Text di salin ke clipboard`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

		// Initialize
		var $temp = $("<input>");
	 	$("body").append($temp);

	 	$temp.val(element).select();
	 	
	 	document.execCommand("copy");
	 	
	 	$temp.remove();
	}
</script>
@endpush