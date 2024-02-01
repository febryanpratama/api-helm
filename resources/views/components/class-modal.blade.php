<div class="modal fade" id="class-modal" tabindex="-1" role="dialog" aria-labelledby="class-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="class-modal-aria"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        <div class="modal-body">
          {{-- Hidden Input --}}
          <input type="hidden" id="class-id">
          <input type="hidden" id="class-action">

          <div class="form-group">
            <label for="">@lang('label.session_name')</label> <span class="text-danger">*</span>
            <input type="text" class="form-control" name="name" id="class-name" placeholder="@lang('label.enter_session_name')">
          </div>

          <div class="form-group">
            <label for="">@lang('label.session_detail')</label> <span class="text-danger">*</span>
            <textarea name="details" id="class-details" cols="10" rows="3" class="form-control tinymce-element" placeholder="@lang('label.enter_session_detail')"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">@lang('label.cancel')</button>
          <button type="submit" class="btn btn-sm btn-company text-white" id="class-btn-loading">@lang('label.add')</button>
        </div>
      </form>
    </div>
  </div>
</div>