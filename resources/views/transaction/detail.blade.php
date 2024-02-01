@extends('layouts.app')

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="payment-id" value="{{ $payment->ID }}">
{{-- <input type="hidden" id="dashboard-url" value="{{ auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : route('home') }}"> --}}

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            @if(Session::has('status'))
                @if (Session::get('status') == 'OK')
                    <div class="alert alert-success text-center">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                        {{ Session::get('message') }}
                    </div>
                @else
                    <div class="alert alert-danger text-center">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                        {{ Session::get('message') }}
                    </div>
                @endif
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="text-align: center">@lang('label.detail_transaction')</div>
                        <div class="card-body">
                            @if(auth()->user()->is_demo != '1')
                            <div class="row">
                                <div class="col-md-3"><strong>@lang('label.unique_code')</strong></div>
                                <div class="col-md-9">
                                    <span>Rp. {{ $payment->UniqueCode }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3"><strong>@lang('label.total_pay')</strong></div>
                                <div class="col-md-9">
                                    <span>Rp. {{ number_format($payment->Payment,0,',','.') }}</span>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-3"><strong>@lang('label.active_period')</strong></div>
                                <div class="col-md-9">
                                    <span>{{ $payment->transaction->package->Subscribe }} Bulan</span>
                                </div>
                            </div>

                            @if(auth()->user()->is_demo != '1')
                                @php
                                    $get_bank = explode("-", $payment->PaymentTo);
                                @endphp
                                <div class="row">
                                    <div class="col-md-3"><strong>@lang('label.bank')</strong></div>
                                    <div class="col-md-9">
                                        {{ $get_bank[0] }}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3"><strong>@lang('label.transfer_to')</strong></div>
                                    <div class="col-md-9">
                                        {{$get_bank[1]}}
                                    </div>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-3"><strong>@lang('label.status')</strong></div>
                                <div class="col-md-9">
                                    {{ $payment->Status }}
                                </div>
                            </div>
                            <hr>
                            
                            @if(auth()->user()->is_demo == '1')
                                @if ($payment->Status == 'Paid')
                                <div class="alert alert-success text-center">
                                    Selamat <b>AKUN DEMO</b> anda sudah aktif.
                                </div>
                                @else
                                <div class="alert alert-info text-center">
                                    <p>
                                        Klik Tombol dibawah untuk mengaktifkan <b>AKUN DEMO</b> anda.
                                    </p>

                                    <button class="btn btn-sm btn-primary" id="active-account">Aktifkan Akun</button>
                                </div>
                                @endif
                            @else
                                @if ($payment->Status == 'Paid')
                                <div class="alert alert-success text-center">
                                    Selamat <b>Akun Anda</b> sudah aktif.
                                </div>
                                @else
                                    <div class="alert alert-info text-center">
                                        Mohon transfer nominal sampai <b>3 digit terakhir</b>.

                                        <br>
                                        Setelah uang di transfer kami membutuhkan waktu sekitar <b>20 Menit</b> untuk mempersiapkan aplikasi anda.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).on('click', '#active-account', function (e) {
        e.preventDefault()

        // Initialize
        let dashboardUrl = $('#dashboard-url').val()

        // Disabled Button True
        $('#active-account').attr('disabled', true)

        $.ajax({
            url: `${baseUrl}/demo/active-account`,
            type: 'POST',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            data: {
                paymentId: $('#payment-id').val()
            },
            success: data => {
                // Validate
                if (!data.status) {
                    Swal.fire({
                      title: 'Error',
                      text: `${data.message}`,
                      icon: 'error'
                    })

                    // Disabled Button False
                    $('#active-account').attr('disabled', false)
                    return 0
                }

                Swal.fire({
                  title: 'Sukses',
                  text: `${data.message}`,
                  icon: 'success'
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location = `${dashboardUrl}`
                  }
                })
            },
            error: e => {
                console.log(e)

                // Disabled Button False
                $('#project-btn-loading').attr('disabled', false)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })
            }
        })
    })
</script>
@endpush
