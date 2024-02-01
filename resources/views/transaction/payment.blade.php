@extends('layouts.app')

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
                <div class="card-header text-center">@lang('label.payment')</div>
                <div class="card-body">
                    <form action="{{route('transaction.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="price" value="{{ ($package->DesktopPriceFinal + $uniqueCode) }}">
                        <input type="hidden" name="unique_code" value="{{ $uniqueCode }}">
                        <input type="hidden" name="original_price" value="{{ $package->DesktopPriceFinal }}">
                        <input type="hidden" name="package" value="{{ $package->ID }}">
                        
                        <div class="row">
                            <div class="col-md-3"><strong>@lang('label.price')</strong></div>
                            <div class="col-md-9">
                                <span>Rp. {{ number_format(($package->DesktopPriceFinal + $uniqueCode),0,',','.') }}</span>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3"><strong>@lang('label.unique_code')</strong></div>
                            <div class="col-md-9">
                                <span>{{ $uniqueCode }}</span>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3"><strong>@lang('label.active_period')</strong></div>
                            <div class="col-md-9">
                                <span>3 months</span>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3"><strong>@lang('label.bank')</strong></div>
                            <div class="col-md-9">
                                <select name="bank" id="" class="form-control">
                                    <option value="bri-4343232">BRI</option>
                                    <option value="bca-4312423">BCA</option>
                                    <option value="mandiri-1232324">Mandiri</option>
                                    <option value="bni-5466443">BNI</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3"><strong>@lang('label.document_npwp')</strong></div>
                            <div class="col-md-9">
                                <input type="file" class="form-control" name="npwp">
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-3"><strong>@lang('label.document_nppkp')</strong></div>
                            <div class="col-md-9">
                                <input type="file" class="form-control" name="nppkp">
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary">@lang('button.checkout')</button>
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

@endpush
