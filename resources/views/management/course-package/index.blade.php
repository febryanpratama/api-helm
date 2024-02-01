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
			<b>@lang('label.list_course_package')</b>

			{{-- <div class="float-right">
				<button class="btn btn-sm btn-company" data-toggle="modal" data-target="#search-transaction"><i class="fas fa-calendar"></i></button>
			</div> --}}
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="transaction-table">
			        <thead class="thead-color">
			            <tr>
			                <th>No</th>
			                <th>Nama Paket Kursus</th>
			                <th>Lembaga Kursus</th>
			                <th>Harga</th>
			                <th>Masa Aktif</th>
			                <th>Total Peserta</th>
			                <th>Opsi</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($courses as $val)
		        		<tr>
		        			<td>{{ $loop->iteration }}</td>
		        			<td>
		        				<a href="{{ route('member.course.show', $val['slug']) }}" class="text-color">{{ $val['name'] }}</a>
		        			</td>
		        			<td>{{ $val['company'] }}</td>
		        			<td>Rp. {{ $val['price'] }}</td>
		        			<td>
		        				{{ $val['periode'] }} {{ coursePeriode($val['periode_type']) }}
		        			</td>
		        			<td>
		        				<a href="{{ route('management.users.index', ['course' => $val['id'], 'partner' => $val['partner_id']]) }}" class="text-color">{{ $val['members'] }} Peserta</a>
		        			</td>
		        			<td>
		        				<a href="{{ route('management.users.index', ['course' => $val['id'], 'partner' => $val['partner_id']]) }}" class="btn btn-sm btn-primary"><i class="fas fa-user-plus"></i></a>
		        			</td>
		        		</tr>
			        	@endforeach
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{{-- {!! $transactions->links() !!} --}}
		</div>
	</div>
</div>
@stop

@push('script')

@endpush