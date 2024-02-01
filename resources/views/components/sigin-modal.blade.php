<div class="modal fade" id="sigin-modal" tabindex="-1" role="dialog" aria-labelledby="sigin-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      {{-- <div class="modal-header">
        <h5 class="modal-title" id="sigin-modal-aria">Isi Data Untuk Melanjutkan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> --}}
      <div class="modal-body">
        <div class="" style="position: absolute; right: 15px;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div id="sigin-modal-area">
          <form method="POST">
            <div class="row justify-content-center mt-4">
              <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 text-center">
                <b>Isi Data Untuk Melanjutkan</b>
              </div>
              
                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mt-4">
                    <div class="form-group">
                      <label for="">Email</label> <span class="text-danger">*</span>
                      <input type="email" placeholder="Masukkan Email" name="email" class="form-control" id="sigin-email">
                    </div>

                    <div class="form-group">
                      <input type="checkbox"> Ingat Saya
                    </div>
                </div>

                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                  <button class="btn btn-primary w-100" type="submit" id="sigin-btn">MASUK</button>
                </div>
            </div>
          </form>
        </div>

        <div id="verify-otp-modal-area" style="display: none;">
          <div class="row justify-content-center mt-4">
            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 text-center">
              <b>Masukkan Kode OTP</b>
            </div>

            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mt-4">
              <form method="POST">
                <div class="row justify-content-center">
                  <div class="col-2">
                    <input type="text" id="otp-one" next="otp-two" prev="first-otp" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>

                  <div class="col-2">
                    <input type="text" id="otp-two" next="otp-three" prev="otp-one" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>

                  <div class="col-2">
                    <input type="text" id="otp-three" next="otp-four" prev="otp-two" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>

                  <div class="col-2">
                    <input type="text" id="otp-four" next="lastest-otp" prev="otp-three" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>
                  
                  <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mt-4">
                    <button class="btn btn-primary w-100" type="submit" id="verify-btn" disabled="">VERIFIKASI</button>
                  </div>

                  <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 pt-3 text-center">
                    <div class="form-group">
                      <label for="" style="font-size: 12px;">KODE OTP telah dikirim melalui email</label>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>