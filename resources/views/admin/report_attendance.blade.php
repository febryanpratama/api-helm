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
            <form action="{{ route('report.attendance') }}" method="get">
                <input type="hidden" name="search" value="1">
                <div class="row">
                    <div class="col-md-3">
                        <label for="">@lang('label.start_date')</label>
                        <input type="date" name="start_date" value="{{ request()->get('start_date') }}" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3">
                        <label for="">@lang('label.end_date')</label>
                        <input type="date" name="end_date" value="{{ request()->get('end_date') }}" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3">
                        <label for="">@lang('label.user')</label>
                        <select name="user_id" id="search_user" class="form-control">
                            <option value="">Choose User</option>
                            @foreach ($user as $item)
                                <option value="{{ $item->id }}" {{ $item->id == request()->user_id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-md btn-primary" type="submit" style="margin-top:30px;">@lang('button.search') <i class="fa fa-search"></i></button>
                        <a href="{{ route('report.attendance') }}" class="btn btn-md btn-primary" style="margin-top:30px;">@lang('button.reset') <i class="fa fa-refresh"></i></a>
                    </div>
                </div>
            </form>
            <div class="card" style="margin-top:10px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @lang('label.report_checkin_checkout')
                    <a href="{{route('report.attendance_download', ['start_date' => request()->get('start_date'), 'end_date' => request()->get('end_date'), 'user_id' => request()->get('user_id')])}}" class="btn btn-sm btn-primary">@lang('button.download')</a>
                </div>

                <div class="card-body">
                    <div class="infinite-scroll">
                        @foreach ($attendance as $item)
                            <div class="card" style="margin-top:5px;">
                                <div class="card-header">{{ $item->user->name }}</div>
                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>@lang('label.check_in_date')</strong>
                                        </div>
                                        <div class="col-md-8">
                                            {{ $item->check_in_datetime }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>@lang('label.check_in_place')</strong>
                                        </div>
                                        <div class="col-md-8">
                                            {{ $item->check_in_place }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>@lang('label.check_in_photo')</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <img src="{{ $item->check_in_photo }}" alt="" srcset="" style="width:130px;height:180px;">
                                            
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>@lang('label.check_out_date')</strong>
                                        </div>
                                        <div class="col-md-8">
                                            {{ $item->check_out_datetime }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>@lang('label.check_out_place')</strong>
                                        </div>
                                        <div class="col-md-8">
                                            {{ $item->check_out_place }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>@lang('label.check_out_photo')</strong>
                                        </div>
                                        <div class="col-md-8">
                                            <img src="{{ $item->check_out_photo }}" alt="" srcset="" style="width:130px;height:180px;">
                                            
                                        </div>
                                    </div>
                                
                                </div>
                            </div>
                        @endforeach
                        {!! $attendance->links() !!}
                    </div>
                    
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

<script>
    $(document).ready(function() {
        $('#search_user').select2({
            
            // multiple: true,
            dropdownAutoWidth: true,
            // multiple: true,
            width: '100%',
            placeholder: "@lang('placeholder.select')",
        });
    });
</script>
@endpush

@push('style')
<style>
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
</style>
@endpush
