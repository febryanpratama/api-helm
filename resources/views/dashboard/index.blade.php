@extends('layouts.master')

@push('style')
<style>
	.weight-300{font-weight: 300;}
	.weight-400{font-weight: 400;}
	.weight-500{font-weight: 500;}
	.weight-600{font-weight: 600;}
	.weight-700{font-weight: 700;}
	.weight-800{font-weight: 800;}

	.text-blue{color: #1b00ff;}
	.text-dark{color: #000000;}
	.text-white{color: #ffffff;}
	.height-100-p{height: 100%;}
	.bg-white{background: #ffffff;}
	.border-radius-10{
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
		border-radius: 10px;
	}

	.border-radius-100{
		-webkit-border-radius: 100%;
		-moz-border-radius: 100%;
		border-radius: 100%;
	}

	.box-shadow{
		-webkit-box-shadow: 0px 0px 28px rgba(0, 0, 0, .08);
		-moz-box-shadow: 0px 0px 28px rgba(0, 0, 0, .08);
		box-shadow: 0px 0px 28px rgba(0, 0, 0, .08);
	}

	.gradient-style{
		background-image: linear-gradient( 135deg, #43CBFF 10%, #9708CC 100%);
	}
	.gradient-style2{
		background-image: linear-gradient( 135deg, #72EDF2 10%, #5151E5 100%);
	}
	.gradient-style3{
		background-image: radial-gradient( circle 732px at 96.2% 89.9%,  rgba(70,66,159,1) 0%, rgba(187,43,107,1) 92% );
	}
	.gradient-style4{
		background-image: linear-gradient( 135deg, #FF9D6C 10%, #BB4E75 100%);
	}

	.widget-style{
		padding: 20px 10px;
	}

	.widget-style .circle-icon{
		width: 60px;
	}

	.widget-style .circle-icon .icon{
		width: 60px;
		height: 60px;
		background: #ecf0f4;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.widget-style .widget-data{
		width: calc(100% - 150px);
		padding: 0 15px;
	}

	.widget-style .progress-data{
		width: 90px;
	}

	.widget-style .progress-data .apexcharts-canvas{
		margin: 0 auto;
	}

	.apexcharts-legend-marker{
		margin-right: 6px !important;
	}

	.wallet-icon {
		width: 60px;
	    height: 60px;
	    background: #28C76F !important;
	    background: rgba(40,199,111,.12) !important;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	}

	.users-icon {
		width: 60px;
	    height: 60px;
	    background: #00CFE8 !important;
	    background: rgba(0,207,232,.12) !important;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	}

	.course-icon {
		width: 60px;
	    height: 60px;
	    background: rgba(234,84,85,.12) !important;
	    color: #EA5455 !important;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	}

	.bg-advertisement {
		background: #1c325e;
	}

	.position-absolute {
	    position: absolute !important;
	    width: 20%;
	    right: 20px;
	    bottom: 10px;
	}
</style>
@endpush

@section('content')
<div class="container">
	<div class="row">
		<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-12 mb-4">
			<div class="bg-advertisement box-shadow border-radius-10 height-100-p widget-style">
				<div class="d-flex flex-wrap align-items-center">
					<div class="clearfix">
						<h5 class="text-white">Ajak Teman, Dapat Cashback.</h5>

						<span style="font-size: 12px;" class="text-white">Dapatkan cashback senilai Rp.1.500 untuk setiap teman yang mendaftar dengan Kode Referral mu.</span>

						<img src="https://preview.keenthemes.com/metronic8/demo1/assets/media/illustrations/sigma-1/17-dark.png" alt="preview-img" class="position-absolute">
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-12 mb-4">
			<div class="bg-white box-shadow border-radius-10 height-100-p widget-style">
				<div class="pl-3"><b>Statistik</b></div>

				<div class="row mt-3">
		            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-xl-0 pl-4">
		              <div class="d-flex flex-row">
		                <div class="circle-icon">
		                	<div class="course-icon border-radius-100 font-24 text-danger"><i class="fa fa-book" aria-hidden="true"></i></div>
		                </div>

		                <div class="ml-3 mt-2">
		                  <div class="fw-bolder mb-0">{{ $totalCourse }}</div>
		                  <p class="card-text font-small-3 mb-0">@lang('label.course_package')</p>
		                </div>
		              </div>
		            </div>

		            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-xl-0 pl-4">
		              <div class="d-flex flex-row">
		                <div class="circle-icon">
		                	<div class="users-icon border-radius-100 font-24 text-info"><i class="fa fa-users" aria-hidden="true"></i></div>
		                </div>

		                <div class="ml-3 mt-2">
		                  <div class="fw-bolder mb-0">{{ $totalSJoin }}</div>
		                  <p class="card-text font-small-3 mb-0">@lang('label.students_join')</p>
		                </div>
		              </div>
		            </div>

		            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-xl-0 pl-4">
		              <div class="d-flex flex-row">
		                <div class="circle-icon">
		                	<div class="wallet-icon border-radius-100 font-24 text-color"><i class="fa fa-wallet" aria-hidden="true"></i></div>
		                </div>

		                <div class="ml-3 mt-2">
		                  <div class="fw-bolder mb-0">Rp.213.000</div>
		                  <p class="card-text font-small-3 mb-0">Nominal Wallet</p>
		                </div>
		              </div>
		            </div>

	          	</div>
			</div>
		</div>
	</div>
</div>
@stop

@push('script')

@endpush