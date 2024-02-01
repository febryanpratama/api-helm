@extends('layouts.app')

@push('style')
<style>
  .instagram {
    background: #f09433; 
    background: -moz-linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); 
    background: -webkit-linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); 
    background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); 
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f09433', endColorstr='#bc1888',GradientType=1 );
    border: none;
  }

  .active-select-type-account {
      box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
      border: 2px solid #62ddbd !important;
  }
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="signup-url" value="{{ route('auth.signup.post') }}">
<input type="hidden" id="verify-otp-url" value="{{ route('auth.otp_verify') }}">

<div class="container">
	<div class="row justify-content-center">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 mt-5">
			<div class="card card-custom">
        <div id="signup-form-area">
          <form method="POST">
            {{-- Hidden Element --}}
            <input type="hidden" name="is_instructor" id="is-instructor">

            <div id="signup-form-input">
              <div class="form-group text-center mt-4">
                <label for=""><h5><b>Pilih Tipe Akun</b></h5></label>
              </div>

              <div class="row justify-content-center">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-12">
                  <div class="clearfix border cursor-area choose" id="instructor">
                    <img src="https://img.freepik.com/free-vector/online-class-illustration_52683-42415.jpg?w=826&t=st=1653056982~exp=1653057582~hmac=43d7a8967c3e25ecccb1eeab8b2a5638db83a8e4d47e82ca0b10acfe44ece490" style="width: 100%;" alt="instructor-img">

                    <div class="clearfix text-center mt-2 mb-2">
                      <b>Mentor</b>
                    </div>
                  </div>
                </div>

                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-12">
                  <div class="clearfix border cursor-area choose" id="student">
                    <img src="https://img.freepik.com/free-vector/study-abroad-concept-illustration_114360-7493.jpg?t=st=1653054928~exp=1653055528~hmac=7395630ae04f1b1f3694fff08b6198fe60da0b2da4b31e00a1a03091681dc5ce&w=826" style="width: 100%;" alt="instructor-img">

                    <div class="clearfix text-center mt-2 mb-2">
                      <b>Murid</b>
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
                    <label for="" style="font-size: 12px;">Dengan mendaftar, Anda menyetujui <a href="{{ asset('docs/sk-ruangajar-com.docx') }}" target="_blank" class="text-color">Ketentuan Penggunaan</a> dan <a href="{{ asset('docs/kp-ruangajar-com.docx') }}" target="_blank" class="text-color">Kebijakan Privasi</a> kami.</label>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div id="verify-otp-area" style="display: none;">
          <div class="row justify-content-center mt-4">
            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 text-center">
              <div class="alert alert-success" id="success-register-alert">Pendaftaran Berhasil, Masukkan Kode OTP yang telah dikirim melalui Email untuk melanjutkan. </div>
            </div>

            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 text-center">
              <b>Kode OTP</b>
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
                      <a href="javascript:void(0)" class="text-color" id="resend-otp"><b>Resend OTP <span id="resend-time"></span></b></a>
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
@stop

@push('script')
{{-- SignUp --}}
<script>
    $(document).on('click', '.choose', function (e) {
        if ($(this).attr('id') == 'instructor') {
            $('#student').removeClass('active-select-type-account')
            $('#instructor').addClass('active-select-type-account')
            $('#is-instructor').val('1|1')
        } else {
            $('#student').addClass('active-select-type-account')
            $('#instructor').removeClass('active-select-type-account')
            $('#is-instructor').val('0|6')
        }
    })

    $(document).on('submit', '#signup-form-area form', function (e) {
        e.preventDefault()

        // Initialize
        let url         = $('#signup-url').val()
        let accountType = $('#is-instructor').val()
        let email       = $('#signup-email').val()
        let countdown   = []

        // Validate
        if (!accountType) {
            toastr.error(`Tipe Akun harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

            return 0
        }

        if (!email) {
            toastr.error(`Email harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

            return 0
        }

        for (let cT = 60; cT >= 1; cT--) {
          countdown.push(cT)
        }

        // Disabled Button True
        $('#signup-btn').attr('disabled', true)

        $.ajax({
            url: `${url}`,
            type: 'POST',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            success: data => {
              // Disabled Button False
              $('#signup-btn').attr('disabled', false)

              if (!data.status) {
                  toastr.error(`${data.message}`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

                  return 0
              }

              // DOM Manipulation
              $('#signup-form-input').css('display', 'none')
              $('#verify-otp-area').css('display', '')

              for (let i = 0; i <= (countdown.length); i++) {
                setTimeout(function () {
                  $('#resend-time').html(`(${countdown[i]} Detik)`)

                  if (i == 60) {
                    $('#resend-time').html(``)
                    $('#resend-time').attr('a', '')
                    $('#resend-otp').addClass('resend-otp-code')
                  }
                }, 1000 * i)
              }
            },
            error: e => {
                console.log(e)

                // Disabled Button False
                $('#signup-btn').attr('disabled', false)

                toastr.error(`Pendaftaran Gagal silahkan coba kembali`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
            }
        })
    })
</script>

{{-- Verify Otp --}}
<script>
  $(document).on('keyup', '.otp-input', function (e) {
      // Initialize
      let otpOne   = $('#otp-one').val()
      let otpTwo   = $('#otp-two').val()
      let otpThree = $('#otp-three').val()
      let otpFour  = $('#otp-four').val()

      // Validate
      if (this.value) {
          $(`#${$(this).attr('next')}`).focus()
      } else {
          if (e.keyCode == 8) {
              $(`#${$(this).attr('prev')}`).focus()
          }
      }

      if (otpOne && otpTwo && otpThree && otpFour) {
          // Disabled Button False
          $('#verify-btn').attr('disabled', false)
      } else {
          // Disabled Button True
          $('#verify-btn').attr('disabled', true)
      }
  })

  function clickEvent(first,last){
      if(first.value.length){
          document.getElementById(last).focus();
      }
  } 

  $(document).on('submit', '#verify-otp-area form', function (e) {
      e.preventDefault()

      // Initialize
      let url      = $('#verify-otp-url').val()
      let email    = $('#signup-email').val()
      let otpOne   = $('#otp-one').val()
      let otpTwo   = $('#otp-two').val()
      let otpThree = $('#otp-three').val()
      let otpFour  = $('#otp-four').val()

      // Validate
      if (!otpOne || !otpTwo || !otpThree || !otpFour) {
          toastr.error(`Kode OTP harus diisi`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

          return 0
      }

      // Disabled Button True
      $('#verify-btn').attr('disabled', true)

      // Initialize
      const fd = new FormData(this)
      fd.append('email', email)

      $.ajax({
          url: `${url}`,
          type: 'POST',
          headers: {'X-CSRF-TOKEN': `${csrfToken}`},
          data: fd,
          contentType: false,
          cache: false,
          processData: false,
          dataType: 'json',
          success: data => {
              // Disabled Button False
              $('#verify-btn').attr('disabled', false)
              
              if (!data.status) {
                  toastr.error(`${data.message}`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})

                  return 0
              }

              $('#sigin-modal').modal('hide')

              toastr.success(`${data.message}`, 'Sukses!', {closeButton:!0, tapToDismiss:!1, rtl:o})

              window.location = `${baseUrl}/${(data.redirect) ? data.redirect : 'dashboard' }`
          },
          error: e => {
              console.log(e)

              // Disabled Button False
              $('#verify-btn').attr('disabled', false)

              toastr.error(`Gagal melakukan login, silahkan coba kembali`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
          }
      })
  })

  $(document).on('click', '.resend-otp-code', function () {
    // Initialize
    let email     = $('#signup-email').val()
    let countdown = []

    for (let cT = 60; cT >= 1; cT--) {
      countdown.push(cT)
    }

    // Remove Class
    $(this).removeClass('resend-otp-code')

    $.ajax({
        url: `${baseUrl}/auth/signin/verify`,
        type: 'POST',
        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
        data: {
          email: email
        },
        success: data => {
          for (let i = 0; i <= (countdown.length); i++) {
            setTimeout(function () {
              $('#resend-time').html(`(${countdown[i]} Detik)`)

              if (i == 60) {
                $('#resend-time').html(``)
                $('#resend-time').attr('a', '')
                $('#resend-otp').addClass('resend-otp-code')
              }
            }, 1000 * i)
          }

          $('#success-register-alert').html('Kode OTP telah dikirim ulang')
        },
        error: e => {
            console.log(e)

            // Disabled Button False
            $('#signup-btn').attr('disabled', false)

            toastr.error(`OTP Gagal diminta`, 'Error!', {closeButton:!0, tapToDismiss:!1, rtl:o})
        }
    })
  })
</script>
@endpush