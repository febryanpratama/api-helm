<div class="modal fade" id="pay-offline-transaction-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Selesaikan Pembayaran</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
				<div class="modal-body">
					{{-- Hidden Element --}}
					<input type="hidden" id="total-payment" name="total_payment">

					<div class="form-group">
						<label for="">Nama Pelanggan</label>
						<input type="text" class="form-control" placeholder="Masukkan Nama Pelanggan" name="customer_name" id="customer-name">
					</div>

					<div class="form-group">
						<label for="">Email</label>
						<input type="text" class="form-control" placeholder="Masukkan Email" name="customer_email" id="customer-email">
					</div>

					<div class="form-group">
						<label for="">Nomor Telepon</label>
						<input type="text" class="form-control" placeholder="Masukkan Nomor Telepon" name="customer_telepon" id="customer-telepon">
					</div>
					
					<div class="form-group">
						<label for="">Tipe Pembayaran</label> <span class="text-danger">*</span>
						<select name="payment_type" id="payment-type" class="form-control">
							<option value="">-- Pilih --</option>
							<option value="1">Bank Transfer</option>
							<option value="2">E-Money</option>
							<option value="3">Cash</option>
							<option value="4">Debit</option>
							<option value="5">Credit</option>
						</select>
					</div>

					<div class="clearfix" id="bank-transfer" style="display: none;">
						<label for="">@lang('label.payment_method')</label> <span class="text-danger">*</span>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="BCA|6801221367"><b> Bank BCA</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="BRI|039301001433302"><b> Bank BRI</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="MANDIRI|1640015155155"><b> Bank MANDIRI</b>
						</div>
					</div>

					<div class="clearfix" id="e-money" style="display: none;">
						<label for="">@lang('label.payment_method')</label> <span class="text-danger">*</span>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="OVO|081285365902"><b> OVO</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="Go Pay|081285365902"><b> Go Pay</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="Shopee Pay|081285365902"><b> Shopee Pay</b>
						</div>

						<div class="clearfix border p-2 mb-2">
							<input type="radio" name="bank" id="" value="DANA|081285365902"><b> DANA</b>
						</div>
					</div>

					<div class="clearfix" id="debit-credit" style="display: none;">
						<div class="form-group">
							<label for="">Nama Penerbit Kartu</label> <span class="text-danger">*</span>
							<input type="text" placeholder="Masukkan Nama Penerbit Kartu" class="form-control" id="publisher-name" name="publisher_name">
						</div>

						<div class="form-group">
							<label for="">Nomor Kartu</label> <span class="text-danger">*</span>
							<input type="text" placeholder="Masukkan Nomor Kartu" class="form-control" id="card-nomor" name="card_nomor">
						</div>
					</div>
				</div>
				<div class="modal-footer">
		      <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">@lang('label.close')</button>
		      <button type="submit" class="btn btn-sm btn-company" id="btn-pay-loading">Simpan</button>
				</div>
      </form>
    </div>
  </div>
</div>