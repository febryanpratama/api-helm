<div class="modal fade" id="users-modal" tabindex="-1" role="dialog" aria-labelledby="users-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="users-modal-aria"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        <div class="modal-body">
          {{-- Hidden Input --}}
          <input type="hidden" id="user-id" name="id">
          <input type="hidden" id="user-action">

          <div class="form-group">
            <label for="">Nama</label> <span class="text-danger">*</span>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan Nama">
          </div>

          <div class="form-group">
            <label for="">Email</label> <span class="text-danger">*</span>
            <input type="text" class="form-control" id="email" name="email" placeholder="Masukkan Email">
          </div>

          <div class="form-group">
            <label for="">Nomor Telepon</label>
            <input type="number" class="form-control" id="phone" name="phone" placeholder="Masukkan Nomor Telepon">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">@lang('label.cancel')</button>
          <button type="submit" class="btn btn-sm btn-company text-white" id="users-btn-loading">@lang('label.add')</button>
        </div>
      </form>
    </div>
  </div>
</div>