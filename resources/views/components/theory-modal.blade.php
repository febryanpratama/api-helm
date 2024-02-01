<div class="modal fade" id="theory-modal" tabindex="-1" role="dialog" aria-labelledby="theory-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="theory-modal-aria"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        <div class="modal-body">
          {{-- Hidden Input --}}
          <input type="hidden" id="theory-id">
          <input type="hidden" id="theory-action">
          <input type="hidden" id="majors-id" name="majors_id">

          <div class="form-group">
            <label for="">@lang('label.theory_name')</label> <span class="text-danger">*</span>
            <input type="text" class="form-control" id="theory-name" name="Name" placeholder="@lang('label.enter_theory_name')">
          </div>

          <div class="form-group">
            <label for="">@lang('label.material_files') <i class="text-info">(PDF, MP4 dan MKV)</i></label> <span class="text-danger">*</span> <br>
            <button class="btn btn-info btn-sm text-white" id="theory-btn-file" type="button"><i class="fa fa-camera"></i></button>
            <span id="span-name-file-theory" class="pl-2"><i>*@lang('label.not_file_selected')</i></span>
            <input type="file" name="upload_file" class="form-control hide-element" id="file-theory" accept="application/pdf,video/*">
          </div>

          <div class="clearfix" id="progress-bar-uploaded-area" style="display: none;">
            <hr>

            <div class="progress">
              <div class="progress-bar progress-bar-striped" role="progressbar" id="progress-bar-uploaded" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">@lang('label.upload_process') 0%</div>
            </div>

            <div class="alert alert-info mt-3 text-center">
              <i class="fas fa-warning"></i> @lang('label.alert_upload_process')
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">@lang('label.cancel')</button>
          <button type="submit" class="btn btn-sm btn-company text-white" id="theory-btn-loading">@lang('label.add')</button>
        </div>
      </form>
    </div>
  </div>
</div>