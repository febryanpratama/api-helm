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
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    
    {{-- <link rel="stylesheet" href="{{ asset('css/apps.css') }}"> --}}

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    {{-- Toastr --}}
    <link rel="stylesheet" href="{{ asset('notif/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('notif/ext-component-toastr.min.css') }}">

    @php
        // Initialze
        $color      = '#62DDBD';
        $href_color = '#faf8ff';
    @endphp
    
    <style>
        .card-header {
            color: #faf8ff;
            border-radius: 15px 15px 15px 15px;
            background-color: {{{ $color }}};
        }

        a {
            color: {{{ $href_color }}}
        }

        .card.border-custom {
            border-color:{{{ $color }}};
        }

        /* Home */
        .text-company {
            color: {{{ $color }}};
        }

        .btn-company {
            background-color: {{{ $color }}};
        }

        .bg-company {
            background-color: {{{ $color }}};
            color: white;
        }

        .btn-outline-company {
            border: 1px solid {{{ $color }}};
            background-color: white;
        }

        .border-company {
            border: 1px solid {{{ $color }}};
        }

        .popover-header {
            background-color: {{{ $color }}} !important;
            color: white;
        }

        .count-message {
            font-size: 0.933em;
            padding: 0.5em 0;
            width: 21px;
            height: 21px;
            flex-shrink: 0;
            line-height: 0.72;
            font-weight: normal;
            text-align: center;
            letter-spacing: -1px;
            border-radius: 50px;
            margin-left: 0.214em;
            background-color: red;
        }

        .btn-download-file-message {
            color: #17a2b8;
            background-color: transparent;
            background-image: none;
            border-color: #17a2b8;
        }

        .btn-download-file-message:hover {
            color: white;
            background-color: #17a2b8;
            background-image: none;
            border-color: #17a2b8;
        }

        .form-control-sm-custom {
            height: calc(1.5em + 0.5rem + 5px);
            padding: 0.25rem 0.5rem;
            font-size: 0.7875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .mt-06 {
            margin-top: 0.6rem !important;
        }

        #navbar-search-element {
            transition: all 0.5s ease-out;
        }

        .hover-tag-footer:hover {
            color: black;
        }

        .card-custom {
            border-radius: 5px;
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
            border: 0 !important;
        }

        .text-color {
            color: #62ddbd !important;
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

    @stack('style')
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
        <div class="web-nav">
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="/">
                        <img src="{{ asset('img/ruang-ajar-logo.png') }}" alt="" srcset="" style="height: 50px; width: 60px;">
                    </a>
                    
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>
    
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="/">@lang('label.home_page')</a>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profile-dropdown-menu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @lang('label.product')
                                </a>

                                <div class="dropdown-menu" aria-labelledby="profile-dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('product.index') }}?tags=kelola-lembaga-kursus">@lang('label.manage_course_institution')</a>
                                    <a class="dropdown-item" href="{{ route('product.index') }}?tags=kelola-paket-kursus">@lang('label.manage_course_package')</a>
                                    <a class="dropdown-item" href="{{ route('product.index') }}?tags=kelola-tatap-muka">@lang('label.manage_face_to_face')</a>
                                    <a class="dropdown-item" href="{{ route('product.index') }}?tags=diskusi-dan-komunikasi">@lang('label.discussion_and_communication')</a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profile-dropdown-menu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @lang('label.service')
                                </a>

                                <div class="dropdown-menu" aria-labelledby="profile-dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('service.index') }}?tags=cara-buka-lembaga-kursus">@lang('label.how_to_open_a_course')</a>
                                    <a class="dropdown-item" href="{{ route('service.index') }}?tags=cara-cari-mentor">@lang('label.how_to_find_a_mentor')</a>
                                    <a class="dropdown-item" href="{{ route('service.index') }}?tags=cara-cari-paket-kursus">@lang('label.how_to_find_course_packages')</a>
                                    <a class="dropdown-item" href="{{ route('service.index') }}?tags=cara-beli-paket-kursus">@lang('label.how_to_buy_a_cource_package')</a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profile-dropdown-menu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @lang('label.help')
                                </a>

                                <div class="dropdown-menu" aria-labelledby="profile-dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('help.index') }}?tags=faq">@lang('label.faq')</a>
                                    <a class="dropdown-item" href="{{ route('help.index') }}?tags=kebijakan-privasi">@lang('label.privasi_policy')</a>
                                    <a class="dropdown-item" href="{{ route('help.index') }}?tags=syarat-dan-ketentuan">@lang('label.term_and_conditions')</a>
                                </div>
                            </li>
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ml-auto">
                            <!-- Authentication Links -->
                            @guest
                                <li class="nav-item dropdown">
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

                                <li class="nav-item dropdown">
                                    <a class="nav-link" href="{{ route('auth.signin') }}" id="sigin-account" v-pre>
                                        <i class="fa-solid fa-right-from-bracket text-color"></i> Masuk
                                    </a>
                                </li>
                            @else
                                @if (auth()->user()->role_id == 10)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin-panel.dashboard') }}"><i class="fas fa-user text-color"></i> Dashboard</a>
                                    </li>
                                @endif

                                @if (auth()->user()->role_id == 6)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('profile.index') }}"><i class="fas fa-user text-color"></i> Profil Akun</a>
                                    </li>
                                @elseif (auth()->user()->role_id == 1)
                                    @if (auth()->user()->company)
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('company:profile.company_edit', [\Str::slug(auth()->user()->company->Name), 'company' => auth()->user()->company_id]) }}">
                                                <i class="fas fa-user text-color"></i> @lang('label.course_institution_profile')
                                            </a>
                                        </li>
                                    @endif
                                @endif
                                
                                <li class="nav-item dropdown">
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

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('auth.logout_process') }}"> <i class="fa fa-sign-out text-color"></i> @lang('label.logout')</a>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <main class="py-2-hide" style="background-color: #faf8ff;">
            @yield('content')
        </main>
    </div>

    {{-- Jquery --}}
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    
    <script src="{{ asset('js/app.js') }}" defer></script>

    {{-- Jquery Ui --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" integrity="sha512-PYku51kWkxxuh0OiQHi8INwfDEVcEe9JYBiZCA21G0ITGdEUU7scEhTyutt69jK591vKJmBhPMP+yYMd6J88nQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.4.1/jquery.jscroll.min.js" defer></script>
    
    <script src="{{ asset('js/script.js') }}"></script>
    
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

    @stack('script')
</body>
</html>
