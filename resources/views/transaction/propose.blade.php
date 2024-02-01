@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
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
                        <div class="card-header" style="text-align: center">@lang('label.propose')</div>
                        <div class="card-body">
                            <form action="{{ route('transaction.propose_store', ['company' => request()->get('company')]) }}" method="post">
                                @csrf
                                <label for="">@lang('label.description')</label>
                                <textarea name="propose" class="form-control" id="" cols="30" rows="5"></textarea>
                                <button class="btn btn-primary" type="submit">@lang('label.send') <i class="fa fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush
