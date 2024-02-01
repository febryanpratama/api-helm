@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-2">
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
            <div class="card">
                <div class="card-header">
                    @if (@$data_user)
                        @lang('label.edit_user')
                    @else
                        @lang('label.create_user')
                    @endif
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 col-12">
                            <form action="{{ route('user.store') }}" method="post">
                                <input type="hidden" name="id" value="{{@$data_user->id}}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="">@lang('label.name')</label>
                                            <input type="text" name="name" required placeholder="@lang('label.put_in') @lang('label.name')" class="form-control" value="{{@$data_user->name}}">
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="">@lang('label.email')</label>
                                            <input type="email" name="email" required placeholder="@lang('label.put_in') @lang('label.email')" class="form-control" value="{{@$data_user->email}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="">@lang('label.nip')</label>
                                            <input type="text" name="nip" placeholder="@lang('label.put_in') @lang('label.nip')" class="form-control" value="{{@$data_user->nip}}">
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="">@lang('label.phone')</label>
                                            <input type="number" name="phone" placeholder="@lang('label.put_in') @lang('label.phone')" class="form-control" value="{{@$data_user->phone}}">
                                        </div>
                                    </div>
                                </div>

                                @if(auth()->user()->company->Type == 'private_office' || auth()->user()->company->Type == 'government_agency')
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="">@lang('label.role')</label>
                                                <select name="role" id="aneh" class="form-control" onchange="myfunction(this)" >
                                                    @foreach ($role as $item)
                                                        @if ($item->ID == '6' || $item->ID == '8' || $item->ID == '9')
                                                            <option value="{{ $item->ID }}" {{ @$data_user->role_id == $item->ID ? 'selected' : ''}}>{{ $item->Name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="">@lang('label.division')</label>

                                                <select name="divisionId" id="" class="form-control">
                                                    @foreach ($divisions as $division)
                                                        <option value="{{ $division->ID }}">{{ $division->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="">@lang('label.division')</label>

                                                <select name="divisionId" id="" class="form-control">
                                                    @foreach ($divisions as $division)
                                                        <option value="{{ $division->ID }}">{{ $division->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        @if (auth()->user()->company->Type != 'demo')
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="">@lang('label.majors')</label>

                                                    <select name="majorsId" id="majorsId" class="form-control">
                                                        @foreach ($majors as $major)
                                                            <option value="{{ $major->ID }}">{{ $major->Name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="">@lang('label.role')</label>
                                                <select name="role" id="aneh" class="form-control" onchange="myfunction(this)" >
                                                    @foreach ($role as $item)
                                                        @if ($item->ID == '6' || $item->ID == '8' || $item->ID == '9')
                                                            <option value="{{ $item->ID }}" {{ @$data_user->role_id == $item->ID ? 'selected' : ''}}>{{ $item->Name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="form-group supervised_by" style="display:none">
                                    <label for="">@lang('label.select_supervised_by')</label>
                                    <select name="supervised_by" id="supervised_by" class="form-control" >
                                        <option value="">@lang('label.put_in') @lang('label.select_supervised_by')</option>
                                        @foreach ($supervisor as $item)
                                            <option value="{{ $item->id }}" {{ @$data_user->supervised_by == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <a href="{{ route('user.data') }}" class="btn btn-sm btn-secondary">@lang('button.back')</a>
                                <button type="submit" class="btn btn-sm btn-primary">@lang('button.save')</button>
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

{{-- Setting Select 2 --}}
<script>
    $(document).ready(function() {
        $('#majorsId').select2()
    })
</script>
@endpush
