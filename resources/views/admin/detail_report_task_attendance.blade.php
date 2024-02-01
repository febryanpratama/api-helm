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
            <div class="card" style="margin-top:10px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @lang('label.detail_report') {{ $user->name }}
                    <a href="{{route('report.excel_attendance_task', ['start_date' => request()->get('start_date'), 'end_date' => request()->get('end_date'), 'user_id' => $user->id])}}" class="btn btn-sm btn-primary">@lang('button.download')</a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>@lang('label.email')</strong>
                        </div>
                        <div class="col-md-8">
                            {{$user->email}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>@lang('label.nip')</strong>
                        </div>
                        <div class="col-md-8">
                            {{$user->nip}}
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="accordionAttendance">
                                <div class="card">
                                    <div class="card-header" id="attendance-{{ $user->id }}">
                                        <a href="#" data-toggle="collapse" data-target="#collapseAttendance-{{ $user->id }}" aria-expanded="true" aria-controls="collapseAttendance-{{ $user->id }}">
                                            @lang('label.attendance')
                                        </a>
                                    </div>
                                    <div id="collapseAttendance-{{ $user->id }}" class="collapse" aria-labelledby="headingConversation-{{ $user->id }}" data-parent="#accordionAttendance">
                                        <div class="card-body">
                                            @foreach ($user->searchAttendances(request()->get('start_date'), request()->get('end_date'), request()->get('user_id')) as $attendance)
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.check_in_date')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        {{ $attendance->check_in_datetime }}
                                                    </div>
                                                </div>
    
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.check_in_place')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        {{ $attendance->check_in_place ? $attendance->check_in_place : '-' }}
                                                    </div>
                                                </div>
    
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.check_in_photo')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        @if ($attendance->check_in_photo)
                                                            <img src="{{ $attendance->check_in_photo }}" alt="" srcset="" style="width:130px;height:180px;">
                                                        @else
                                                        -
                                                        @endif
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.check_out_date')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        {{ $attendance->check_out_datetime ? $attendance->check_out_datetime : '-' }}
                                                    </div>
                                                </div>
    
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.check_out_place')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        {{ $attendance->check_out_place ? $attendance->check_out_place : '-' }}
                                                    </div>
                                                </div>
    
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.check_out_photo')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        @if ($attendance->check_out_photo)
                                                            <img src="{{ $attendance->check_out_photo }}" alt="" srcset="" style="width:130px;height:180px;">
                                                        @else
                                                        -
                                                        @endif
                                                        
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="accordionTask">
                                <div class="card">
                                    <div class="card-header" id="task-{{ $user->id }}">
                                        <a href="#" data-toggle="collapse" data-target="#collapseTask-{{ $user->id }}" aria-expanded="true" aria-controls="collapseTask-{{ $user->id }}">
                                            @lang('label.task')
                                        </a>
                                    </div>
                                    <div id="collapseTask-{{ $user->id }}" class="collapse" aria-labelledby="headingConversation-{{ $user->id }}" data-parent="#accordionTask">
                                        <div class="card-body">
                                            @foreach ($user->tasks as $task)
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.task')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        {{$task->name}}
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.assigned_by')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        {{ $task->assignedBy->name }}
                                                    </div>
                                                </div>
                
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.assigned_to')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        @foreach ($task->users as $v)
                                                            {{ $v->name . ', ' }}
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.progress')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        @if (count($task->todos) > 0)
                                                            @php
                                                                $percentage = (count($task->isDone())/count($task->todos)) * 100;
                                                            @endphp
                
                                                            {{ round($percentage) }}%
                                                        @else
                                                            0%
                                                        @endif
                                                    </div>
                                                </div>
                                                @if (count($task->attachments) > 0)
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <strong>@lang('label.report')</strong>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <a href="{{ route('task.download', $task->id) }}" target="_blank" class="btn btn-sm btn-primary">@lang('button.download')</a>
                                                        </div>
                                                    </div>
                                                @endif
                                                <hr>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
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
