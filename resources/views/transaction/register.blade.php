@extends('layouts.app')

@push('style')
<style>
    .price-features li i {
        height: 20px;
        width: 20px;
        display: inline-block;
        text-align: center;
        line-height: 20px;
        font-size: 10px;
        border-radius: 50%;
        margin-right: 20px;
        background-color: rgba(246, 87, 110, 0.1);
        color: #62DDBD;
    }

    ul li {
        list-style: none;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center mt-2">
        <div class="col-md-8">
            {{-- Notification --}}
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

            <div class="card mt-2">
                <div class="card-header text-center">
                    <h5>Buka Kursus Gratis</h5>

                    @lang('label.complate_data')
                </div>
                <div class="card-body">
                    <form action="{{route('transaction.register_store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row mt-2">
                            <div class="col-md-4"><b>Email Anda</b> <span class="text-danger">*</span></div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="email" placeholder="" readonly value="{{ auth()->user()->email }}">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4"><b>Nama Anda</b> <span class="text-danger">*</span></div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="name" placeholder="Masukkan Nama Anda" required value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4"><b>@lang('label.phone')</b> <span class="text-danger">*</span></div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="no_hp_account" placeholder="Masukkan Nomer Hp" required value="{{ old('no_hp_account') }}">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4"><b>Nama Lembaga Kursus</b> <span class="text-danger">*</span></div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="company_name" placeholder="Masukkan Nama Lembaga Kursus Anda Di RuangAjar" required value="{{ old('company_name') }}">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4"><b>Alamat Lembaga Kursus (Opsional)</b></div>
                            <div class="col-md-8">
                                <textarea name="company_address" class="form-control" id="" rows="3" placeholder="Masukkan Alamat Lembaga Kursus">{{ old('company_address') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-sm btn-primary">Buka Kursus Sekarang</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        
        $(document).ready(function() {
            $('#agency_js').change(function(){
                var val = $(this).val()

                if (val == 'private_office' || val == 'government_agency') {
                    $('.office').removeAttr('style', 'display');
                    $('.school').attr('style', 'display:none');
                    $('#sk_pendirian').removeAttr('required');
                } else if (val == 'college' || val == 'school') {
                    $('.school').removeAttr('style', 'display');
                    $('.office').attr('style', 'display:none');
                    $('#npwp').removeAttr('required');
                } else {
                    $('#sk_pendirian').removeAttr('required');
                    $('#npwp').removeAttr('required');
                }
            })
        });
    </script>
    
    <script>
        $(document).ready(function () {
            // Initialize
            let packageVal = $('#package-list option:selected').val()

            if (packageVal != 0) {
                // Initialize
                let package = $('#package-list option:selected').text()
                let value   = package.split('|')

                if (value[0] == 'Silver ') {
                    $('#user-limit').html('250 User')
                } else {
                    $('#user-limit').html('Unlimited User')
                }

                $('#package-benefits').css('display', '')
            } else {
                $('#package-benefits').css('display', 'none')
            }
        })

        $(document).on('change', '#package-list', function () {
            if (this.value == 0) {
                $('#package-benefits').css('display', 'none')

                return 0
            }

            // Initialize
            let package = $('#package-list option:selected').text()
            let value   = package.split('|')
            
            if (value[0] == 'Silver ') {
                $('#user-limit').html('250 User')
            } else {
                $('#user-limit').html('Unlimited User')
            }

            $('#package-benefits').css('display', '')
        })
    </script>
@endpush
