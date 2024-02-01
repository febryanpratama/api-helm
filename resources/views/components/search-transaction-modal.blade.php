<div class="modal fade" id="search-transaction" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">@lang('label.search_transaction')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" method="GET">
		<div class="modal-body">
			<div class="row">
				<div class="col-6">
					<div class="form-group">
						<label for="">@lang('label.from_date')</label>
						<input type="date" class="form-control" name="from_date" value="{{ (request('from_date')) ? request('from_date') : '' }}">
					</div>
				</div>

				<div class="col-6">
					<div class="form-group">
						<label for="">@lang('label.till_date')</label>
						<input type="date" class="form-control" name="till_date" value="{{ (request('till_date')) ? request('till_date') : '' }}">
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-sm btn-company">@lang('label.search')</button>
		</div>
      </form>
    </div>
  </div>
</div>