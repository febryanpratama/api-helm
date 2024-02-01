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
			                <th>@lang('label.student_name')</th>
			                <th>@lang('label.transaction_amount')</th>
			                <th>Detail Paket Kursus</th>
			                <th>Status Transaksi</th>
			                <th>Tanggal Checkout</th>
			                <th>@lang('label.option')</th>
			            </tr>
			        </thead>
			        <tbody>
			        	@foreach($transactions as $val)
			        		<tr>
			        			<td>#{{ $val->id }}</td>
			        			<td>{{ $val->user->name }}</td>
			        			<td>
			        				@if ($val->total_payment > 0)
				        				<b>{{ rupiah($val->total_payment) }}</b>
			        				@else
			        					<span class="badge badge-success">@lang('label.free')</span>
			        				@endif
			        			</td>
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
			        				@if ($val->status_payment == 0 && $nowDate <= $val->expired_transaction)
			        					<span class="badge badge-info text-white">{{ statusTransaction($val->status_payment) }}</span>
			        				@elseif ($val->status_payment == 1)
			        					<span class="badge badge-success">{{ statusTransaction($val->status_payment) }}</span>
			        				@else
			        					<span class="badge badge-danger">{{ statusTransaction(2) }}</span>
			        				@endif
			        			</td>
			        			<td>{{ $val->created_at->format('d F Y H:i') }}</td>
			        			<td>
			        				@if($val->status_payment == 0)
			        					<button class="btn btn-sm btn-primary approve-transaction" id="approve-transaction-{{ $val->id }}" transaction-id="{{ $val->id }}">Approve</button>
			        				@endif
			        			</td>
			        		</tr>
			        	@endforeach
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
<script>
	$(document).on('click', '.approve-transaction', function (e) {
		e.preventDefault()

		// Initialize
		let transactionId = $(this).attr('transaction-id')

		// Validate
		Swal.fire({
		    text: `Approve Transaksi dengan ID #${transactionId}?`,
		    icon: 'question',
		    showCancelButton: true,
		    confirmButtonColor: '#3085d6',
		    cancelButtonColor: '#d33',
		    cancelButtonText: 'Batal',
		    confirmButtonText: 'Oke'
		}).then((result) => {
		  if (result.isConfirmed) {
		    // Call Function
		    updateTransaction(transactionId)
		  }
		})
	})

	function updateTransaction(transactionId) {
		// Disabled Button True
		$(`#approve-transaction-${transactionId}`).attr('disabled', true)

		$.ajax({
		    url: `${baseUrl}/admin-panel/transaction/update/transaction/${transactionId}`,
		    type: 'POST',
		    headers: {'X-CSRF-TOKEN': `${csrfToken}`},
		    success: data => {
		    	if (!data.status) {
			        toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
			    	
			    	// Disabled Button False
			    	$(`#approve-transaction-${transactionId}`).attr('disabled', false)

			        return 0
		    	}

			    toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

			    location.reload()
		    },
		    error: e => {
		        console.log(e)

		        // Disabled Button False
		        $(`#approve-transaction-${transactionId}`).attr('disabled', false)

		        toastr.error(`500 Internal server Error!`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
		    }
		})
	}
</script>
@endpush