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
	<div class="card card-custom">
		<div class="card-header bg-white">
			<b>@lang('label.transaction_list') - Online</b>

			{{-- <div class="float-right">
				<a href="{{ route('course.transaction.create') }}" class="btn btn-sm btn-company">@lang('label.add_transaction')</a>
			</div> --}}
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>No</th>
			                <th>Nama Murid</th>
			                <th>Paket Kursus</th>
			                <th>Nominal Transaksi</th>
			                <th>Detail Paket Kursus</th>
			                <th>Status</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@forelse($transactions as $val)
			        	@php
			        		// Initialize
	                        $commissionFormula = $val->original_price_course - (($val->apps_commission/100) * $val->original_price_course);
			        	@endphp
		        		<tr>
		        			<td>{{ $loop->iteration }}</td>
		        			<td>{{ $val->user->name }}</td>
		        			<td>{{ $val->course->name }}</td>
		        			<td>{{ rupiah($commissionFormula) }}</td>
		        			<td>
		        				<ul>
		        					<li><b>Tanggal Aktif</b> : {{ ($val->course_start) ? date('d F Y', strtotime($val->course_start)) : '-' }}</li>
		        					<li><b>Tanggal Berakhir</b> : {{ ($val->expired_course && $val->expired_course != '0000-00-00') ? date('d F Y', strtotime($val->expired_course)) : '-' }}</li>
		        					<li><b>Masa Aktif Kursus</b> : {{ $val->course_periode.' '.coursePeriode($val->course_periode_type) }}</li>
		        				</ul>
		        			</td>
		        			<td>
		        				@if ($val->checkout->status_payment == 0 && $nowDate <= $val->checkout->expired_transaction)
		        					<span class="badge badge-info text-white">{{ statusTransaction($val->checkout->status_payment) }}</span>
		        				@elseif ($val->checkout->status_payment == 1)
		        					<span class="badge badge-success">{{ statusTransaction($val->checkout->status_payment) }}</span>
		        				@else
		        					<span class="badge badge-danger">{{ statusTransaction(2) }}</span>
		        				@endif
		        			</td>
		        		</tr>
			        	@empty
			        	<tr>
			        		<td colspan="6" class="text-center">@lang('label.no_data')</td>
			        	</tr>
			        	@endif
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{!! $transactions->links() !!}
		</div>
	</div>
</div>
@stop

@push('script')

@endpush