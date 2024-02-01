@extends('layouts.app')

@push('style')
<style>
  .active-select-type-account {
    box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
    border: 2px solid #62ddbd !important;
  }

  .background-color-disabled {
    background-color: #00000013;
    cursor: not-allowed;
  }

  .web-nav {
    display: none;
  }

  .btn-primarys {
    color: #fff !important;
    background-color: #f95700 !important;
    border-color: #f95700 !important;
  }

  .text-colors {
    color: #f95700 !important;
  }
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="signin-url" value="{{ route('authorization.signin') }}">
<input type="hidden" id="verify-otp-url" value="{{ route('auth.otp_verify') }}">
<input type="hidden" id="redirect" value="{{ request('redirect') }}">
<input type="hidden" id="is-instructor-selected" value="{{ (request('insturctor') ? 'true' : 'false') }}">

<div class="container mb-5">
	<div class="row justify-content-center">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 mt-5">
			<div class="card card-custom">
        <div id="signin-form-area">
          <form method="POST">
            <div id="signin-form-input">
              <!-- <div class="form-group text-center mt-4">
                <label for=""><h5><b>Selamat Datang Admin!</b></h5></label>
              </div> -->

              <!-- <div class="row justify-content-center">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-12 text-center">
                  <img src="{{ asset('img/ruang-ajar-logo.png') }}" style="width: 60%;" alt="logo-preview">
                </div>
              </div> -->

              <div class="row justify-content-center p-3">
                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12">
                  <div class="form-group mt-4">
                    <label for="">Email</label> <span class="text-danger">*</span>
                    <input type="email" placeholder="Masukkan Email" name="email" class="form-control" id="signin-email">
                  </div>
                </div>

                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mb-4">
                  <button class="btn btn-primarys w-100" type="submit" id="signin-btn">MASUK</button>
                </div>

                <!-- <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 pt-3">
                  <div class="form-group">
                    <label for="" style="font-size: 12px;">Dengan Masuk, Anda menyetujui <a href="{{ route('help.index') }}?tags=syarat-dan-ketentuan" target="_blank" class="text-color">Ketentuan Penggunaan</a> dan <a href="{{ route('help.index') }}?tags=kebijakan-privasi" target="_blank" class="text-color">Kebijakan Privasi</a> kami.</label>
                  </div>
                </div> -->
              </div>
            </div>
          </form>
        </div>

        <div id="verify-otp-area" style="display: none;">
          <div class="row justify-content-center mt-4">
            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 text-center">
              <b>Kode OTP</b>
            </div>

            <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mt-4">
              <form method="POST">
                <div class="row justify-content-center p-3">
                  <div class="col-sm-3 col-md-2 col-lg-2 col-xl-2 col-3">
                    <input type="text" id="otp-one" next="otp-two" prev="first-otp" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>

                  <div class="col-sm-3 col-md-2 col-lg-2 col-xl-2 col-3">
                    <input type="text" id="otp-two" next="otp-three" prev="otp-one" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>

                  <div class="col-sm-3 col-md-2 col-lg-2 col-xl-2 col-3">
                    <input type="text" id="otp-three" next="otp-four" prev="otp-two" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>

                  <div class="col-sm-3 col-md-2 col-lg-2 col-xl-2 col-3">
                    <input type="text" id="otp-four" next="lastest-otp" prev="otp-three" maxlength="1" class="form-control otp-input" name="otp_code[]">
                  </div>
                  
                  <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 mt-4">
                    <button class="btn btn-primarys w-100" type="submit" id="verify-btn" disabled="">VERIFIKASI</button>
                  </div>

                  <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 pt-3 text-center">
                    <div class="form-group">
                      <div for="" style="font-size: 12px;">KODE OTP telah dikirim melalui email, silakan cek inbox atau folder spam</div>
                      
                      <a href="javascript:void(0)" class="text-colors" id="resend-otp"><b>KIRIM ULANG OTP <span id="resend-time"></span></b></a>
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
{{-- Signin --}}
<script>
  $(document).on('submit', '#signin-form-area form', function (e) {
      e.preventDefault()

      // Initialize
      let url         = $('#signin-url').val()
      let email       = $('#signin-email').val()
      let countdown   = []

      // Validate
      if (!email) {
          toastr.error(`Email harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

          return 0
      }

      for (let cT = 60; cT >= 1; cT--) {
        countdown.push(cT)
      }

      // Disabled Button True
      $('#signin-btn').attr('disabled', true)

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
            $('#signin-btn').attr('disabled', false)

            if (!data.status) {
                toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

                return 0
            }

            // DOM Manipulation
            $('#signin-form-input').css('display', 'none')
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
              $('#signin-btn').attr('disabled', false)

              toastr.error(`Gagal melakukan login, silahkan coba kembali`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
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
      let email    = $('#signin-email').val()
      let otpOne   = $('#otp-one').val()
      let otpTwo   = $('#otp-two').val()
      let otpThree = $('#otp-three').val()
      let otpFour  = $('#otp-four').val()
      let redirect = $('#redirect').val()

      // Validate
      if (!otpOne || !otpTwo || !otpThree || !otpFour) {
          toastr.error(`Kode OTP harus diisi`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

          return 0
      }

      // Disabled Button True
      $('#verify-btn').attr('disabled', true)

      // Initialize
      const fd = new FormData(this)
      fd.append('email', email)
      fd.append('redirect', redirect)

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
                  toastr.error(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

                  return 0
              }

              $('#sigin-modal').modal('hide')

              toastr.success(`${data.message}`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})

              window.location = `${baseUrl}/${(data.redirect) ? data.redirect : 'dashboard' }`
          },
          error: e => {
              console.log(e)

              // Disabled Button False
              $('#verify-btn').attr('disabled', false)

              toastr.error(`Gagal melakukan login, silahkan coba kembali`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
          }
      })
  })

  $(document).on('click', '.resend-otp-code', function () {
    // Initialize
    let email     = $('#signin-email').val()
    let countdown = []

    for (let cT = 60; cT >= 1; cT--) {
      countdown.push(cT)
    }

    // Remove Class
    $(this).removeClass('resend-otp-code')

    $.ajax({
        url: `${baseUrl}/auth/resend/otp`,
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

          toastr.success(`Kode OTP telah dikirim ulang`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
        },
        error: e => {
            console.log(e)

            // Disabled Button False
            $('#signin-btn').attr('disabled', false)

            toastr.error(`OTP Gagal diminta`, '', {closeButton:!0, tapToDismiss:!1, rtl:o})
        }
    })
  })
</script>
@endpush