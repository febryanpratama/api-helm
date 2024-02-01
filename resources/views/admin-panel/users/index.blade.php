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
			<b>@lang('label.users_list') (Dummy)</b>

			{{-- <div class="float-right">
				<button class="btn btn-sm btn-company" data-toggle="modal" data-target="#search-transaction"><i class="fas fa-calendar"></i></button>
			</div> --}}
		</div>
		<div class="card-body">
			<div class="table-responsive">
			    <table class="table table-bordered" id="users-table">
			        <thead class="thead-color">
			            <tr>
			                <th>No</th>
			                <th>@lang('label.name')</th>
			                <th>@lang('label.email')</th>
			                <th>Role</th>
			                <th>OTP</th>
			                <th>@lang('label.join_date')</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($users as $val)
			        	<tr>
			        		<td>{{ $loop->iteration }}</td>
			        		<td>{{ $val->name }}</td>
			        		<td>{{ $val->email }}</td>
			        		<td>{{ $val->role->Name }}</td>
			        		<td>{{ $val->otp }}</td>
			        		<td>{{ $val->created_at->format('d F Y H:i') }}</td>
			        	</tr>
			        	@endforeach
			        </tbody>
			    </table>
			</div>
		</div>
		<div class="card-footer bg-white">
			{!! $users->links() !!}
		</div>
	</div>
</div>
@stop

@push('script')

@endpush