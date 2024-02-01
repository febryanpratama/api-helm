@extends('layouts.app')

@push('style')
<style type="text/css">
	.fs-text-message {
		font-size: 13px;
	}

	/* Desktop */
	@media screen and (max-width: 900px) and (min-width: 600px), (min-width: 1100px) {
		.width-height-avatar {
			width: 70%;
		}
	}

	.scroll-y-report {
		overflow-y: scroll;
		overflow-x: hidden;
		height: 800px;
	}

	.border-left-element {
	    border-left: 3px solid #6cb2eb;
	}

	.img-checkin-out {
		width: 130px;
		height: 130px !important;
		border-radius: 20px 20px 20px 20px;
	}

	.over-icon {
	    transform: rotate( -180deg );            
	    transition: transform 150ms ease;
	}

	.out-icon {
	    transform: rotate( -360deg );            
	    transition: transform 150ms ease;
	}

	.swiper-slide-width {
		width: 22% !important;
	}
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="download-reports-url" value="{{ route('report.excel_attendance_task') }}">
<input type="hidden" id="hint-widget-store-url" value="{{ route('hint.widget.store') }}">

<div class="container mt-2">
	<div class="row">
		{{-- Reports --}}
		<div class="col-md-6 col-12 mb-4">
			<div class="card">
				<div class="card-header">
					<div class="float-right">
						{{-- <a href="{{ route('report.excel_attendance_task') }}" class="btn btn-white btn-sm rounded-pill">Download</a> --}}
						<button class="btn btn-white btn-sm rounded-pill download-reports" id="instruction-three" title="Download Data" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Klik Download untuk mendownload data Laporan yang dipilih, Jika tidak ada yang dipilih maka akan mendownload semua data Laporan.">Download</button>
						
						<button class="btn btn-link text-white" data-toggle="collapse" data-target="#collapse-report" aria-expanded="false" aria-controls="collapse-report"><i class="fa fa-calendar"></i></button>
					</div>
					<b>Laporan Member</b>
				</div>

				<div class="card-body">
					<div class="collapse overflow mb-3" id="collapse-report">
					  <div class="card">
					  	<div class="card-body" id="reports">
					  		<form method="GET" action="{{ route('report.task_attendance') }}">
								<input type="hidden" name="search" value="1">
								<div class="row">
								  	<div class="col-md-6">
								      	<div class="form-group">
									      	<label for="">@lang('label.start_date')</label>
									      	<input type="date" name="start_date" value="{{ request()->get('start_date') }}" class="form-control" placeholder="" id="start_date">
								      	</div>
								  	</div>

								  <div class="col-md-6">
								  	<div class="form-group">
									    <label for="">@lang('label.end_date')</label>
										<input type="date" name="end_date" value="{{ request()->get('end_date') }}" class="form-control" placeholder="" id="end_date">
								  	</div>
								  </div>
								</div>

				                <div class="row">
				                	<div class="col-md-6">
				                		<div class="form-group">
				              				<label for="">@lang('label.user')</label>
				              				<select name="user_id" id="search_user" class="form-control">
					              			    <option value="">Choose User</option>
					              			    @foreach ($user as $item)
					              			        <option value="{{ $item->id }}" {{ $item->id == request()->user_id ? 'selected' : '' }}>{{ $item->name }}</option>
					              			    @endforeach
				              				</select>
				                		</div>
				                  	</div>

				                  	<div class="col-md-6">
				                      	<button class="btn btn-md btn-primary rounded-pill btn-sm" type="submit" style="margin-top:35px;">@lang('button.search') <i class="fa fa-search"></i></button>
				                      	<a href="{{ route('report.task_attendance') }}" class="btn btn-md btn-primary rounded-pill btn-sm" style="margin-top:35px;">@lang('button.reset') <i class="fa fa-refresh"></i></a>
				                  	</div>
				                </div>
			            	</form>
					  	</div>
					  </div>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<a href="javascript:void(0)" class="btn btn-sm btn-company text-white rounded-pill" data-toggle="collapse" data-target="#filter-division-element" aria-expanded="false" aria-controls="filter-division-element"><i class="fa fa-filter"></i></a>
						</div>

						<div class="float-left">
							<span class=""><b>Total @lang('label.division') : {{ count(auth()->user()->company->division) }} Divisi</b></span>
						</div>
					</div>

					<div class="clearfix mt-3 collapse" id="filter-division-element">
						<div class="card">
							<div class="card-body">
								@php
									// Initialize
									$btnClass 		= 'btn-outline-company text-company';
									$btnClassActive = 'btn-company text-white';
									$divId 	  		= request('division');
								@endphp

								<p><b>Filter Divisi :</b></p>
								<a href="{{ route('report.task_attendance') }}" class="btn btn-sm {{ (!request('division')) ? $btnClassActive : $btnClass }} rounded-pill mb-2">Semua Divisi</a>

								@foreach(auth()->user()->company->division as $division)
								<a href="{{ route('report.task_attendance') }}?division={{ $division->ID }}" class="btn btn-sm {{ ($divId == $division->ID) ? $btnClassActive : $btnClass }} rounded-pill mb-2">{{ $division->Name }}</a>
								@endforeach
							</div>
						</div>
					</div>

				    <div class="clearfix mt-3 scroll-y-report">
				    	@forelse($user as $item)
				    		<div class="row">
				    			@if($loop->iteration == '1' && $hintWidgets < 3)
					    		    <div class="col-md-2 col-3 cursor-area" id="instruction-one" data-toggle="popover" title="Pilih Beberapa Data" data-content="Klik avatar member untuk menandai data Laporan." data-placement="left">
					    		        <div class="float-left mt-2 check-element" id="check-element-{{ $item->id }}" item-id="{{ $item->id }}">
					    		            <img src="https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg" class="rounded-circle width-height-avatar">
					    		        </div>

    		            		        <div class="ml-2 mt-2 uncheck-element" id="uncheck-element-{{ $item->id }}" item-id="{{ $item->id }}" style="display: none;">
    		        	    		        <div class="rounded-circle text-center pt-1 popover-header" style="width: 35px; height: 35px;">
    		        	    		        	<i class="fa fa-check"></i>
    		        	    		        </div>
    		            		        </div>
					    		    </div>
				    		    @elseif ($loop->iteration == '2' && $hintWidgets < 3)
					    		    <div class="col-md-2 col-3 cursor-area" id="instruction-twu" data-toggle="popover" title="Pilih Beberapa Data" data-content="Klik juga disini!" data-placement="left">
					    		        <div class="float-left mt-2 check-element" id="check-element-{{ $item->id }}" item-id="{{ $item->id }}">
					    		            <img src="https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg" class="rounded-circle width-height-avatar">
					    		        </div>

			            		        <div class="ml-2 mt-2 uncheck-element" id="uncheck-element-{{ $item->id }}" item-id="{{ $item->id }}" style="display: none;">
			        	    		        <div class="rounded-circle text-center pt-1 popover-header" style="width: 35px; height: 35px;">
			        	    		        	<i class="fa fa-check"></i>
			        	    		        </div>
			            		        </div>
					    		    </div>
				    		    @else
	    		        		    <div class="col-md-2 col-3 cursor-area">
	    		        		        <div class="float-left mt-2 check-element" id="check-element-{{ $item->id }}" item-id="{{ $item->id }}">
	    		        		            <img src="https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg" class="rounded-circle width-height-avatar">
	    		        		        </div>

	    		        		        <div class="ml-2 mt-2 uncheck-element" id="uncheck-element-{{ $item->id }}" item-id="{{ $item->id }}" style="display: none;">
	    		    	    		        <div class="rounded-circle text-center pt-1 popover-header" style="width: 35px; height: 35px;">
	    		    	    		        	<i class="fa fa-check"></i>
	    		    	    		        </div>
	    		        		        </div>
	    		        		    </div>
				    		    @endif

				    		    <div class="col-md-10 col-9 cursor-area report-detail" id="{{ $item->id }}" name="{{ $item->name }}" start-date="{{ request()->get('start_date') }}" end-date="{{ request()->get('end_date') }}" division="{{ (count($item->division) > 0) ? $item->division[0]->Name : '-' }}">
				    		    	<div class="clearfix mt-2">
				    		    		<div class="float-right text-right">
				    		    			<b>{{ (count($item->division) > 0) ? $item->division[0]->Name : '-' }}</b><br>
				    		    			<h6>
				    		    				{{-- Initialize --}}
				    		    				@php
				    		    					$presence = $item->attendance($item->id);
				    		    				@endphp

				    		    				@if ($presence)
				    		    					@if (!$presence->check_out_datetime)
						    		    				<b class="text-info">Bekerja</b>
						    		    			@else
					    		    					<b class="text-success">Hadir</b>
				    		    					@endif
				    		    				@else
					    		    				<b class="text-danger">Tidak Hadir</b>
				    		    				@endif
				    		    			</h6>
				    		    		</div>

				    		    		<div class="float-left">
				    		    			<b>{{ $item->name }}</b><br>
				    		    			<h6><b class="text-company">{{ count($item->TasksToDay($item->id)) }} Tugas</b></h6>
				    		    		</div>
				    		    	</div>
				    		    </div>
				    		</div>
		    		    <hr>
		    		    @empty
		    		    <div class="clearfix mt-4 text-center"><b><i>Tidak Ada Data</i></b></div>
				    	@endforelse
				    </div>
				</div>
			</div>
		</div>

		{{-- Member Activity --}}
		<div class="col-md-6 col-12">
			<div class="card">
				<div class="card-header">
					<div class="float-right">
						<button class="btn btn-link text-white" data-toggle="collapse" data-target="#collapse-activity" aria-expanded="false" aria-controls="collapse-activity"><i class="fa fa-calendar"></i></button>
					</div>
					<b>Aktivitas Member</b>
				</div>

				<div class="card-body">
					<div class="collapse overflow mb-3" id="collapse-activity">
					  <div class="card">
					  	<div class="card-body">
					  		<form action="{{ route('report.task_attendance') }}" method="get">
								<input type="hidden" name="search" value="1">
								<div class="row">
							  	<div class="col-md-6">
							      	<div class="form-group">
								      	<label for="">@lang('label.start_date')</label>
								      	<input type="date" name="start_date_activity" value="{{ request()->get('start_date_activity') }}" class="form-control" placeholder="">
							      	</div>
							  	</div>

								  <div class="col-md-6">
								  	<div class="form-group">
									    <label for="">@lang('label.end_date')</label>
										<input type="date" name="end_date_activity" value="{{ request()->get('end_date_activity') }}" class="form-control" placeholder="">
								  	</div>
								  </div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="">@lang('label.user')</label>
											<select name="user_id_activity" id="search_user_activity" class="form-control">
										    <option value="">Choose User</option>
										    @foreach ($user as $item)
										        <option value="{{ $item->id }}" {{ $item->id == request()->user_id_activity ? 'selected' : '' }}>{{ $item->name }}</option>
										    @endforeach
											</select>
										</div>
									</div>

									<div class="col-md-6">
									  	<button class="btn btn-md btn-primary rounded-pill btn-sm" type="submit" style="margin-top:35px;">@lang('button.search') <i class="fa fa-search"></i></button>
									  	<a href="{{ route('report.task_attendance') }}" class="btn btn-md btn-primary rounded-pill btn-sm" style="margin-top:35px;">@lang('button.reset') <i class="fa fa-refresh"></i></a>
									</div>
								</div>
              				</form>
					  	</div>
					  </div>
					</div>

					<div class="clearfix">
						<div class="float-right">
							<a href="javascript:void(0)" class="btn btn-sm btn-company text-white rounded-pill" data-toggle="collapse" data-target="#filter-member-activity-element" aria-expanded="false" aria-controls="filter-member-activity-element"><i class="fa fa-filter"></i></a>
						</div>

						<div class="float-left">
							<span class=""><b>Total Aktivitas Hari Ini : {{ $memberAcTotal }} Aktivitas</b></span>
						</div>
					</div>

					<div class="clearfix mt-3 collapse" id="filter-member-activity-element">
						<div class="card">
							<div class="card-body">
								@php
									// Initialize
									$memberAcId = request('member-activity');
								@endphp

								<p><b>Filter Divisi :</b></p>
								<a href="{{ route('report.task_attendance') }}" class="btn btn-sm {{ (!request('member-activity')) ? $btnClassActive : $btnClass }} rounded-pill mb-2">Semua Divisi</a>

								@foreach(auth()->user()->company->division as $division)
								<a href="{{ route('report.task_attendance') }}?member-activity={{ $division->ID }}" class="btn btn-sm {{ ($memberAcId == $division->ID) ? $btnClassActive : $btnClass }} rounded-pill mb-2">{{ $division->Name }}</a>
								@endforeach
							</div>
						</div>
					</div>

					<div class="clearfix mt-3 scroll-y-report">
						@forelse($memberActivity as $val)
						<div class="row">
						    <div class="col-md-2 col-3">
						        <div class="float-left mt-2">
						            <img src="https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg" class="rounded-circle width-height-avatar">
						        </div>
						    </div>

						    <div class="col-md-10 col-9">
						        <div class="float-right mt-2">
						        	{{ $val->updated_at->format('d F Y H:i') }}
						        </div>

					            <div class="mt-2">
					            	<b>{{ $val->user->name }}</b><br>
					            <span class="fs-text-message">Merubah Status "{{ $val->todo->todo }}" Menjadi {{ ucfirst($val->status) }}</span>
					            </div>
						    </div>
						</div>
						<hr>
						@empty
		    		    <div class="clearfix mt-4 text-center"><b><i>Tidak Ada Data</i></b></div>
						@endforelse
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- Include Modal --}}
@include('components.report-detail-modal')
@stop

@push('script')
<script src="{{ asset('js/php-date-formatter.js') }}"></script>

{{-- Global Var --}}
<script>
	{{-- Initialize --}}
	let members = []
</script>

{{-- Roam Widget --}}
<script>
	$(function () {
		// Setting Popover
		$('[data-toggle="popover"]').popover()

		// Show Popover
		setTimeout(function () {
			$('#instruction-one').popover('show')
		}, 1000)
	})

	$(document).on('click', '#instruction-one', function () {
		$('#instruction-one').popover('hide')
		
		setTimeout(function () {
			$('#instruction-twu').popover('show')
		}, 1000)

		// Call Function
		storeHintWidget('instruction_one')
	})

	$(document).on('click', '#instruction-twu', function () {
		$('#instruction-twu').popover('hide')
		
		setTimeout(function () {
			$('#instruction-three').popover('show')
		}, 1000)

		// Call Function
		storeHintWidget('instruction_twu')
	})

	$(document).on('click', '#instruction-three', function () {
		$('#instruction-three').popover('hide')

		// Call Function
		storeHintWidget('instruction_three')
	})

	function storeHintWidget(step) {
		// Initialize
		let url = $('#hint-widget-store-url').val()

		$.ajax({
		    url: `${url}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		        step: step,
		        widget: 'reports_page'
		    },
		    success: data => {
		        // console.log(data)
		        // Code Here!
		    },
		    error: e => {
		        console.log(e)

		        Swal.fire({
		          title: 'Error',
		          text: '500 Internal Server Error!',
		          icon: 'error'
		        })
		    }
		})
	}
</script>

{{-- Check UnCheck Element --}}
<script>
	$(document).on('click', '.check-element', function (e) {
		e.preventDefault()

		// Initialize
		let itemId = $(this).attr('item-id')

		$(`#check-element-${itemId}`).css('display', 'none')
		$(`#uncheck-element-${itemId}`).css('display', '')

		// Push Member
		members.push(itemId)
	})

	$(document).on('click', '.uncheck-element', function (e) {
		e.preventDefault()

		// Initialize
		let itemId = $(this).attr('item-id')

		$(`#uncheck-element-${itemId}`).css('display', 'none')
		$(`#check-element-${itemId}`).css('display', '')

		memberIndex = members.indexOf(itemId)

		if (memberIndex !== -1) {
			members.splice(memberIndex, 1)
		}
	})
</script>

{{-- Download Reports --}}
<script>
	$(document).on('click', '.download-reports', function (e) {
		e.preventDefault()
		// Initialize
		url  = $('#download-reports-url').val()
		type = 'all'

		if (members.length > 0) {
			type = 'aNumberOf'
		}

		// Disabled Button True
		$('.download-reports').attr('disabled', true)

		$.ajax({
		    url: `${url}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	type: type,
		    	membersId: members,
		    	start_date: $('#start_date').val(),
		    	end_date: $('#end_date').val(),
		    	user_id: $('#search_user').val()
		    },
		    xhrFields:{
               responseType: 'blob'
           	},
		    success: (result, status, xhr) => {
		    	// Disabled Button False
		    	$('.download-reports').attr('disabled', false)

		    	// Initialize
		    	let disposition = xhr.getResponseHeader('content-disposition')
    	        let matches 	= /"([^"]*)"/.exec(disposition)
    	        let filename 	= (matches != null && matches[1] ? matches[1] : 'report.xlsx')

    	        // The actual download
    	        let blob = new Blob([result], {
    	            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    	        })

    	        let link = document.createElement('a')
    	        link.href = window.URL.createObjectURL(blob)
    	        link.download = filename

    	        document.body.appendChild(link)

    	        link.click()
    	        document.body.removeChild(link)

		    },
		    error: e => {
		    	Swal.fire({
		    	  title: 'Error',
		    	  text: 'Gagal Mendownload Laporan',
		    	  icon: 'error'
		    	})

		    	console.log(e)

		    	// Disabled Button False
		    	$('.download-reports').attr('disabled', false)
		    }
		})
	})
</script>

{{-- Report Detail --}}
<script>
	$(document).on('click', '.report-detail', function (e) {
		// Initialize
		let userIdPrivate = $(this).attr('id')

		$('#report-detail-modal').modal('show')
		$('#report-detail-modal-aria').html('Detail Laporan')
		$('#current-division').html($(this).attr('division'))
		$('#user-name-in-modal').html($(this).attr('name'))
		$('#download-report-by-user-id').attr('href', `${baseUrl}/reports/excel?user_id=${userIdPrivate}`)

		// Initialize
		let startDate = $(this).attr('start-date')
		let endDate   = $(this).attr('end-date')

		// Call Function
		progressToDay(userIdPrivate)
		attendaceCount(userIdPrivate)
		checkInOut(userIdPrivate, startDate, endDate)
	})

	function progressToDay(userId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/reports/task-list/${userId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Initialize
		    	let template = templateTaskList(data)

		    	$('#results-progress-body-modal').html(template)
		    	$('#count-task').html(data.data.length)
		    },
		    error: e => {
		    	Swal.fire({
		    	  title: 'Error',
		    	  text: 'Gagal mendapatkan data List Tugas',
		    	  icon: 'error'
		    	})

		    	console.log(e)
		    }
		})
	}

	function attendaceCount(userId) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/reports/attendance-count/${userId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	// Append Val
		    	$('#present').html(data.data.present)
		    	$('#not-present').html(data.data.not_present)
		    },
		    error: e => {
		    	console.log(e)

		    	Swal.fire({
		    	  title: 'Error',
		    	  text: 'Gagal mendapatkan total data kehadiran',
		    	  icon: 'error'
		    	})
		    }
		})
	}

	function checkInOut(userId, startDate, endDate) {
		// Initialize
		$.ajax({
		    url: `${baseUrl}/reports/attendance/${userId}`,
		    type: 'GET',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    data: {
		    	startDate,
		    	endDate,
		    	userId
		    },
		    success: data => {
		    	// Initialize
		    	let template = templateAttendance(data)

		    	$('#results-reports-body-modal').html(template)
		    },
		    error: e => {
		    	console.log(e)

		    	Swal.fire({
		    	  title: 'Error',
		    	  text: 'Gagal mendapatkan data kehadiran',
		    	  icon: 'error'
		    	})
		    }
		})
	}

	function templateTaskList(data) {
		// Initialize
		let template = ``

		if (data.data.length > 0) {
			$.each(data.data, function (key, val) {
				template += `<div class="row mb-3">
			      				<div class="col-md-12 col-12">
			      					<p>
			      						<span>Menyelesaikan</span>
			      						<b class="text-info"><u>${val.todo}</u></b>
			      						di <b class="text-info"><u>${val.task}</u></b>
			      					</p>

			      					<p>
			      						<div class="float-left">
			      							<span class="badge badge-info text-white rounded-pill">${val.percentage}%</span>
			      						</div>

			      						<div class="float-right">
			      							${val.is_done_time}
			      						</div>
			      					</p>
			      				</div>
			      			</div>

			      			<div class="clearfix">
			      				<div class="float-right cursor-area check-in-out-collapse" data-toggle="collapse" data-target="#latest-todo-${val.id}" aria-expanded="false" aria-controls="latest-todo-${val.id}" id="${val.id}">
			      		    			<i class="fa fa-angle-up" id="check-in-out-collapse-icon-${val.id}"></i>
			      				</div>

			      				<h6><b>List To Do (Done)</b></h6>
			      			</div>

			      			<div class="collapse" id="latest-todo-${val.id}">
				      			<div class="row">
				      				${val.todos}
				      			</div>
				      		</div>
			      			<hr>`
			})
		} else {
			template += `<div class="text-center mt-4"><b><i>Tidak ada tugas yang dikerjakan</i></b></div>`
		}

		return template
	}

	function templateAttendance (data) {
		// Initialize
		let template = ``

		if (data.data.length > 0) {
			$.each(data.data, function (key, val) {
				// Initialize
				let checkInPhoto  = val.check_in_photo
				let checkOutPhoto = val.check_out_photo
				let display       = 'none'
				let displayIn 	  = 'none'
				let displayOut    = 'none'

				if (checkInPhoto) {
					displayIn = ''
				}

				if (checkOutPhoto) {					
					displayOut = ''
				}

				if (checkInPhoto || checkOutPhoto) {
					display = ''
				}

				// Get Date Format
				let checkInDate  = dateConvert(val.check_in_datetime)
				let checkOutDate = dateConvert(val.check_out_datetime)

				template += `<div class="row">
        		    	<div class="col-md-12 col-12">
        		    		<div class="clearfix">
        		    			<div class="float-left">
        		    				<b class="text-info">Tanggal ${checkInDate}</b>
        		    			</div>

        		    			<div class="float-right cursor-area check-in-out-collapse" data-toggle="collapse" data-target="#check-in-out-collapse-${val.id}" aria-expanded="false" aria-controls="check-in-out-collapse-${val.id}" id="${val.id}">
  	      		    			<i class="fa fa-angle-up" id="check-in-out-collapse-icon-${val.id}"></i>
        		    			</div>
        		    		</div>
	
	        		    	<hr>
        		    	</div>
        		    </div>

        		    <div class="collapse" id="check-in-out-collapse-${val.id}">
	        		    <div class="row mt-2 mb-4" style="display: ${display}">
	        		    	<div class="col-md-6 col-6 text-center" style="display: ${displayIn}">
	        		    		<img src="${checkInPhoto}" alt="check-in" class="img-thumbnail img-checkin-out">

	        		    		<b class="mt-2">Checkin</b>
	        		    	</div>

	        		    	<div class="col-md-6 col-6 text-center" style="display: ${displayOut}">
	        		    		<img src="${checkOutPhoto}" alt="check-out" class="img-thumbnail img-checkin-out">

	        		    		<b class="mt-2">Checkout</b>
	        		    	</div>
	        		    </div>

	        		    <div class="clearfix">
	        		    	<p>
	        		    		<span>
	        		    			Tanggal Checkin
	        		    			<br>
	        		    			<b>${checkInDate}</b>
	        		    		</span>
	        		    	</p>

	        		    	<p>
	        		    		<span>
	        		    			Tempat Checkin
	        		    			<br>
	        		    			<b>
	        		    				${val.check_in_place}
	        		    			</b>
	        		    		</span>
	        		    	</p>
	        		    </div>
	        		    <hr>
	        		    <div class="clearfix">
	        		    	<p>
	        		    		<span>
	        		    			Tanggal Checkout
	        		    			<br>
	        		    			<b>${checkOutDate}</b>
	        		    		</span>
	        		    	</p>

	        		    	<p>
	        		    		<span>
	        		    			Tempat Checkout
	        		    			<br>
	        		    			<b>
	        		    				${val.check_out_place}
	        		    			</b>
	        		    		</span>
	        		    	</p>
	        		    </div>
        		    </div>`
			})
		} else {
			template += `<div class="text-center mt-4"><b><i>Pengguna belum melakukan Checkin dan Checkout</i></b></div>`
		}

		return template
	}

	$(document).on('click', '.check-in-out-collapse', function () {
		// Check Rotate
		if ($(this).attr('rotate-icon')) {
			$(`#check-in-out-collapse-icon-${$(this).attr('id')}`).removeClass('over-icon')
			$(`#check-in-out-collapse-icon-${$(this).attr('id')}`).addClass('out-icon')
			$('.check-in-out-collapse').removeAttr('rotate-icon')

			return 0
		}

		$(`#check-in-out-collapse-icon-${$(this).attr('id')}`).addClass('over-icon')
		$(`#check-in-out-collapse-icon-${$(this).attr('id')}`).removeClass('out-icon')
		$('.check-in-out-collapse').attr('rotate-icon', true)
	})
</script>

{{-- Convert Date To Js --}}
<script>
	function dateConvert(data) {
		let fmt    = new DateFormatter()
		let format = fmt.parseDate(data, 'Y-m-d h:i:s');
		let value  = fmt.formatDate(format, 'd F Y H:i');

		return value
	}
</script>

{{-- Config Select 2 --}}
<script>
	$(document).ready(function() {
        $('#search_user').select2({
            dropdownAutoWidth: true,
            width: '100%',
            placeholder: "@lang('placeholder.select')",
        })

        $('#search_user_activity').select2({
            dropdownAutoWidth: true,
            width: '100%',
            placeholder: "@lang('placeholder.select')",
        })
    })
</script>
@endpush