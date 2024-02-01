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
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="verify-otp-url" value="{{ route('auth.otp_verify') }}">
<input type="hidden" id="redirect" value="{{ request('redirect') }}">
<input type="hidden" id="signin-email" value="{{ request('email') }}">

<div class="container">
	<div class="row justify-content-center">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-12 mt-5">
			<div class="card card-custom">
        <div id="verify-otp-area">
          <div class="row justify-content-center mt-4">
            {{-- <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10 col-12 text-center">
              <div class="alert alert-success" id="success-register-alert">Pendaftaran Berhasil, Masukkan Kode OTP yang telah dikirim melalui Email untuk melanjutkan. </div>
            </div> --}}

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
                      <div for="" style="font-size: 12px;">KODE OTP telah dikirim melalui email, silakan cek inbox atau folder spam</div>
                      
                      <a href="javascript:void(0)" class="text-color" id="resend-otp"><b>KIRIM ULANG OTP <span id="resend-time"></span></b></a>
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

              window.location = `${baseUrl}/${redirect}`
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

<script>
  $(document).ready(function () {
    let countdown = []

    for (let cT = 60; cT >= 1; cT--) {
      countdown.push(cT)
    }

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
  })
</script>
@endpush