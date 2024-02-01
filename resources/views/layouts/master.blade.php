<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RuangAjar') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Boostrap V.4.6.x Internal -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style-master.css') }}" rel="stylesheet">

    {{-- Toastr --}}
    <link rel="stylesheet" href="{{ asset('notif/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('notif/ext-component-toastr.min.css') }}">

    @stack('style')

    <style>
        .bg-custom {
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
            border: 0 !important;
            background-color: white !important;
        }

        .text-color {
            color: #62ddbd !important;
        }

        .btn-company {
            background-color: #62DDBD;
            color: white;
        }

        .btn-company:hover {
            color: white !important;
        }

        .bg-default-ruangajar {
            background-color: #62ddbd !important;
            color: white !important;
        }

        .card-custom {
            border-radius: 5px;
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
            border: 0;
        }

        .circle-notification-custom {
            background: #62ddbd;
            color: white;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            text-align: center;
            font-size: 11px;
        }

        /* Responsivity */
        @media screen and (max-width: 576px) {
            .fix-img-in-card {
                width: 100%;
                object-fit: cover;
                height: 50vw;
            }

            .fix-img-detail-course-package {
                height: 15vw;
                object-fit: cover;
            }
        }

        @media screen and (max-width: 768px) {
            .fix-img-in-card {
                width: 100%;
                object-fit: cover;
                height: 50vw;
            }

            .fix-img-detail-course-package {
                height: 70vw;
                object-fit: cover;
            }
        }

        @media screen and (max-width: 992px) {
            .fix-img-in-card {
                width: 100%;
                object-fit: cover;
                height: 50vw;
            }
        }

        @media screen and (min-width: 1200px) and (max-width: 1500px) {
            .fix-img-in-card {
                width: 100%;
                object-fit: cover;
                height: 12vw;
            }

            .fix-img-detail-course-package {
                height: 14vw;
                object-fit: cover;
            }
        }

        @media screen and (min-width: 1920px) and (max-width: 2000px) {
            .fix-img-in-card {
                width: 100%;
                object-fit: cover;
                height: 12vw;
            }

            .fix-img-detail-course-package {
                height: 10vw;
                object-fit: cover;
            }
        }
    </style>
</head>

<body>
    {{-- Global Value --}}
    <input type="hidden" id="base-url-env" value="{{ URL::to('/') }}">

    @if(auth()->check())
        <input type="hidden" id="authentication" value="true">
        <input type="hidden" id="user-id-account" value="{{ auth()->user()->id }}">
        <input type="hidden" id="user-role-id-account" value="{{ auth()->user()->role_id }}">
    @else
        <input type="hidden" id="authentication" value="false">
    @endif
    
    <div id="app">
        {{-- Navbar --}}
        <nav class="navbar navbar-expand-lg navbar-light bg-custom">
            <div class="container">
                <a class="navbar-brand intro-one-ruangajar" href="/">
                  <img src="{{ asset('img/ruang-ajar-logo.png') }}" alt="" srcset="" style="height:50px;">
                </a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <ul class="navbar-nav mr-auto">
                        {{-- Admin --}}
                        @if (auth()->user()->role_id == '10')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin-panel.dashboard') }}">Dashboard</a>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="take-down-data" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Master Data
                                </a>

                                <div class="dropdown-menu" aria-labelledby="take-down-data">
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                       Data User (Dummy)
                                    </a>

                                    <a class="dropdown-item" href="{{ route('admin.users.sellers') }}">
                                       Data Seller
                                    </a>

                                    <!-- <a class="dropdown-item" href="{{ route('admin.instructor.index') }}">
                                       @lang('label.instructor_list')
                                    </a> -->

                                    <!-- <a class="dropdown-item" href="{{ route('admin.take.down.data.course.package') }}">
                                       Daftar Produk
                                    </a> -->

                                    <!-- <a class="dropdown-item" href="{{ route('admin.transaction.index') }}">
                                       @lang('label.transaction_list')
                                    </a> -->

                                    <a class="dropdown-item" href="{{ route('e.wallet.index') }}">Withdraw</a>
                                    <a class="dropdown-item" href="{{ route('admin.autocomplete.config.autocomplete') }}">Setting Autocomplete - Kategori</a>
                                    <a class="dropdown-item" href="{{ route('list.transaction.autocomplete') }}">Setting Autocomplete - Detail Transaksi</a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="take-down-data" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Take Down Data
                                </a>

                                <div class="dropdown-menu" aria-labelledby="take-down-data">
                                    <a class="dropdown-item" href="{{ route('admin.take.down.data.course.package') }}">Produk</a>
                                    <a class="dropdown-item" href="{{ route('admin.take.down.data.users') }}">@lang('label.users_data')</a>
                                    <a class="dropdown-item" href="{{ route('admin.take.down.data.institution') }}">Data Toko</a>
                                    <a class="dropdown-item" href="{{ route('admin.take.down.data.video') }}">Video Higlight</a>
                                </div>
                            </li>
                        @endif

                        {{-- Management --}}
                        @if (auth()->user()->role_id == '2')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('management.dashboard') }}">Dashboard</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('management.course.package.index') }}">Daftar Produk</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('management.transaction.index') }}">
                                    @lang('label.transaction_list')
                                </a>
                            </li>
                        @endif

                        {{-- Mentor --}}
                        @if (auth()->user()->role_id == '1')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('company:profile.company_edit', [\Str::slug(auth()->user()->company->Name), 'company' => auth()->user()->company_id]) }}">@lang('label.course_institution_profile')</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link intro-two-new-account" href="{{ route('package.course.index') }}">@lang('label.my_course_package')</a>
                            </li>
    
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('e.wallet.index') }}">@lang('label.your_wallet')</a>
                            </li>
                        @endif

                        {{-- Student --}}
                        @if (auth()->user()->role_id == '6')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile.index') }}">@lang('label.account_profile')</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('member.course.index') }}">@lang('label.course_package')</a>
                            </li>
                        @endif

                        @if (auth()->user()->role_id == '6')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('course.transaction.index') }}">
                                    @lang('label.transaction_list')

                                    @if ($waitingPaymentGlobal > 0)
                                        <div class="float-right circle-notification-custom ml-1" id="circle-notif-animate">{{ $waitingPaymentGlobal }}</div>
                                    @endif
                                </a>
                            </li>
                        @endif

                        @if (auth()->user()->role_id == '1')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="transaction-dropdown-menu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @lang('label.transaction_list')
                                </a>

                                <div class="dropdown-menu" aria-labelledby="transaction-dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('course.transaction.index') }}?tipe=online">@lang('label.transaction_online')</a>
                                    <a class="dropdown-item" href="{{ route('course.transaction.index') }}?tipe=offline">@lang('label.transaction_offline')</a>
                                </div>
                            </li>
                        @endif
                    </ul>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown pt-2">
                            <a class="nav-link" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa-solid fa-globe text-color"></i> @lang('label.language')
                            </a>
                            
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('lang.switch', 'id') }}">
                                    <img src="{{ asset('img/indonesia.png') }}" alt="" style="width: 20px; height: 20px;"> Indonesia
                                </a>

                                <a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">
                                    <img src="{{ asset('img/uk.png') }}" alt="" style="width: 20px; height: 20px;"> Inggris
                                </a>
                            </div>
                        </li>

                        <li class="nav-item pt-2">
                            @if (auth()->user()->role_id == 1)
                                <a href="{{ route('package.course.index') }}" class="nav-link"><i class="fas fa-book text-color"></i> {{ $courseGlobal }} @lang('label.package')</a>
                            @elseif (auth()->user()->role_id == 6)
                                <a href="{{ route('member.course.index') }}?my-course=true" class="nav-link"><i class="fas fa-book text-color"></i> {{ $courseGlobal }} @lang('label.my_course_package')</a>
                            @endif
                        </li>

                        @if (auth()->user()->role_id == 6)
                            <li class="nav-item pt-2">
                                <a href="{{ route('cart.index') }}" class="nav-link"><i class="fas fa-shopping-cart text-color"></i> <span id="cart-global-element">{{ $cartsGlobal }}</span> @lang('label.course_package')</a>
                            </li>
                        @endif

                        @if (auth()->user()->role_id != 2)
                            <li class="nav-item pt-2">
                                <a class="nav-link" href="{{ route('e.wallet.index') }}"><i class="fas fa-wallet text-color"></i> {{ rupiah($walletGlobal) }}</a>
                            </li>
                        @endif

                        <li class="nav-item pt-2">
                            <a class="nav-link" href="{{ route('chats.index') }}"><i class="fas fa-message text-color"></i> <span id="count-unread-message-global">{{ $unreadCountGlobal }}</span> @lang('label.message')</a>
                        </li>

                        <li class="nav-item pt-2">
                            <a class="nav-link" href="{{ route('auth.logout_process') }}"> <i class="fa fa-sign-out text-color"></i> @lang('label.logout')</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Content --}}
        <main class="py-2-hide mt-4" style="background-color: #faf8ff;">
            @yield('content')
        </main>
    </div>

    {{-- Jquery V.1.10.1 --}}
    {{-- <script src="{{ asset('js/jquery.js') }}"></script> --}}

    {{-- Jquery V.1.11.1 --}}
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script> --}}

    {{-- Jquery Ui V.1.11.1 --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" integrity="sha512-PYku51kWkxxuh0OiQHi8INwfDEVcEe9JYBiZCA21G0ITGdEUU7scEhTyutt69jK591vKJmBhPMP+yYMd6J88nQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}

    {{-- Jquery V.3.6.0 and Jquery UI v.1.12.1 --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script> --}}

    {{-- <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js" integrity="sha256-6XMVI0zB8cRzfZjqKcD01PBsAy3FlDASrlC8SxCpInY=" crossorigin="anonymous"></script> --}}

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    {{-- /Jquery V.3.6.0 and Jquery UI v.1.12.1 --}}

    {{-- Jquery Scroll --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.4.1/jquery.jscroll.min.js" defer></script> --}}
    
    {{-- For Inlucde Jquery, Jquery UI, etc  --}}
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}

    {{-- Jquery Stable Version --}}
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Jquery Stable Version --}}
    
    {{-- Library --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://kit.fontawesome.com/965792c645.js" crossorigin="anonymous"></script>

    {{-- Toastr --}}
    <script src="{{ asset('notif/toastr.min.js') }}"></script>
    <script src="{{ asset('notif/ext-component-toastr.min.js') }}"></script>

    {{-- Global Config --}}
    <script>
        {{-- Initialize --}}
        let csrfToken   = $('meta[name="csrf-token"]').attr('content')
        let baseUrl     = $('#base-url-env').val()
        let homeUrl     = $('#home-url').val()
        let userId      = $('#user-id-account').val()
        let userRoleId  = $('#user-role-id-account').val()
        let companyName = $('#company-name').val()
        let t,o         = "rtl" === $("html").attr("data-textdirection")
    </script>

    {{-- Logout --}}
    <script type="text/javascript">
        // Trigger Input Type File
        $(document).on('click', '#report-upload-area', function () {
            $('#report-upload-file').click()
        })

        $(document).on('submit', '#checkout-modal form', function (e) {
            e.preventDefault()

            // Initialize
            let url = $('#checkout-url').val()

            // Disabled Button True
            $('.checkout-button').attr('disabled', true)

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
                        $('.checkout-button').attr('disabled', false)

                        return 0
                    }

                    // Hide Modal
                    $('#checkout-modal').modal('hide')

                    Swal.fire({
                      title: 'Sukses',
                      text: `${data.message}`,
                      icon: 'success'
                    }).then((result) => {
                      if (result.isConfirmed) {
                        window.location.replace(`${baseUrl}`)
                      }
                    })
                },
                error: e => {
                    console.log(e)

                    // Disabled Button False
                    $('.checkout-button').attr('disabled', false)

                    Swal.fire({
                      title: 'Error',
                      text: '500 Internal Server Error!',
                      icon: 'error'
                    })
                }
            })
        })
    </script>

    {{-- Global Function --}}
    <script>
        $(document).ready(function () {
            // Call Function
            configTooltip()
        })
        
        function configTooltip() {
            $('.config-tooltip').tooltip()
        }
    </script>

    <script>
        setInterval(function () {
            if ($('#circle-notif-animate').attr('hide') == 'true') {
                $('#circle-notif-animate').css('display', 'none')
                $('#circle-notif-animate').attr('hide', 'false')
            } else {
                $('#circle-notif-animate').attr('hide', 'true')
                $('#circle-notif-animate').css('display', '')
            }
        }, 1000)
    </script>

    @stack('script')
</body>
</html>
