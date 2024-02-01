<div class="modal fade" id="partner-modal" tabindex="-1" role="dialog" aria-labelledby="partner-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="partner-modal-aria"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        <div class="modal-body">
          {{-- Hidden Element --}}
          <input type="hidden" id="partner-action">
          <input type="hidden" id="partner-id" name="id">

          <div class="form-group">
            <label for="">Nama Mitra <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Ex: BANK" id="partner-name">
          </div>

          <div class="form-group">
            <label for="">PIC <span class="text-danger">*</span></label>
            <input type="text" name="pic" class="form-control" placeholder="Masukkan PIC" id="pic-name">
          </div>

          <div class="form-group">
            <label for="">Email PIC <span class="text-danger">*</span></label>
            <input type="text" name="email" class="form-control" placeholder="Masukkan Email PIC" id="pic-email">
          </div>

          <div class="form-group">
            <label for="">@lang('label.phone') PIC<span class="text-danger">*</span></label>
            <input type="number" name="phone" class="form-control" placeholder="@lang('label.enter_phone') PIC" id="pic-phone">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">@lang('label.cancel')</button>
          <button type="submit" class="btn btn-sm btn-company text-white" id="partner-btn-loading">@lang('label.add')</button>
        </div>
      </form>
    </div>
  </div>
</div>