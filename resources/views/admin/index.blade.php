@extends('layouts.app')

@push('style')
<style>
    .card-body-custom {
        padding: 0.25rem 0.25rem 1.25rem 1.25rem !important;
    }

    .card-img-top {
        height: 300px
    }

    .card-no-border {
        border-color: #d7dfe3;
        border-radius: 4px;
        margin-bottom: 30px;
        -webkit-box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.05);
        box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.05)
    }

    .pro-img {
        margin-top: -80px;
        margin-bottom: 20px
    }

    .little-profile .pro-img img {
        width: 128px;
        height: 128px;
        -webkit-box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        border-radius: 100%
    }

    .avatar-group {
        border-radius: 50%;
        width: 30%;
        height: 65px;
    }
</style>
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" value="{{ route('user.store') }}" id="user-store-url">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-12">
            <div class="card">
                <div class="card-header">
                    <div class="float-right">
                        <a href="{{ route('company:home', \Str::slug(auth()->user()->company->Name)) }}" class="btn btn-sm btn-primary">@lang('button.home')</a>

                        @if(auth()->user()->checkDivision())
                        <a href="{{ route('user.form') }}" class="btn btn-sm btn-primary">@lang('label.add_member')</a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="clearfix">
                        <h6><b><u>@lang('label.user_propose')</u></b></h6>
                        
                        <div class="row mt-3 scroll-y">
                            @foreach ($user_propose as $key => $item)
                            <div class="col-md-3 col-12">
                                <div class="card">
                                    <div class="clearfix">
                                        <div style="border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 5px;" class="float-right p-1">
                                            <a href="javascript:void(0)" class="text-success approve-propose" user-id="{{ $item->id }}" user-name="{{$item->name }}">
                                                <i class="fa fa-check"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="text-danger delete-member" user-id="{{ $item->id }}" user-name="{{$item->name }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body card-body-custom">
                                        <b>NIP : {{ $item->nip }}</b><br>
                                        <b>Email : {{ $item->name }}</b>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="clearfix">
                        <h6><b><u>@lang('label.member_list')</u></b></h6>

                        <div class="row mt-3 scroll-y">
                            @foreach ($user as $key => $item)
                            <div class="col-md-3 col-12 mb-3">
                                <div class="card">
                                    <div class="clearfix">
                                        <div style="border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 5px;" class="float-right p-1">
                                            <a href="javascript:void(0)" class="text-primary profile-details-area cursor-area" user-id="{{ $item->id }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="text-danger delete-member" user-id="{{ $item->id }}" user-name="{{$item->name }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body text-center card-body-custom">
                                        <div class="clearfix">
                                            <img src="{{ ($item->avatar) ? $item->avatar : "https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg" }}" class="avatar-group" alt="avatar-group">
                                        </div>

                                        <h6 class="mt-3"><b>{{ $item->name }}</b></h6>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-12">
                                {!! $user->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include File --}}
@include('components.profile-details-modal')
@endsection

@push('script')
{{-- List Propose --}}
<script>
    $(document).on('click', '.approve-propose', function (e) {
        e.preventDefault()

        // Initialize
        let userId   = $(this).attr('user-id')
        let userName = $(this).attr('user-name')

        // Validate
        Swal.fire({
            text: `Approve Member ${userName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oke'
        }).then((result) => {
          if (result.isConfirmed) {
            // Call Function
            approveMember(userId)
          }
        })
    })

    function approveMember (userId) {
        $.ajax({
            url: `${baseUrl}/propose/approve/${userId}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                // Validate
                if (!data.status) {
                    Swal.fire({
                      title: 'Error',
                      text: `${data.message}`,
                      icon: 'error'
                    })

                    return 0
                }

                Swal.fire({
                  title: 'Sukses',
                  text: `${data.message}`,
                  icon: 'success'
                }).then((result) => {
                  if (result.isConfirmed) {
                    location.reload()
                  }
                })
            },
            error: e => {
                console.log(e)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })

                return 0
            }
        })
    }
</script>

{{-- List Member --}}
<script>
    $(document).on('click', '.delete-member', function (e) {
        e.preventDefault()

        // Initialize
        let userId   = $(this).attr('user-id')
        let userName = $(this).attr('user-name')

        // Validate
        Swal.fire({
            text: `Hapus Member ${userName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oke'
        }).then((result) => {
          if (result.isConfirmed) {
            // Call Function
            destroyMember(userId)
          }
        })
    })

    // Destroy Member
    function destroyMember (userId) {
        $.ajax({
            url: `${baseUrl}/user/delete/${userId}`,
            type: 'POST',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                // Validate
                if (!data.status) {
                    Swal.fire({
                      title: 'Error',
                      text: `${data.message}`,
                      icon: 'error'
                    })

                    return 0
                }

                Swal.fire({
                  title: 'Sukses',
                  text: `${data.message}`,
                  icon: 'success'
                }).then((result) => {
                  if (result.isConfirmed) {
                    location.reload()
                  }
                })
            },
            error: e => {
                console.log(e)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })

                return 0
            }
        })
    }
</script>

{{-- Profile Details --}}
<script>
    $(document).on('click', '.profile-details-area', function (e) {
        e.preventDefault()

        $('#profile-details-modal-aria').html('Detail Member')
        $('#profile-details-modal').modal('show')

        // Call Function
        showUser($(this).attr('user-id'))
    })

    function showUser(userId) {
       $.ajax({
            url: `${baseUrl}/user/edit/${userId}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                // Append Value Element
                $('#name-profile-element').html(data.data.data.name)

                if (data.data.data.background_image) {
                    $('#backgorund-img-profile').attr('src', data.data.data.background_image)
                } else {
                    $('#backgorund-img-profile').attr('src', 'https://www.contentviewspro.com/wp-content/uploads/2017/07/default_image.png')
                }

                if (data.data.data.avatar) {
                    $('#avatar-profile').attr('src', data.data.data.avatar)
                } else {
                    $('#avatar-profile').attr('src', 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg')
                }

                // Append Value Input
                $('#id-profile').val(data.data.data.id)
                $('#name-profile').val(data.data.data.name)
                $('#email-profile').val(data.data.data.email)
                $('#nip-profile').val(data.data.data.nip)
                $('#phone-profile').val(data.data.data.phone)

                // Call Function
                showRole(data.data.role_id)

                if ((data.data.data.division).length > 0) {
                    showDivision(data.data.data.division[0].ID)

                    $('#current-dvision').html(data.data.data.division[0].Name)
                } else {
                    showDivision()
                }

                if ((data.data.data.majors).length > 0) {
                    showMajors(data.data.data.majors[0].ID)
                } else {
                    showMajors()
                }
            },
            error: e => {
                console.log(e)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })

                return 0
            }
        })
    }

    function showRole(roleId) {
       $.ajax({
            url: `${baseUrl}/role/show`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                // Initialize
                let template = templateRole(data, roleId)

                $('#role-profile').html(template)
            },
            error: e => {
                console.log(e)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })

                return 0
            }
        })
    }

    function showDivision(divisionId = '') {
        $.ajax({
            url: `${baseUrl}/division?divisionId=${divisionId}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                $('#divisionId').html(data.data)
            },
            error: e => {
                console.log(e)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })

                return 0
            }
        })
    }

    function showMajors(majorsId = '') {
        $.ajax({
            url: `${baseUrl}/majors?majorsId=${majorsId}`,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            success: data => {
                $('#majorsId').html(data.data)
            },
            error: e => {
                console.log(e)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })

                return 0
            }
        })
    }

    function templateRole(data, roleId) {
        // Initialize
        let role = ``

        if (data.data.length > 0) {
            $.each(data.data, function (key, val) {
                // Initialize
                let selected = ''

                if (val.ID == roleId) {
                    selected = 'selected'
                }

                role += `<option value="${val.ID}" ${selected}>${val.Name}</option>`
            })
        } else {
            role += `<option value="0">--- Pilih Role ---</option>`
        }

        return role
    }

    // Update
    $(document).on('submit', '#member-edit form', function (e) {
        e.preventDefault()

        // Initialize
        let url = $('#user-store-url').val()

        // Disabled Button True
        $('.btn-loading').attr('disabled', true)

        $.ajax({
            url: `${url}`,
            type: 'POST',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            success: data => {
                // Validate
                if (!data.status) {
                    Swal.fire({
                      title: 'Error',
                      text: `${data.message}`,
                      icon: 'error'
                    })

                    // Disabled Button False
                    $('.btn-loading').attr('disabled', false)

                    return 0
                }

                // Hide Modal
                $('#profile-details-modal').modal('hide')

                Swal.fire({
                  title: 'Sukses',
                  text: `${data.message}`,
                  icon: 'success'
                }).then((result) => {
                  if (result.isConfirmed) {
                    location.reload()
                  }
                })
            },
            error: e => {
                console.log(e)

                // Disabled Button False
                $('.btn-loading').attr('disabled', false)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })
            }
        })
    })
</script>

<script type="text/javascript">
    // $('ul.pagination').hide();

    // $(function() {
    //     $('.infinite-scroll').jscroll({
    //             autoTrigger: true,
    //             loadingHtml: '<img class="center-block" src="{{ asset('img/loader.gif') }}" alt="Loading..." />',
    //             padding: 0,
    //             nextSelector: '.pagination li.active + li a',
    //             contentSelector: 'div.infinite-scroll',
    //             callback: function() {
    //                 $('ul.pagination').remove();
    //             }
    //         });
    // });
</script>
@endpush