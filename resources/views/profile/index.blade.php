@extends('layouts.app')

@push('style')
<style>
    .card-body-custom {
        padding: 0.25rem 0.25rem 1.25rem 1.25rem !important;
    }

    /* Mobile */
    @media (max-width: 640px) {
        .card-img-top {
            height: 250px;
        }
    }

    @media screen and (max-width: 900px) and (min-width: 600px), (min-width: 1100px) {
        .card-img-top {
            height: 400px;
        }
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
</style>

{{-- For X Editable --}}
{{-- <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" /> --}}
@endpush

@section('content')
{{-- Hidden Element --}}
<input type="hidden" id="upload-file-url" value="{{ route('profile.upload.file') }}">

<div class="container mt-4">
    <div class="row">
    	<div class="col-md-12 col-12">
    		<div class="card">
                @if(auth()->user()->thumbnail)
                    <img class="card-img-top" src="{{ auth()->user()->thumbnail }}" alt="Card image cap" id="backgorund-img-profile">
                @else
                    <img class="card-img-top" src="https://truelife.church/wp-content/uploads/2017/12/dummy-thumbnail-1080x675.jpg" alt="Card image cap" id="backgorund-img-profile">
                @endif

                <div class="card-body little-profile">
                    <div class="pro-img text-center">
                        @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="user" id="avatar-profile">
                        @else
                        <img src="https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg" alt="user" id="avatar-profile">
                        @endif
                    </div>
                    <h3 class="m-b-0 text-center" id="name-profile-element">{{ auth()->user()->name }}</h3>
                    <p class="text-center">IT Team</p>

                    <div class="clearfix">
                        <h6><b>Informasi</b></h6>

                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <td>@lang('label.name')</td>
                                    <td width="10">:</td>
                                    <td>
                                        <div class="clearfix" id="name-element">
                                            <div class="float-left">
                                                {{ auth()->user()->name }}
                                            </div>

                                            <div class="float-left ml-2 cursor-area click-element-edit" tags="name">
                                                <i class="fa fa-pencil text-primary"></i>
                                            </div>
                                        </div>

                                        <div class="clearfix" id="name-input-element" style="display: none;">
                                            <div class="float-left">
                                                <input type="text" id="name-edit" value="{{ auth()->user()->name }}" class="form-control">
                                            </div>

                                            <div class="float-left pl-4 mt-1">
                                                <button class="btn btn-sm btn-primary save-element" id="save-name" tags="name"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-sm btn-danger ml-1 close-element" id="unsave-name"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>@lang('label.email')</td>
                                    <td width="10">:</td>
                                    <td>{{ auth()->user()->email }}</td>
                                </tr>

                                <tr>
                                    <td>@lang('label.nip')</td>
                                    <td width="10">:</td>
                                    <td>{{ auth()->user()->nip }}</td>
                                </tr>

                                <tr>
                                    <td>@lang('label.phone')</td>
                                    <td width="10">:</td>
                                    <td>
                                        {{-- {{ auth()->user()->phone }} --}}
                                        <div class="clearfix" id="phone-element">
                                            <div class="float-left">
                                                {{ auth()->user()->phone }}
                                            </div>

                                            <div class="float-left ml-2 cursor-area click-element-edit" tags="phone">
                                                <i class="fa fa-pencil text-primary"></i>
                                            </div>
                                        </div>

                                        <div class="clearfix" id="phone-input-element" style="display: none;">
                                            <div class="float-left">
                                                <input type="text" id="phone-edit" value="{{ auth()->user()->phone }}" class="form-control">
                                            </div>

                                            <div class="float-left pl-4 mt-1">
                                                <button class="btn btn-sm btn-primary save-element" id="save-phone" tags="phone"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-sm btn-danger ml-1 close-element" id="unsave-phone"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>@lang('label.role')</td>
                                    <td width="10">:</td>
                                    <td>{{ auth()->user()->role->Name }}</td>
                                </tr>

                                <tr>
                                    <td>@lang('label.thumbnail')</td>
                                    <td width="10">:</td>
                                    <td id="thumbnail-form">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="clearfix">
                                                <button class="btn btn-info btn-sm text-white" id="thumbnail-btn-file" type="button"><i class="fa fa-camera"></i></button>
                                                    @if(auth()->user()->thumbnail)
                                                        @php
                                                            // Initialize
                                                            $thumbnail = explode('/', auth()->user()->thumbnail);
                                                        @endphp

                                                        <span id="span-name-file-thumbnail" class="pl-2">
                                                            <i>{{ ($thumbnail[8]) ? $thumbnail[8] : 'Tidak ada file yang dipilih.' }}</i>
                                                        </span>

                                                        {{-- Hidden Element --}}
                                                        <input type="hidden" id="thumbnail-selected-file" value="{{ ($thumbnail[8]) ? $thumbnail[8] : 'Tidak ada file yang dipilih.' }}">
                                                    @else
                                                        <span class="pl-2"><i>Tidak ada file yang dipilih.</i></span>

                                                        {{-- Hidden Element --}}
                                                        <input type="hidden" id="thumbnail-selected-file" value="Tidak ada file yang dipilih.">
                                                    @endif
                                                <input type="file" id="file-thumbnail" name="file" hidden="">
                                            </div>

                                            <div class="mt-2" id="upload-thumbnail" style="display: none;">
                                                <button type="submit" class="btn btn-sm btn-primary" id="save-thumbnail"><i class="fa fa-check"></i></button>
                                                <button type="button" class="btn btn-sm btn-danger text-white" id="unsave-thumbnail"><i class="fa fa-times"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>

                                <tr>
                                    <td>@lang('label.avatar')</td>
                                    <td width="10">:</td>
                                    <td id="avatar-form">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="clearfix">
                                                <button class="btn btn-info btn-sm text-white" id="avatar-btn-file" type="button"><i class="fa fa-camera"></i></button>
                                                    @if(auth()->user()->avatar)
                                                        @php
                                                            // Initialize
                                                            $avatar = explode('/', auth()->user()->avatar);
                                                        @endphp

                                                        <span id="span-name-file-avatar" class="pl-2">
                                                            <i>{{ ($avatar[8]) ? $avatar[8] : 'Tidak ada file yang dipilih.' }}</i>
                                                        </span>        
                                                        
                                                        {{-- Hidden Element --}}
                                                        <input type="hidden" id="avatar-selected-file" value="{{ ($avatar[8]) ? $avatar[8] : 'Tidak ada file yang dipilih.' }}">
                                                    @else
                                                        <span class="pl-2"><i>Tidak ada file yang dipilih.</i></span>

                                                        {{-- Hidden Element --}}
                                                        <input type="hidden" id="avatar-selected-file" value="Tidak ada file yang dipilih.">
                                                    @endif
                                                <input type="file" id="file-avatar" name="file" hidden="">
                                            </div>

                                            <div class="mt-2" id="upload-avatar" style="display: none;">
                                                <button type="submit" class="btn btn-sm btn-primary" id="save-avatar"><i class="fa fa-check"></i></button>
                                                <button type="button" class="btn btn-sm btn-danger text-white" id="unsave-avatar"><i class="fa fa-times"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</div>
@stop

@push('script')
{{-- For X Editable --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/jquery-popover@0.0.4/src/jquery-popover.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script> --}}

<script>
    {{-- Trigger Input Type File --}}
    $(document).on('click', '#thumbnail-btn-file', function () {
        $('#file-thumbnail').click()
    })

    $(document).on('click', '#avatar-btn-file', function () {
        $('#file-avatar').click()
    })

    // Get Full Path
    $(document).on('change', '#file-thumbnail', function () {
        // Initialize
        let fileSelected = $('#thumbnail-selected-file').val()

        // Validate
        if (this.files[0]) {
            $('#span-name-file-thumbnail').html(`${this.files[0].name}`)
            $('#upload-thumbnail').css('display', '')
            $('#thumbnail-btn-file').css('display', 'none')
        } else {
            $('#span-name-file-thumbnail').html(`<i>${fileSelected}</i>`)
        }
    })

    $(document).on('change', '#file-avatar', function () {
        // Initialize
        let fileSelected = $('#avatar-selected-file').val()

        // Validate
        if (this.files[0]) {
            $('#span-name-file-avatar').html(`${this.files[0].name}`)
            $('#upload-avatar').css('display', '')
            $('#avatar-btn-file').css('display', 'none')
        } else {
            $('#span-name-file-avatar').html(`<i>${fileSelected}</i>`)
        }
    })
</script>

{{-- Click Edit --}}
<script>
$(document).on('click', '.click-element-edit', function (e) {
    e.preventDefault()

    // Initialize
    let tags = $(this).attr('tags')

    if (tags == 'name') {
        $('#name-input-element').css('display', '')
        $('#name-element').css('display', 'none')
    } else {
        $('#phone-input-element').css('display', '')
        $('#phone-element').css('display', 'none')
    }
})

$(document).on('click', '.close-element', function (e) {
    e.preventDefault()

    // Initialize
    let id = $(this).attr('id')

    if (id == 'unsave-name') {
        $('#name-input-element').css('display', 'none')
        $('#name-element').css('display', '')
    } else {
        $('#phone-input-element').css('display', 'none')
        $('#phone-element').css('display', '')
    }
})

$(document).on('click', '.save-element', function (e) {
    e.preventDefault()

    // Initialize
    let tags  = $(this).attr('tags')
    let key = ''
    let val = ''

    if (tags == 'name') {
        key = 'name'
        val = $('#name-edit').val()

        // Validate
        if (!val) {
            Swal.fire({
              title: 'Error',
              text: `Nama harus diisi!`,
              icon: 'error'
            })

            return 0
        }
    } else {
        key = 'phone_no'
        val = $('#phone-edit').val()

        // Validate
        if (!val) {
            Swal.fire({
              title: 'Error',
              text: `Nomer Hp harus diisi!`,
              icon: 'error'
            })

            return 0
        }
    }

    // Call Function
    updateProfile(tags, key, val)
})

function updateProfile(tags, key, val) {
    // Disabled Button True
    $(`#save-${tags}`).attr('disabled', true)
    
    let data = {}
    data[`_method`] = 'PATCH'
    data[`${key}`]  = val

    $.ajax({
        url: `${baseUrl}/profile/update/${userId}`,
        type: 'POST',
        headers: {'X-CSRF-TOKEN': `${csrfToken}`},
        data: data,
        success: data => {
            // Validate
            if (!data.status) {
                Swal.fire({
                  title: 'Error',
                  text: `${data.message}`,
                  icon: 'error'
                })

                // Disabled Button False
                $(`#save-${tags}`).attr('disabled', false)

                return 0
            }

            // Hide Modal
            $('#task-modal').modal('hide')

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
            $(`#save-${tags}`).attr('disabled', false)

            Swal.fire({
              title: 'Error',
              text: '500 Internal Server Error!',
              icon: 'error'
            })
        }
    })
}
</script>

{{-- Upload File --}}
<script>
    // Thumbnail
    $(document).on('submit', '#thumbnail-form form', function (e) {
        e.preventDefault()

        // Call function
        uploadFile('thumbnail', this)
    })

    $(document).on('click', '#unsave-thumbnail', function (e) {
        e.preventDefault()
        // Initialize
        let fileSelected = $('#thumbnail-selected-file').val()

        $('#upload-thumbnail').css('display', 'none')
        $('#thumbnail-btn-file').css('display', '')
        $('#span-name-file-thumbnail').html(`<i>${fileSelected}</i>`)
    })

    // Avatar
    $(document).on('submit', '#avatar-form form', function (e) {
        e.preventDefault()

        // Call function
        uploadFile('avatar', this)
    })

    $(document).on('click', '#unsave-avatar', function (e) {
        e.preventDefault()
        // Initialize
        let fileSelected = $('#avatar-selected-file').val()

        $('#upload-avatar').css('display', 'none')
        $('#avatar-btn-file').css('display', '')
        $('#span-name-file-avatar').html(`<i>${fileSelected}</i>`)
    })

    function uploadFile(type, data) {
        // Initialize
        let url = $('#upload-file-url').val()

        // Disabled Button True
        $(`#save-${type}`).attr('disabled', true)
        
        let fd = new FormData(data)
        fd.append('type', type)

        $.ajax({
            url: `${url}`,
            type: 'POST',
            headers: {'X-CSRF-TOKEN': `${csrfToken}`},
            data: fd,
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
                    $(`#save-${type}`).attr('disabled', false)

                    return 0
                }

                // Hide Modal
                $('#task-modal').modal('hide')

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
                $(`#save-${type}`).attr('disabled', false)

                Swal.fire({
                  title: 'Error',
                  text: '500 Internal Server Error!',
                  icon: 'error'
                })
            }
        })
    }
</script>
@endpush