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
                <div class="col-md-6">
                    <a href="{{ route('report.attendance') }}">
                        <div class="card">
                            <div class="card-header" style="text-align: center">@lang('label.checkin_checkout')</div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('report.task') }}">
                        <div class="card">
                            <div class="card-header" style="text-align: center">@lang('label.task')</div>
                        </div>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
    $('ul.pagination').hide();
    $(function() {
        $('.infinite-scroll').jscroll({
                autoTrigger: true,
                loadingHtml: '<img class="center-block" src="{{ asset('img/loader.gif') }}" alt="Loading..." />',
                padding: 0,
                nextSelector: '.pagination li.active + li a',
                contentSelector: 'div.infinite-scroll',
                callback: function() {
                    $('ul.pagination').remove();
                }
            });
    });
</script>
@endpush
