@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
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

            <div class="row mt-2">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('company:home', \Str::slug(auth()->user()->company->Name)) }}" class="btn btn-primary">@lang('button.home')</a>
                            
                            <div class="float-right">
                                <a href="{{route('user.form')}}" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="right" style="text-align: right">
                          <a href="{{route('user.form')}}" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
                    </div> --}}

                    <div class="card mt-2">
                        <div class="card-header">@lang('label.data_user')</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="infinite-scroll">
                                        @foreach ($user as $key => $item)
                                            <div class="card mb-3" style="margin-top:5px;">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    {{ $item->name }}
        
                                                    <div class="float-sm-right">
                                                        <a href="{{route('user.form', ['id'=>$item->id])}}" class=""><i class="fa fa-pencil"></i></a>
                                                        <a href="#" class="" 
                                                        onclick="if (confirm('Are you sure?')) document.getElementById('delete-{{ $item->id }}').submit()"><i class="fa fa-trash"></i></a>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <h6>@lang('label.nip')</h6>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <strong>{{$item->nip}}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <h6>@lang('label.email')</h6>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <strong>{{$item->email}}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <h6>@lang('label.created_at_label')</h6>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <strong>{{ $item->created_at->format('d M y H:i') }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <form id="delete-{{ $item->id }}" action="{{ route('user.delete', $item->id) }}" method="post" style="display: none;">
                                                @csrf
                                            </form>
                                        @endforeach
                                        {!! $user->links() !!}
                                    </div>
                                    {{-- <table class="table table-responsive">
                                        <tr>
                                            <th>No</th>
                                            <th>Email</th>
                                            <th>Name</th>
                                            <th>Aksi</th>
                                        </tr>
        
                                        @foreach ($user as $key => $item)
                                            <tr>
                                                <td>{{++$key}}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>
                                                    <a href="{{ route('user.delete', $item->id) }}" class="btn btn-sm btn-danger" onclick="event.preventDefault();
                                                        document.getElementById('delete-{{ $item->id }}').submit();"><i class="fa fa-trash"></i></a>
        
                                                    <form id="delete-{{ $item->id }}" action="{{ route('user.delete', $item->id) }}" method="post" style="display: none;">
                                                        @csrf
                                                    </form>
                                                    
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" style="margin-top:30px;">
                    <div class="card mt-2">
                        <div class="card-header">@lang('label.user_propose')</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    @foreach ($user_propose as $key => $item)
                                        <div class="card" style="margin-top:5px;">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                {{ $item->name }}
    
                                                <div class="float-sm-right">
                                                    <a href="#" class="" 
                                                    onclick="if (confirm('Are you sure?')) document.getElementById('delete-{{ $item->id }}').submit()"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <h6>@lang('label.nip')</h6>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <strong>{{$item->nip}}</strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <h6>@lang('label.email')</h6>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <strong>{{$item->email}}</strong>
                                                    </div>
                                                </div>
                                                <a href="{{route('admin.propose_approve', $item->id)}}" class="btn btn-sm btn-primary">Approve</a>
                                            </div>
                                        </div>
                                        <form id="delete-{{ $item->id }}" action="{{ route('user.delete', $item->id) }}" method="post" style="display: none;">
                                            @csrf
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="row mt-2">
                <div class="col-md-4">
                    <a href="{{ route('company:home', \Str::slug(auth()->user()->company->Name)) }}" class="btn btn-primary">@lang('button.home')</a>
                </div>
            </div> --}}
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">@lang('label.add_user')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('user.store') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" name="name" required placeholder="@lang('label.name')" class="form-control">
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" required placeholder="@lang('label.email')" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="number" name="nip" required placeholder="@lang('label.nip')" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="number" name="phone" required placeholder="@lang('label.phone')" class="form-control">
                    </div>
                    <div class="form-group">
                        <select name="role" id="aneh" class="form-control" onchange="myfunction(this)" >
                            @foreach ($role as $item)
                                @if ($item->ID == '6' || $item->ID == '8' || $item->ID == '9')
                                    <option value="{{ $item->ID }}">{{ $item->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group supervised_by" style="display:none">
                        <select name="supervised_by" id="supervised_by" class="form-control" >
                            <option value="">@lang('label.select_supervised_by')</option>
                            @foreach ($supervisor as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('button.close')</button>
                    <button type="submit" class="btn btn-primary">@lang('button.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
    function myfunction(Element)
    {
        console.log($(Element).val());
        if ($(Element).val() == '8') {
            $(".supervised_by").show();
        } else {
            $(".supervised_by").hide();
            $("#supervised_by").val('');
        }
    }


</script>

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
