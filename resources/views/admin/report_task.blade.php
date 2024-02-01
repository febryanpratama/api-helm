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
                    <form action="{{ route('report.task') }}" method="get">
                        <input type="hidden" name="search" value="1">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="">@lang('label.user')</label>
                                <select name="user_id" id="user_id" class="form-control" required>
                                    <option value="">@lang('label.choose_user')</option>
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
                        <div class="card-header d-flex justify-content-between align-items-center" style="text-align: center">
                            @lang('label.report_task')
                            <a href="{{route('report.task_download', ['start_date' => request()->get('start_date'), 'end_date' => request()->get('end_date'), 'user_id' => request()->get('user_id')])}}" class="btn btn-sm btn-primary">@lang('button.download')</a>
                        </div>

                        <div class="card-body">
                            <div class="card-body">
                                @foreach ($tasks as $key => $item)
                                    <div class="card">
                                        <div class="card-header">@lang('label.task') - {{ $item->name }}</div>
                        
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>@lang('label.assigned_by')</strong>
                                                </div>
                                                <div class="col-md-8">
                                                    {{ $item->assignedBy->name }}
                                                </div>
                                            </div>
        
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>@lang('label.assigned_to')</strong>
                                                </div>
                                                <div class="col-md-8">
                                                    @foreach ($item->users as $v)
                                                        {{ $v->name . ', ' }}
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>@lang('label.progress')</strong>
                                                </div>
                                                <div class="col-md-8">
                                                    @if (count($item->todos) > 0)
                                                        @php
                                                            $percentage = (count($item->isDone())/count($item->todos)) * 100;
                                                        @endphp
            
                                                        {{ round($percentage) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </div>
                                            </div>
                                            @if (count($item->attachments) > 0)
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>@lang('label.report')</strong>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <a href="{{ route('task.download', $item->id) }}" target="_blank" class="btn btn-sm btn-primary">@lang('button.download')</a>
                                                    </div>
                                                </div>
                                            @endif
                                            <hr>
                                            <a href="{{ route('todo.detail_task', $item->id) }}" class="btn btn-sm btn-primary">@lang('button.detail') <i class="fa fa-eye"></i></a>

                                            
                                            <hr>
                                            @foreach ($item->todos as $v)
                                                <div class="row" style="margin-top:10px;background-color: rgb(238, 238, 238);height:30px;border-radius: 5px;">
                                                    <div class="col-md-12">
                                                        <h6 style="text-align: left; line-height: 30px;">{{ $v->todo }}</h6>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Modal todo -->
                                    <div class="modal fade" id="add-todo-assign-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Edit Todo</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('todo.store', $item->id) }}" method="post">
                                                    @csrf
                                                    {{-- <input type="hidden" name="id" value="{{ $item->id }}"> --}}
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <input type="text" name="todo" value="" required placeholder="Enter Todo Name" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            @if (auth()->user()->role_id == '8')
                                                                <label for="">Assigned To</label>
                                                                <select name="assigned_to" id="assigned_to" class="form-control" required placeholder="">
                                                                    @foreach ($item->users as $assign_user)
                                                                        <option value="{{ $assign_user->id }}">{{ $assign_user->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="hidden" name="assigned_to" value="{{ auth()->user()->id }}">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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

@endpush
