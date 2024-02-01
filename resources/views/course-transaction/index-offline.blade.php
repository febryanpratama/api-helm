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
			<b>@lang('label.transaction_list') - Offline</b>

			<div class="float-right">
				<a href="{{ route('course.transaction.create') }}" class="btn btn-sm btn-company">@lang('label.add_transaction')</a>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>#INV</th>
			                <th>Tanggal Transaksi</th>
			                <th>Data Customer</th>
			                <th>Item</th>
			                <th>Nominal Transaksi</th>
			                <th>Status</th>
			                <th>Tipe Pembayaran</th>
			                <th>Opsi</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@forelse($transactions as $val)
			        	<tr>
			        		<td>{{ $val->inv_code }}</td>
			        		<td>{{ $val->created_at->format('d/M/Y') }} {{ $val->created_at->format('H:i') }}</td>
			        		<td>
			        			<ul>
			        				<li><b>Nama</b> : {{ ($val->customer_name) ? $val->customer_name : 'N/A' }}</li>
			        				<li><b>Email</b> : {{ ($val->customer_email) ? $val->customer_email : '-' }}</li>
			        				<li><b>Nomor Telepon</b> : {{ ($val->customer_telepon) ? $val->customer_telepon : '-' }}</li>
			        			</ul>
			        		</td>
			        		<td>
			        			<ul>
			        				@foreach($val->checkoutDetail as $cd)
			        				<li>{{ $cd->course_name }}</li>
			        				@endforeach
			        			</ul>
			        		</td>
			        		<td>{{ rupiah($val->total_payment) }}</td>
			        		<td>
			        			@if($val->expired_transaction <= $nowDate && $val->status_payment == 2)
				        			<div class="badge badge-danger text-white">
					        			Expired
				        			</div>
			        			@elseif ($val->expired_transaction >= $nowDate && $val->status_payment == 2)
    			        			<div class="badge badge-info text-white">
    				        			Belum Lunas
    			        			</div>
			        			@else
			        				<div class="badge badge-success">
			        					Lunas
			        				</div>
		        				@endif
			        		</td>
			        		<td>{{ paymentType($val->payment_type) }}</td>
			        		<td>
			        			<a href="{{ route('offline.transaction.show', $val->id) }}" class="btn btn-sm btn-primary" target="_blank">Detail</a>
			        		</td>
			        	</tr>
			        	@empty
			        	<tr>
			        		<td colspan="8" class="text-center">@lang('label.no_data')</td>
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
@stop

@push('script')

@endpush