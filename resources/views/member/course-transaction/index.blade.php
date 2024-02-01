@extends('layouts.master')

@push('style')
<style>
	.thead-color {
		background-color: #f6f9fc !important;
	}
</style>
@endpush

@section('content')
<div class="container mb-4">
	@if ($waitingPayment)
	<div class="alert alert-info text-center">
		<i class="fa fa-info-circle"></i> @lang('label.alert_not_yed_paid', ['name' => auth()->user()->name, 'countData' => $waitingPayment])
	</div>
	@endif

	<div class="card card-custom">
		<div class="card-header bg-white">
			<b>@lang('label.transaction_list')</b>

			<div class="float-right">
				<button class="btn btn-sm btn-company" data-toggle="modal" data-target="#search-transaction"><i class="fas fa-calendar"></i></button>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>#ID</th>
			                <th>@lang('label.course_package')</th>
			                <th>@lang('label.transaction_amount')</th>
			                <th>@lang('label.unique_code')</th>
			                <th>@lang('label.status')</th>
			                <th>@lang('label.option')</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@forelse($transactions as $val)
			        	<tr>
			        		<td>#{{ $val->id }}</td>
			        		<td>
		        				<ul>
			        				@foreach($val->checkoutDetail as $cd)
		        					<li>
		        						{{ $cd->course_name }}

		        						@if ($cd->original_price_course > 0)
		        							- <b>{{ rupiah($cd->original_price_course) }}</b>
		        						@else
		        							<span class="badge badge-success">@lang('label.free')</span>
		        						@endif

		        						<ul>
		        							<li><b>@lang('label.active_date')</b> : {{ ($cd->course_start) ? date('d F Y', strtotime($cd->course_start)) : '-' }}</li>
		        							<li><b>@lang('label.end_date')</b> : {{ ($cd->expired_course && $cd->expired_course != '0000-00-00') ? date('d F Y', strtotime($cd->expired_course)) : '-' }}</li>
		        							<li><b>@lang('label.course_package_active_period')</b> : {{ $cd->course_periode.' '.coursePeriode($cd->course_periode_type) }}</li>
		        						</ul>
		        					</li>
			        				@endforeach
		        				</ul>
			        		</td>
			        		<td>
			        			@if ($val->total_payment > 0)
			        				{{ rupiah($val->total_payment) }}
			        			@else
			        				<span class="badge badge-success">@lang('label.free')</span>
			        			@endif
			        		</td>
			        		<td>{{ $val->unique_code }}</td>
			        		<td>
			        			@if ($val->status_payment == 0 && $nowDate <= $val->expired_transaction)
			        				<span class="badge badge-info text-white">{{ statusTransaction($val->status_payment) }}</span>
			        			@elseif ($val->status_payment == 1)
			        				<span class="badge badge-success">{{ statusTransaction($val->status_payment) }}</span>
			        			@else
			        				<span class="badge badge-danger">{{ statusTransaction(2) }}</span>
			        			@endif
			        		</td>
			        		<td>
			        			@if ($val->status_payment == 0 && $nowDate <= $val->expired_transaction)
				        			<a href="{{ route('member.checkout.show', $val->id) }}" class="btn btn-sm btn-info text-white">Detail</a>
				        		@else
				        			-
				        		@endif
			        		</td>
			        	</tr>
			        	@empty
			        	<tr>
			        		<td colspan="6" class="text-center">@lang('label.no_data')</td>
			        	</tr>
			        	@endforelse
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{!! $transactions->links() !!}
		</div>
	</div>
</div>

{{-- Include File --}}
@include('components.search-transaction-modal')
@stop

@push('script')

@endpush