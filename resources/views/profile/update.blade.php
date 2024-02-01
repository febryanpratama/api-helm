@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center mt-2">
        <div class="col-md-8">
            @if(Session::has('status'))
                <div class="alert alert-danger text-center">
                   <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                   {{ Session::get('message') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">@lang('label.complete_profile')</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update', $user->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="form-group row">
                            <label for="otp" class="col-md-4 col-form-label">@lang('label.name')</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="otp" class="col-md-4 col-form-label">@lang('label.phone')</label>

                            <div class="col-md-6">
                                <input id="phone_no" type="text" class="form-control @error('phone_no') is-invalid @enderror" name="phone_no" value="{{ old('phone_no') }}" required autocomplete="phone_no" autofocus>
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <label for="otp" class="col-md-4 col-form-label">Company Name</label>

                            <div class="col-md-6">
                                <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-sm btn-primary">@lang('button.save')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection