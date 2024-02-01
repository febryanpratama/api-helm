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
			<b>Detail Invoice - {{ $course->name }}</b>

			@if (!$paidStatus)
				<div class="float-right">
					<a href="{{ route('management.checkout.index', $course->id) }}" class="btn btn-sm btn-company">Bayar Invoice</a>
				</div>
			@elseif ($paidStatus && $paidStatus->status == 0)
				<div class="float-right">
					<span class="text-dark"><b>Status : Menunggu Pembayaran</b></span>
					<div class="clearfix">
						<a href="{{ route('management.checkout.show', $paidStatus->id) }}" class="text-info">Klik Disini Untuk Melihat Cara Pembayaran</a>
					</div>
				</div>
			@else
				<div class="float-right">
					<span class="text-success"><b>Dibayar</b></span>
				</div>
			@endif
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>No</th>
			                <th>Nama Peserta</th>
			                <th>Harga</th>
			                <th>Kode Unik</th>
			                <th>Total Pembayaran</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@php
			        		// Initialize
			        		$totals = 0;
			        	@endphp

			        	@foreach($checkout as $val)
			        	@php
			        		// Initialize
			        		$totals += $val->total_payment;
			        	@endphp
		        		<tr>
		        			<td>{{ $loop->iteration }}</td>
		        			<td>{{ $val->username }}</td>
		        			<td>Rp.{{ $val->price_course }}</td>
		        			<td>{{ $val->unique_code }}</td>
		        			<td>{{ rupiah($val->total_payment) }}</td>
		        		</tr>
			        	@endforeach
			        	<tr>
			        		<td colspan="4" class="text-right"><b>Total</b></td>
			        		<td colspan="1"><b>{{ rupiah($totals) }}</b></td>
			        	</tr>
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
		</div>
	</div>
</div>
@stop

@push('script')

@endpush