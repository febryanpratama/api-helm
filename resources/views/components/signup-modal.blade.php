<div class="modal fade" id="signup-modal" tabindex="-1" role="dialog" aria-labelledby="signup-modal-aria" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="signup-modal-aria">Daftar Akun</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        {{-- Hidden Element --}}
        <input type="hidden" name="is_instructor" id="is-instructor">

        <div class="modal-body">
          <div id="form-input-element">
            <div class="form-group text-center">
              <label for=""><h5><b>Pilih Tipe Akun</b></h5></label>
            </div>

            <div class="row justify-content-center">
              <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-12">
                <div class="clearfix border h-100 cursor-area choose" id="instructor">
                  <img src="https://img.freepik.com/free-vector/online-class-illustration_52683-42415.jpg?w=826&t=st=1653056982~exp=1653057582~hmac=43d7a8967c3e25ecccb1eeab8b2a5638db83a8e4d47e82ca0b10acfe44ece490" alt="instructor-img">

                  <div class="clearfix text-center mt-2 mb-2">
                    <b>Mentor</b>
                  </div>
                </div>
              </div>

              <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-12">
                <div class="clearfix border h-100 cursor-area choose" id="student">
                  <img src="https://img.freepik.com/free-vector/study-abroad-concept-illustration_114360-7493.jpg?t=st=1653054928~exp=1653055528~hmac=7395630ae04f1b1f3694fff08b6198fe60da0b2da4b31e00a1a03091681dc5ce&w=826" alt="instructor-img">

                  <div class="clearfix text-center mt-2 mb-2">
                    <b>Pelajar</b>
                  </div>
                </div>
              </div>
            </div>

            <div class="row justify-content-center">
              <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                <div class="form-group mt-4">
                  <label for="">Email</label> <span class="text-danger">*</span>
                  <input type="email" placeholder="Masukkan Email" name="email" class="form-control" id="signup-email">
                </div>
              </div>

              <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                <div class="form-group">
                  <label for="">Kode Referral</label>
                  <input type="text" placeholder="Masukkan Kode Referral" name="referral_code" class="form-control" id="referral-code">
                </div>
              </div>

              <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                <button class="btn btn-primary w-100" type="submit" id="signup-btn">DAFTAR</button>
              </div>

              <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 pt-3">
                <div class="form-group">
                  <label for="" style="font-size: 12px;">Dengan mendaftar, Anda menyetujui <a href="javascript:void(0)" class="text-color">Ketentuan Penggunaan dan Kebijakan Privasi kami</a>.</label>
                </div>
              </div>
            </div>
          </div>

          <div id="signup-success">
            
          </div>
        </div>
      </form>
    </div>
  </div>
</div>