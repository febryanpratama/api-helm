<div class="modal fade" id="course-modal" tabindex="-1" role="dialog" aria-labelledby="course-modal-aria" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="course-modal-aria"></h5>
        <button type="button" class="close" id="close-add-course" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        {{-- Hidden Element --}}
        <input type="hidden" id="course-id-form">
        <input type="hidden" id="action-form">

        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="">@lang('label.name_course_package')</label> <span class="text-danger">*</span>
                <input type="text" class="form-control" id="course-name" name="name" placeholder="Masukkan Nama Kursus">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="">@lang('label.description')</label> <span class="text-danger">*</span>
                <textarea name="description" id="course-description" cols="10" rows="3" class="form-control tinymce-element" placeholder="Masukkan Deskripsi Kursus"></textarea>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-md-9 col-lg-9 col-xl-9 col-12">
              <div class="form-group">
                <label for="">@lang('label.course_package_validity_period') <span id="detail-periode"></span></label> <span class="text-danger">*</span>
                <input type="number" class="form-control" id="course-periode" name="periode" placeholder="@lang('label.enter_course_package_validity_period')">
              </div>
            </div>

            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-12" style="padding-top: 2rem;">
              <div class="form-group">
                {{-- <label for="">Periode</label> <span class="text-danger">*</span> --}}
                <select name="periode_type" id="periode-type" class="form-control">
                  {{-- <option value="0">- Pilih -</option> --}}
                  <option value="1">Mingguan</option>
                  <option value="2">Bulanan</option>
                  <option value="3">Tahunan</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
                <div class="form-group">
                  <label for="">@lang('label.course_package_type') <i class="fas fa-question-circle text-color cursor-area config-tooltip" data-toggle="tooltip" data-placement="top" title="@lang('label.is_public_description')"></i></label>
                  
                  <select name="is_private" id="is-private" class="form-control">
                    <option value="0">Publik</option>
                    <option value="1">Pribadi</option>
                  </select>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 is_public">
              <div class="form-group">
                <label for="">
                  @lang('label.course_package_rate_type')

                  <span id="commissio-text">
                    (<i>* @lang('label.teaching_room_commission') <span class="text-color">5%</span></i>)
                  </span>
                </label>
                
                <select name="course_type" id="course-type" class="form-control">
                  <option value="1">@lang('label.paid')</option>
                  <option value="2">@lang('label.free')</option>
                </select>
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 is_private" style="display: none;">
                <label for="">@lang('label.commission_type')</label> <span class="text-danger">*</span>
                <select name="commission_type" id="commission-type" class="form-control">
                  <option value="0">Pembagian Komisi Per Peserta Bergabung (5%)</option>
                  <option value="1">Pembagian Hasil Total</option>
                </select>
            </div>
          </div>

          <div class="row commission_type_1" style="display: none;">
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <div class="form-group">
                <label for="">@lang('label.min_user_joined')</label> <span class="text-danger">*</span>
                <input type="number" name="min_user_joined" id="min-user-joined" class="form-control" placeholder="@lang('label.just_enter_number')">
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <div class="form-group">
                <label for="">@lang('label.commission_min_user_joined') (%)</label> <span class="text-danger">*</span>
                <input type="number" name="commission_min" id="commission-min-user-joined" class="form-control" placeholder="@lang('label.just_enter_number')">
              </div>
            </div>
          </div>

          <div class="row commission_type_1" style="display: none;">
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <div class="form-group">
                <label for="">@lang('label.max_user_joined')</label> <span class="text-danger">*</span>
                <input type="number" name="max_user_joined" id="max-user-joined" class="form-control" placeholder="@lang('label.just_enter_number')">
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <label for="">@lang('label.commission_max_user_joined') (%)</label> <span class="text-danger">*</span>
              <input type="number" name="commission_max" id="commission-max-user-joined" class="form-control" placeholder="@lang('label.just_enter_number')">
            </div>
          </div>

          <div class="is_private" style="display: none;">
            <hr>
          </div>

          <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12" id="course-price-area">
              <div class="form-group">
                <label for="">@lang('label.price') (Rp)</label> <span class="text-danger">*</span>
                <input type="text" class="form-control" id="course-price" name="price" placeholder="Masukkan Harga Kursus">
                <span id="nominal-accepted"></span>
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <div class="form-group">
                <label for="">@lang('label.thumbnail')</label> <br>
                <button class="btn btn-info btn-sm text-white" id="course-btn-file" type="button"><i class="fa fa-camera"></i></button>
                <span id="span-name-file-course" class="pl-2"><i>*Tidak ada file yang dipilih</i></span>
                <input type="file" name="upload_file" class="form-control hide-element" id="file-course" accept="image/png,image/jpg,image/jpeg,video/mp4,video/avi,application/pdf">
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <label for="">@lang('label.course_package_category')</label>
              <select name="category[]" id="category-id" class="form-control category-area" placeholder="" multiple="multiple" style="width: 100%;">
                <option value="">--- @lang('label.select_category') ---</option>
              </select>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12">
              <label for="">@lang('label.course_package_category_type')</label> <span class="text-danger">*</span>
              <select name="course_package_category" id="course-package-category" class="form-control" placeholder="">
                <option value="0">Program Paket Kursus</option>
                <option value="1">Program Magang</option>
                <option value="2">Program Kerja</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">@lang('label.cancel')</button>
          <button type="submit" class="btn btn-sm btn-company text-white" id="course-btn-loading">@lang('label.add')</button>
        </div>
      </form>
    </div>
  </div>
</div>