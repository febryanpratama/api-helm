<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/apps.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropzone.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lightbox.css') }}"> 
    <link rel="stylesheet" href="{{ asset('css/jquery.atwho.css') }}"> 
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <script src="{{ asset('js/jquery.js') }}"></script>
    
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    {{-- Jquery Ui --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" integrity="sha512-PYku51kWkxxuh0OiQHi8INwfDEVcEe9JYBiZCA21G0ITGdEUU7scEhTyutt69jK591vKJmBhPMP+yYMd6J88nQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.4.1/jquery.jscroll.min.js" defer></script>
    <script src="{{ asset('js/dropzone.js') }}"></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    {{-- Swiper --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/css/swiper.min.css" integrity="sha256-DBYdrj7BxKM3slMeqBVWX2otx7x4eqoHRJCsSDJ0Nxw=" crossorigin="anonymous"/>

    @php
        $color = '#c02c4b';
        $href_color = '#faf8ff';
    if (auth()->user() && auth()->user()->company && auth()->user()->company->Color) {
        $color = auth()->user()->company->Color;
    }

    if (guestCompanyColor()) {
        $color = guestCompanyColor();
    }
        // if (auth()->user() && auth()->user()->role_id != '10' && auth()->user()->company->Type == 'private_office') {
        //     $color = '#FCCCD4';
        //     $href_color = '';
        // } else if (auth()->user() && auth()->user()->role_id != '10' && auth()->user()->company->Type == 'government_agency') {
        //     $color = '#8EB695';
        //     $href_color = '#faf8ff';
        // } else if (auth()->user() && auth()->user()->role_id != '10' && auth()->user()->company->Type == 'school') {
        //     $color = '#FBDEA2';
        //     $href_color = '';
        // } else if (auth()->user() && auth()->user()->role_id != '10' && auth()->user()->company->Type == 'public_office') {
        //     $color = '#E87C67';
        //     $href_color = '#faf8ff';
        // }
    @endphp
    
    {{-- 
        Note :
        Css selebihnya disimpan pada file
        style.css
    --}}
    
    <style>
        .card-header {
            color: #faf8ff;
            border-radius: 0px 15px 0px 15px;
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
    </style>

    @stack('style')
</head>
<body>
    {{-- Global Value --}}
    <input type="hidden" id="base-url-env" value="{{ URL::to('/') }}">
    @if(auth()->check())
        @if(auth()->user()->company)
            <input type="hidden" id="home-url" value="{{ route('company:home', \Str::slug(auth()->user()->company->Name)) }}">
            <input type="hidden" id="company-name" value="{{ \Str::slug(auth()->user()->company->Name) }}">
        @endif

        <input type="hidden" id="authentication" value="true">
        <input type="hidden" id="user-id-account" value="{{ auth()->user()->id }}">
        <input type="hidden" id="user-role-id-account" value="{{ auth()->user()->role_id }}">
    @else
        <input type="hidden" id="authentication" value="false">
    @endif
    
    <div id="app">
        @guest
            <div class="m-bottom-bar">
                <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                    <div class="container d-flex">
                        <a class="navbar-brand" href="{{ route('landing') }}" style="text-align:center">
                            <img src="{{ companyLogo() }}" alt="" srcset="" style="height:50px;">
                        </a>
                        <a class="navbar-brand" href="{{route('auth.index')}}">Login</a>
                    </div>
                </nav>
            </div>
        @else
            <div class="m-bottom-bar">
                <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                    <div class="container d-flex justify-content-center">
                        @if (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0 && auth()->user()->company && auth()->user()->company->IsConfirmed == 'n') 
                            <a class="navbar-brand" href="{{ route('transaction.package') }}">
                        @elseif (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0)
                            <a class="navbar-brand" href="{{ route('transaction.agency') }}">
                        @elseif (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) > 0)
                            <a class="navbar-brand" href="{{ route('transaction.detail', auth()->user()->transaction[0]->payment->ID) }}">
                        @elseif (auth()->user() && auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y' && auth()->user()->company->Address == '-')
                            <a class="navbar-brand" href="{{ route('profile.company_edit', ['company' => auth()->user()->company_id]) }}">
                        @else
                        <a class="navbar-brand" href="{{ auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : route('home') }}" style="text-align:center">
                        @endif
                            <img src="{{ companyLogo() }}" alt="" srcset="" style="height:50px;">
                        </a>
                    </div>
                </nav>
            </div>
        @endguest

        <div class="web-nav">
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    @if (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0 && auth()->user()->company && auth()->user()->company->IsConfirmed == 'n')
                        <a class="navbar-brand" href="{{ route('transaction.package') }}">
                    @elseif (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0)
                        <a class="navbar-brand" href="{{ route('transaction.agency') }}">
                    @elseif (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) > 0)
                        <a class="navbar-brand" href="{{ route('transaction.detail', auth()->user()->transaction[0]->payment->ID) }}">
                    @elseif (auth()->user() && auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y' && auth()->user()->company->Address == '-' )
                        <a class="navbar-brand" href="{{ route('profile.company_edit', ['company' => auth()->user()->company_id]) }}">
                    @else
                    <a class="navbar-brand" href="{{ auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : route('home') }}">
                    @endif
                        {{-- {{ config('app.name', 'Laravel') }} --}}
                        <img src="{{ companyLogo() }}" alt="" srcset="" style="height:50px;">
    
                        {{-- <span style="margin-top:20px;">
                            <strong>
                                KPP Karangpilang
                            </strong>
                        </span> --}}
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>
    
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    @lang('label.product')
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{route('landing.product')}}">
                                        @lang('label.manage_user')
                                    </a>
                                    <a class="dropdown-item" href="{{route('landing.product') . '#project_management'}}">
                                        @lang('label.manage_project')
                                    </a>
                                    <a class="dropdown-item" href="{{route('landing.product') . '#task_management'}}">
                                        @lang('label.manage_task')
                                    </a>
                                    <a class="dropdown-item" href="{{route('landing.product') . '#knowledge_management'}}">
                                        @lang('label.manage_knowledge')
                                    </a>
                                    <a class="dropdown-item" href="{{route('landing.product') . '#chat'}}">
                                        @lang('label.internal_external_comunicate')
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    @lang('label.services')
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{route('landing.service') . '#package'}}">
                                        @lang('label.how_choose_package')
                                    </a>
                                    <a class="dropdown-item" href="{{route('landing.service') . '#promo'}}">
                                        @lang('label.how_extend_package')
                                    </a>
                                    <a class="dropdown-item" href="{{route('landing.service') . '#payment'}}">
                                        @lang('label.how_payment')
                                    </a>
                                    @if (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0 && auth()->user()->company && auth()->user()->company->IsConfirmed == 'n')
                                        <a class="dropdown-item" href="{{route('transaction.agency')}}">
                                    @elseif (auth()->user() && auth()->user()->is_active == 'n' && count(auth()->user()->transaction) > 0)
                                        <a class="dropdown-item" href="{{ route('transaction.detail', auth()->user()->transaction[0]->payment->ID) }}">
                                    @elseif (auth()->user() && auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y' && auth()->user()->company->Address == '-' )
                                        <a class="dropdown-item" href="{{ route('profile.company_edit', ['company' => auth()->user()->company_id]) }}">
                                    @else
                                    <a class="dropdown-item" href="{{ auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : route('home') }}">
                                    @endif
                                        @lang('label.subscribe_now')
                                    </a>
                                </div>
                            </li>
                        </ul>

                        {{-- Center Side Of Navbar --}}
                        <ul class="navbar-nav">
                            <li>
                                <form action="" id="form-search">
                                    <input type="text" class="form-control rounded-pill icon-input" id="search" name="search" placeholder="Cari proyek, task, catatan, memo .." value="{{request()->get('search')}}" style="
                                    width: 300px;
                                    border: 2px solid #160101;
                                ">
                                </form>
                            </li>
                            {{-- <li>
                                <a href="#" data-toggle="modal" data-target="#filter" class="btn btn-primary rounded-pill" title="Filter"> <i class="fa fa-filter" style="color:black"></i></a>
                            </li> --}}
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ml-auto">
                            <!-- Authentication Links -->
                            @guest
                                {{-- <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif --}}
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="{{ route('auth.index') }}" v-pre>
                                        Login
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="{{ route('lang.switch', 'id') }}" v-pre>
                                        <img src="{{ asset('img/indonesia.png') }}" alt="" style="width: 20px; height: 20px;">
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="#" v-pre>
                                        |
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="{{ route('lang.switch', 'en') }}" v-pre>
                                        <img src="{{ asset('img/uk.png') }}" alt="" style="width: 20px; height: 20px;">
                                    </a>
                                </li>
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }} <span class="caret"></span>
                                    </a>
    
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                                            @lang('label.profile')
                                        </a>
                                        
                                        @if (auth()->user()->role_id == 1 && auth()->user()->is_active == 'y' && auth()->user()->company_id != null)
                                            {{-- <a class="dropdown-item" href="{{ route('company:user.data', \Str::slug(auth()->user()->company->Name)) }}">
                                                @lang('label.data_user')
                                            </a> --}}
                                            <a class="dropdown-item" href="{{ route('company:report.task_attendance', \Str::slug(auth()->user()->company->Name)) }}">
                                                @lang('label.report')
                                            </a>
                                            <a class="dropdown-item" href="{{ route('company:profile.company_edit', [\Str::slug(auth()->user()->company->Name), 'company' => auth()->user()->company_id]) }}">
                                                {{auth()->user()->company->Name}}
                                            </a>
                                        @endif

                                        @if (auth()->user()->role_id == 9)
                                            <a class="dropdown-item" href="{{ route('home') }}">
                                                Home
                                            </a>
                                        @endif
    
                                        @if (auth()->user()->role_id == 6)
                                            <a class="dropdown-item" href="{{ route('user.report.index') }}">
                                                @lang('label.report')
                                            </a>
                                            <a href="{{ route('task.index_member') }}"  class="dropdown-item">@lang('label.task')</a>
                                        @endif
    
                                        <a href="{{ route('chat.area.index') }}" class="dropdown-item">@lang('label.chat')</a>
                                        
                                        @if (auth()->user()->role_id != 1)
                                            {{-- <a class="dropdown-item" href="{{ route('auth.logout') }}"
                                            onclick="event.preventDefault();
                                                            document.getElementById('logout-form').submit();">
                                                {{ __('Logout') }}
                                            </a>
    
                                            <form id="logout-form" action="{{ route('auth.logout') }}" method="get" style="display: none;">
                                                @csrf
                                            </form> --}}

                                            <a href="javascript:void(0)" class="dropdown-item" data-toggle="modal" data-target="#checkout-modal">{{ __('Logout') }}</a>
                                        @endif
    
                                        @if (auth()->user()->role_id == 1)
                                            {{-- <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                            document.getElementById('logout-form').submit();">
                                                {{ __('Logout') }}
                                            </a>
    
                                            <form id="logout-form" action="{{ route('logout') }}" method="post" style="display: none;">
                                                @csrf
                                            </form> --}}
                                            <a class="dropdown-item" href="{{ route('auth.logout') }}"
                                            onclick="event.preventDefault();
                                                            document.getElementById('logout-form').submit();">
                                                {{ __('Logout') }}
                                            </a>
    
                                            <form id="logout-form" action="{{ route('auth.logout') }}" method="get" style="display: none;">
                                                @csrf
                                            </form>
                                        @endif
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="{{ route('lang.switch', 'id') }}" v-pre>
                                        <img src="{{ asset('img/indonesia.png') }}" alt="" style="width: 20px; height: 20px;">
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="#" v-pre>
                                        |
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="{{ route('lang.switch', 'en') }}" v-pre>
                                        <img src="{{ asset('img/uk.png') }}" alt="" style="width: 20px; height: 20px;">
                                    </a>
                                </li>

                                <li class="nav-item dropdown notif-drop">
                                    <a class="nav-link" style="color:#ea852e" href="#" id="notifications" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bell" data-count="0"></i>(<b><span class="count">0</span></b>)
                                    </a>
                                    {{-- <a href="#" class="dropdown-toggle select-dropdown" id="notifications"><i class="fa fa-bell-o" data-count="0"></i>(<b><span class="notif-count">0</span></b>)</a> --}}
                                    <ul class="dropdown-menu">
                                        <li class="head text-light" style="background-color: #c02c4b">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 col-12">
                                                    <span>@lang('label.notification') (
                                                        <span class="count">
                                                        0
                                                        </span>
                                                        )</span>
                                                    <a href="#" class="float-right text-light">@lang('label.mark_all_as_read')</a>
                                                </div>
                                            </div>
                                        </li>
                                        <div class="notif-content" aria-labelledby="notificationsMenu" id="notificationsMenu">
                                            
                                        </div>
                                        {{-- <li class="notification-box">
                                            <div class="row">
                                                <div class="col-lg-3 col-sm-3 col-3 text-center">
                                                    <img src="{{asset('img/300x300.png')}}" class="w-50 rounded-circle">
                                                </div>
                                                <div class="col-lg-8 col-sm-8 col-8">
                                                    <strong class="text-info">David John</strong>
                                                    <div>
                                                        Lorem ipsum dolor sit amet, consectetur
                                                    </div>
                                                    <small class="text-warning">27.11.2015, 15:00</small>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="notification-box bg-gray">
                                            <div class="row">
                                                <div class="col-lg-3 col-sm-3 col-3 text-center">
                                                    <img src="{{asset('img/300x300.png')}}" class="w-50 rounded-circle">
                                                </div>
                                                <div class="col-lg-8 col-sm-8 col-8">
                                                    <strong class="text-info">David John</strong>
                                                    <div>
                                                        Lorem ipsum dolor sit amet, consectetur
                                                    </div>
                                                    <small class="text-warning">27.11.2015, 15:00</small>
                                                </div>
                                            </div>
                                        </li> --}}
                                        <li class="footer text-center" style="background-color: #c02c4b">
                                        <a href="{{ route('notif.index') }}" class="text-light">@lang('label.view_all')</a>
                                        </li>
                                    </ul>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        {{-- Deadline Information --}}
        {{-- @if (auth()->check())
            @if (count($deadlinesGlobalVar) > 0)
                <div class="w-100">
                  <div class="text-center">
                      <div class="alert alert-danger">
                        <div class="swiper-container swiper-container-deadline">
                            <div class="swiper-wrapper">
                                @foreach($deadlinesGlobalVar as $deadline)
                                <div class="swiper-slide">
                                   Tugas Anda <b style="color: #FF0000;">{{ $deadline['name'] }}</b> akan mendekati Deadline!
                                </div>
                                @endforeach
                            </div>
                        </div>           
                      </div>
                  </div>
                </div>
            @endif
        @endif --}}

        <main class="py-2" style="background-color: #faf8ff;">
            @yield('content')

            @if (auth()->user() && auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y')
                <a class="open-button" style="border-radius: 15px 15px 0px 0px;background-color:rgb(11, 120, 163);bottom:0px;" href="{{ route('chat.area.index') }}" target="_blank">
                    <div class="clearfix" style="padding-left: 36%;">
                        <div class="count-message float-left">0</div>
                        <div class="float-left ml-2">Chat</div>
                    </div>
                </a>
            @endif
        </main>

        @guest


        @else
            <div class="m-bottom-bar" style="margin-top:30px;">
                <nav class="navbar fixed-bottom navbar-light bg-light">
                    {{-- <a class="navbar-brand" href="{{ auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : route('home') }}"><i class="fa fa-tasks" style="font-size: 30px;"></i></a> --}}
                    <a class="navbar-brand" href="{{ route('notif.index') }}"><i class="fa fa-bell" style="font-size: 30px;"></i></a>
                    @if (auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0)
                        <a class="navbar-brand" href="{{ route('transaction.agency') }}"><i class="fa fa-home" style="font-size: 30px;"></i></a>
                    @elseif (auth()->user()->is_active == 'n' && count(auth()->user()->transaction) > 0)
                        <a class="navbar-brand" href="{{ route('transaction.detail', auth()->user()->transaction[0]->payment->ID) }}"><i class="fa fa-home" style="font-size: 30px;"></i></a>
                    @elseif (auth()->user() && auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y' && auth()->user()->company->Address == '-')
                        <a class="navbar-brand" href="{{ route('profile.company_edit', ['company' => auth()->user()->company_id]) }}">
                    @else
                    <a class="navbar-brand" href="{{ auth()->user() && auth()->user()->company ? route('company:home', \Str::slug(auth()->user()->company->Name)) : route('home') }}"><i class="fa fa-home" style="font-size: 30px;"></i></a>
                    @endif
                    @if (auth()->user()->role_id != 1)
                        <a href="javascript:void(0)" class="navbar-brand" data-toggle="modal" data-target="#checkout-modal"><i class="fa fa-sign-out" style="font-size: 30px;"></i></a>
                        {{-- <a class="navbar-brand" href="{{ route('auth.logout') }}"><i class="fa fa-sign-out" style="font-size: 30px;"></i></a> --}}
                    @endif

                    @if (auth()->user()->role_id == 1)
                        <a class="navbar-brand" href="{{ route('logout') }}" onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();"><i class="fa fa-sign-out" style="font-size: 30px;"></i></a>

                        <form id="logout-form" action="{{ route('logout') }}" method="post" style="display: none;">
                            @csrf
                        </form>
                    @endif
                </nav>
            </div>
        @endguest
        
    </div>
        <!-- Modal -->
        <div class="modal fade" id="motivation_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Check</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('check.store')}}" method="post">
                        @csrf
                        <div class="modal-body">
                        {{auth()->user() && auth()->user()->company && auth()->user()->company->motivationRandom() && isset(auth()->user()->company->motivationRandom()[0]) ? auth()->user()->company->motivationRandom()[0]->motivation : ''}}
                        <input type="hidden" id="location" name="location" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('label.close')</button>
                            <button type="submit" class="btn btn-primary">Check</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- modal admin expired subscribe --}}
        <div class="modal fade" id="expired_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Expired</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="javascript:window.location='/subscribe/package'">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <h5>Paket anda sudah expired harap segera memperpanjang paket anda</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="javascript:window.location='/subscribe/package'">@lang('label.close')</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- modal member expired subscribe --}}
        <div class="modal fade" id="expired_modal_member" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Expired</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="javascript:window.location='/subscribe/package'">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <h5>Paket anda sudah expired harap hubungi admin anda untuk segera memperpanjang paket</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="javascript:window.location='/subscribe/package'">@lang('label.close')</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="grup_chat" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">@lang('label.new_group')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('conversation.create') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <input type="text" name="title" placeholder="@lang('placeholder.title')" required  
                                 class="form-control" id="title_group" autofocus>
        
                            </div>
        
                            <div class="form-group">
                                <input type="text" name="description" placeholder="@lang('placeholder.description')"  
                                 class="form-control">
                                 
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">@lang('button.save')</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('label.close')</button>
                            <button type="submit" class="btn btn-primary">@lang('button.save')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="edit_grup_chat" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">@lang('label.edit_group')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="#" method="post" enctype="multipart/form-data" id="form_edit_group">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <input type="text" name="title" required placeholder="title" class="form-control" id="group_title">
        
                            </div>
        
                            <div class="form-group">
                                <input type="text" name="description" placeholder="Description"  
                                 class="form-control" id="group_desc">
                                 
                            </div>
                            <div class="modal-footer">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">@lang('button.save')</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @php
            $assign_user = array();
            if (auth()->user() && auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y') {
                $assign_user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
            }
        @endphp
        <div class="modal fade" id="add_member" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">@lang('label.add_member')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="#" method="post" enctype="multipart/form-data" id="form_member_add">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <select name="participant[]" class="form-control participant" required placeholder="" multiple="multiple" style="width:466px;">
                                    @foreach ($assign_user as $user)
                                        @if (auth()->user()->id == $user->id)
                                        
                                        @else
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>

                            </div>
                            <div class="modal-footer">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="btn_save_member">@lang('button.save')</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- delete group --}}
        <form action="#" method="post" id="delete-group_form">
            @csrf
            @method('delete')
        </form>

        {{-- chat --}}

        <div class="chat-popup" id="myForm" style="max-width: 50%;max-height: 70%;box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
            <div class="container">
                <div class="row" style="text-align: end;">
                    <div class="col-md-12 bg-white">
                        {{-- <button type="button" class="btn btn-sm btn-primary" onclick="closeForm()">ay<i class="fa fa-times"></i></button> --}}
                        <a href="#"onclick="closeForm()"><strong>
                            <i class="fa fa-chevron-down" style="color:#222;font-size:24px;margin-top:11px;"></i>    
                        </strong></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 bg-white " style="width:540px;">
                        <div class=" row border-bottom padding-sm" style="height: 40px;">
                            <div class="col-md-12">
                                <strong>
                                    Group
                                </strong>
                                <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#grup_chat"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        
                        <!-- =============================================================== -->
                        <!-- member list -->
                        <ul class="friend-list" id="list-group">
                                        
                        </ul>
                    </div>
                    
                    <!--=========================================================-->
                    <!-- selected chat -->
                    <div class="col-md-8 bg-white "style="width:480px;height:70%;">
                        <div class="row" id="group_body" style="display:none;">
                            <div class="col-md-12 d-flex justify-content-between align-items-center" style="text-align: center">
                                <strong style="font-size:20px;" id="grup_name"></strong>
                                <div class="float-sm-right">
                                    <a href="#" class="" id="" data-toggle="modal" data-target="#add_member" style="color:black">
                                        <i class="fa fa-user-plus"></i>
                                    </a>
                                    
                                    <a href="#" class="" id="" data-toggle="modal" data-target="#edit_grup_chat" style="color:black">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    
                                    <a href="#" class="" onclick="if (confirm('Are you sure?'))" style="color:black" id="delete-group">
                                        <i class="fa fa-trash"></i>
                                    </a>

                                    <a href="#search-message-btn" class="" style="color:black" id="search-message" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="search-message-btn">
                                        <i class="fa fa-search"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="collapse w-100" id="search-message-btn">
                                <div class="col-md-12 col-12 mt-2">
                                    <div class="card" style="border: 1px solid white !important;">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8 col-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" id="search-message-input" placeholder="Cari Pesan ...">
                                                    </div>
                                                </div>

                                                <div class="col-md-4 col-4 mt-1">
                                                    {{-- Hidden Element --}}
                                                    <input type="hidden" id="position-message" value="1">

                                                    <button class="btn btn-sm btn-primary" id="search-all-message-btn">
                                                        @lang('label.search')
                                                    </button>

                                                    <button class="btn btn-sm btn-primary search-btn-message-up-down" style="display: none;" id="up-message-btn">
                                                        <i class="fa fa-angle-up"></i>
                                                    </button>

                                                    <button class="btn btn-sm btn-primary search-btn-message-up-down" style="display: none;" id="down-message-btn">
                                                        <i class="fa fa-angle-down"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="chat-message" style="height:380px;padding-left:0px;padding-left: 5px;">
                            <ul class="chat getchat" id="getchat" style="max-height:90%; overflow-y:scroll;padding:0px;">
                                
                            </ul>
                        </div>
                        <div class="chat-box bg-white" style="position: fixed; bottom: 0px; width: 32%;">
                            {{-- Replay Message Show Element --}}
                            <div class="row" id="replay-message-show-element" style="display: none;">
                                {{-- Hidden Element --}}
                                <input type="hidden" id="replay-message-id">
                                <div class="col-md-8 col-8 ml-3 mb-1" style="border-left: 2px solid #38c172;">
                                    <div class="clearfix" id="">
                                        <div class="header" style="border-bottom: 1px solid #00000024;">
                                            <strong class="primary-font" id="replay-name-message"></strong>
                                            <small class="pull-right text-muted"><i class="fa fa-clock-o"></i> <span id="replay-time-message"></span></small>

                                            <div class="float-right cursor-area" id="replay-message-close">
                                                <i class="fa fa-times"></i>
                                            </div>
                                        </div>
                                        
                                        <p id="replay-body-message"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Button Action Message --}}
                            <div class="row">
                                <div class="col-md-7 col-7">
                                    <input class="form-control border no-shadow no-rounded" placeholder="Type your message here" id="chatTextarea">
                                    <input type="file" name="file_upload" id="file-upload-message" placeholder="" style="display: none;">
                                </div>

                                <div class="col-md-1 col-1">
                                    <button class="btn btn-link text-dark mr-2" id="btn-file-upload-message">
                                      <i class="fa fa-paperclip fa-lg" id="color-paperclip-message"></i>
                                    </button>
                                    
                                </div>
                                <div class="col-md-1 col-1">
                                    <button class="btn btn-success no-rounded" type="button" id="btn_send">@lang('label.send')</button>
                                </div>
                            </div>
                            {{-- <div class="input-group">
                                <input class="form-control border no-shadow no-rounded" placeholder="Type your message here" id="chatTextarea">
                                <span class="input-group-btn">
                                    <button class="btn btn-success no-rounded" type="button" id="btn_send">@lang('label.send')</button>
                                </span>
                            </div> --}}
                            <!-- /input-group -->   
                        </div>            
                    </div>        
                </div>
            </div>
        </div>
        {{-- end --}}
    
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script> --}}

    @if (auth()->user())
        @php
            $subjects_users = auth()->user()->getDivisionMajorsSubjectData();
        @endphp
        {{-- modal filter --}}
        <div class="modal fade" id="filter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Filter</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="#" method="get">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request()->get('start_date') }}">
                            </div>
                            <div class="form-group">
                            <label for="">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request()->get('end_date') }}">
                            </div>
                            <div class="form-group">
                                <label for="">Subject</label>
                                <select name="subject[]" class="form-control" id="subject_filter" placeholder="" multiple="multiple" style="width:466px;">
                                    @foreach ($subjects_users as $item)
                                    <option value="">Pilih</option>
                                        <option value="{{ $item->ID }}" {{ request()->get('subject') == $item->ID ? 'selected' : '' }}>{{$item->Name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="modal-footer">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- end modal --}}
    @endif
    
    {{-- Include Components --}}
    @include('components.checkout-modal')

    {{-- Global Config --}}
    <script>
        {{-- Initialize --}}
        let csrfToken   = $('meta[name="csrf-token"]').attr('content')
        let baseUrl     = $('#base-url-env').val()
        let homeUrl     = $('#home-url').val()
        let userId      = $('#user-id-account').val()
        let userRoleId  = $('#user-role-id-account').val()
        let companyName = $('#company-name').val()
    </script>

    {{-- Resource --}}
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBxbZxhzKBe5g9ZGLwM6STYnfNPU3ithjE"
    type="text/javascript"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    
    {{-- Notification --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Select 2 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Swipper --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.1/js/swiper.min.js" integrity="sha256-4sETKhh3aSyi6NRiA+qunPaTawqSMDQca/xLWu27Hg4=" crossorigin="anonymous"></script>
    <script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-app.js"></script>
	<script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-auth.js"></script>
	<script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-database.js"></script>


    {{-- init firebase --}}
    <script>
        var apiKey = "{{ env('FIREBASE_API_KEY') }}";
        var authDomain = "{{ env('FIREBASE_AUTH_DOMAIN') }}";
        var databaseURL = "{{ env('FIREBASE_DATABASE_URL') }}";
        var projectId = "{{ env('FIREBASE_PROJECT_ID') }}";
        var storageBucket = "{{ env('FIREBASE_STORAGE_BUCKET') }}";
        var messagingSenderId = "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}";
        var appId = "{{ env('FIREBASE_APP_ID') }}";
    </script>
    <script src="{{ asset('js/firebase_config.js') }}"></script>
    {{-- end --}}

    <script>
        $(document).ready(function () {
            let swiper = new Swiper(".swiper-container-deadline", {
                direction: "vertical",
                slidesPerView: 1,
                autoplay: {
                  delay: 2500,
                  disableOnInteraction: false,
                },
            })
        })     
    </script>

    {{-- Global Function --}}
    <script>
        // Get User By Project Id
        function getUsersByProject(projectId, taskId) {
            $.ajax({
                url: `${baseUrl}/list-users/project/${projectId}?task-id=${taskId}`,
                type: 'GET',
                headers: {'X-CSRF-TOKEN': `${csrfToken}`},
                success: data => {
                    $('#assigned-to').html(data.data)
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

        // Get Users By Task Id
        function getUsersByTask(id) {
            $.ajax({
                url: `${baseUrl}/list-users/todo/${id}`,
                type: 'GET',
                headers: {'X-CSRF-TOKEN': `${csrfToken}`},
                success: data => {
                    // Validate
                    if (data.status) {
                        // Initialize
                        let users = ''

                        $.each(data.data, function(key, val) {
                            users += `<option value="${val.id}">${val.name}</option>`
                        })

                        $('#assigned_to-modal').html(users)
                        $('#assigned_to-modal-detail').html(users)
                    }

                    // console.log('masuk ke app.blade.php')
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
    
    {{-- Logout --}}
    <script type="text/javascript">
        // Trigger Input Type File
        $(document).on('click', '#report-upload-area', function () {
            $('#report-upload-file').click()
        })

        // Get Full Path
        $(document).on('change', '#report-upload-file', function () {
            console.log(this.files)
            // Validate
            // if (this.files[0]) {
            //     $('#span-name-file-project').html(`${this.files[0].name}`)
            // } else {
            //     $('#span-name-file-project').html('<i>*Tidak ada file yang dipilih</i>')
            // }
        })

        // Click Dropzone Area
        $(document).on('click', '.dropzone-area', function () {
            $('.upload-foto-auth')[0].click();
        })

        Dropzone.autoDiscover = false;
        Dropzone.options.upload = {
            acceptedFiles: 'image/*'
        };

        $(document).ready(function () {
            var myDropzone = new Dropzone("div.upload-foto-auth", { 
                url: "/upload-foto",
                maxFiles: 1,
                addRemoveLinks: true,
                success: function (file, response) {
                    let imgName = response;
                    file.previewElement.classList.add("dz-success")
                    
                    $('#checkout-report-file').val(response.data.imagePath);
                    $('.upload-foto').attr('src', `${baseUrl}/storage/${response.data.imagePath}`)
                },
                error: function (file, response) {
                    file.previewElement.classList.add("dz-error");

                    Swal.fire({
                      icon: 'error',
                      text: `${response}`
                    })
                }
            })
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

    {{-- Chatting --}}
    <script>
        $(document).ready(function() {
            $('.participant').select2({
                placeholder: "Choose Member",
            });
        });

        $(document).ready(function () {
            // Initiailze
            let authentication = $('#authentication').val()

            if (authentication == 'true') {
                // Call Function
                // countMessage()
            }
        })

        function countMessage() {
            $.ajax({
                url: `${baseUrl}/count/unread-message`,
                type: 'GET',
                headers: {'X-CSRF-TOKEN': `${csrfToken}`},
                success: data => {
                    $('.count-message').html(data.data)
                },
                error: e => {
                    console.log(e)

                    Swal.fire({
                      title: 'Error',
                      text: '500 Internal Server Error!',
                      icon: 'error'
                    })
                }
            })
        }

        $(document).on('click', '#btn-file-upload-message', function () {
            $('#file-upload-message').click()
        })

        $(document).on('change', '#file-upload-message', function () {
            // Validate
            if (this.files[0]) {
                $('#color-paperclip-message').css('color', '#38c172')
            } else {
                $('#color-paperclip-message').css('color', 'black')
            }
        })

        // Search Message
        // Initialize Global Var
        const highlights = document.getElementsByClassName('highlight')

        $(document).on('click', '#search-all-message-btn', function () {
            // Initialize
            const content        = document.getElementById("getchat");
            const searchInput    = $('#search-message-input').val()
            const originalString = content.innerHTML

            // Call Function
            highlight(content, originalString, searchInput)
            foundWord()

            // Show Display
            $('.search-btn-message-up-down').css('display', '')
        })

        function highlight(element, originalString, search){
          if(search.length > 0) {
            let regex         = new RegExp(search, "gi");
            let newString     = originalString.replace(regex, "<span class='highlight'> " + search + "</span>")
            element.innerHTML = newString
          } else {
            //tidak mencari apapun
            element.innerHTML = originalString
          }
        }

        function foundWord(){ 
            if(highlights.length > 0){
                indicator(1, highlights)
            }
        }

        $(document).on('click', '#up-message-btn', function () {
            // Initialize
            let position = $('#position-message').val()

            // Call Function
            indicator(parseInt(position) - 1, highlights)

            $('#position-message').val(parseInt(position) - 1)
        })

        $(document).on('click', '#down-message-btn', function () {
            // Initialize
            let position = $('#position-message').val()
            
            // Call Function
            indicator(parseInt(position) + 1, highlights)

            $('#position-message').val(parseInt(position) + 1)
        })

        function indicator(currentPosition, highlights = ''){
            if(currentPosition > highlights.length || currentPosition == 0){
                return false;
            }

            // Call Function
            removeCurrentIndicator(currentPosition, highlights)
           
            $('.highlight').get(currentPosition - 1).id = currentPosition
            highlights[currentPosition - 1].classList.add('active-highlight');
            window.location.hash  = '#' + currentPosition //move location
        }

        function removeCurrentIndicator(currentPosition, highlights = ''){
            // Remove Highlight Previous
            if(highlights[parseInt(currentPosition) - 1]){
                // Initialize
                let removeClassById = $('.highlight').attr('id', currentPosition - 1)
                removeClassById.removeClass('active-highlight')
            }
        }
    </script>

    <script>
        function openForm() {
            document.getElementById("myForm").style.display = "block";
        }

        function closeForm() {
            document.getElementById("myForm").style.display = "none";
        }
    </script>

    @stack('script')

    @if (auth()->user())
        <script>

            $('#delete-group').click(function(){
                if (confirm('Are you sure?'))
                document.getElementById('delete-group_form').submit();
            });

            // TODO: untuk nnati load more
            // $(".getchat").scroll(function (event) {
            //     var scroll_position = $(".getchat").scrollTop();
            //     // console.log(scroll_position);
            //     if(scroll_position == 0){
            //         // console.log('learner '+last_id_message);
            //         var html = chatMessage(22);
            //         console.log('yey');
            //         chatMessageLoadMore(22);
            //     }
            // });


            var url_get_list_rooms = "{{ route('group.list') }}";
            var user_id = "{{ auth()->user()->id }}";
            // list chat group api

            function listGroup(conversation) {

                var timeAgo =  '';
                var last_message =  '';
                if (conversation.last_message.data[0]) {
                    
                    var actionTime = moment(conversation.last_message.data[0].created_at + "+07:00", "YYYY-MM-DD HH:mm:ssZ");
    
                    var timeAgo = actionTime.fromNow();
                    last_message = conversation.last_message.data[0].body;
                }
                // <div id="group-`+conversation.id+`" onclick="chatMessage(`+conversation.id+`)">
                //     <li>
                //         <a href="#" class="clearfix">
                //             <div class="friend-name">    
                //                 <strong>`+conversation.conversation.data.title+`</strong>
                //             </div>
                //             <div class="last-message text-muted">`+conversation.last_message.data[0].body+`</div>
                //             <small class="time text-muted">`+timeAgo+`</small>
                //         <small class="chat-alert text-muted"><i class="fa fa-check"></i></small>
                //         </a>
                //     </li> 
                // </div>
                return `
                <div id="group-`+conversation.id+`" onclick="chatMessage(`+conversation.id+`)">
                    <li>
                        <a href="#" class="clearfix">
                            <div class="friend-name">   
                                <strong>`+conversation.conversation.data.title+`</strong>
                            </div>
                            <div class="last-message text-muted">`+last_message+`</div>
                            <small class="time text-muted" style="display: contents;">`+timeAgo+`</small>
                        </a>
                    </li> 
                </div>
                `;
            }

            $.getJSON(url_get_list_rooms, [],
                function (data, textStatus, jqXHR) {
                    // console.log(data);
                    $.each(data.data, function (i, v) { 
                        var html = listGroup(v);
                        $('#list-group').append(html);
                    });
                }
            );

            {{-- Auto Scroll to end of div --}}
            function updateScroll(){
                var element = document.getElementById("getchat");
                element.scrollTop = element.scrollHeight;
            }

            function chatMessage(conversation) {
                var get_chat = "/group/list/message/"+conversation;
                var get_detail_group = "/group/detail/"+conversation;
                $(".getchat").html("");

                // console.log(get_detail_group)
                
                $.getJSON(get_chat, [],
                    function (data, textStatus, jqXHR) {
                        // console.log(data.data.data);
                        
                        // Initialize
                        let totalMessage = (data.data.data).length // <- Old
                        // let totalMessage = (data.data).length
                        totalMessage -= 1

                        $.each(data.data.data.reverse(), function (i, v) {
                            // Initialize
                            let bottomMessage = ''

                            // Check latest Message
                            if (i == totalMessage) {
                                bottomMessage = 'mb-4'
                            }

                            var html = chatMessageHtml(v,user_id,bottomMessage);
                            $('.getchat').append(html);

                        });
                    }
                );

                document.getElementById('btn_send').setAttribute('onclick',"sendMessage("+conversation+")");
                $('#group_body').attr('style', false);

                // get detail group
                $.getJSON(get_detail_group, [],
                    function (data, textStatus, jqXHR) {
                        // console.log(data);
                        $('#grup_name').html(data.data.data.title);

                        // for modal edit
                        $('#group_title').val(data.data.data.title);
                        $('#group_desc').val(data.data.data.description);
                        $('#form_edit_group').attr('action', '/conversation/update/'+conversation);

                        $('#delete-group_form').attr('action', '/conversation/delete/'+conversation);

                        $('#form_member_add').attr('action', '/conversation/add-participants/'+conversation);

                        if (data.data.last_message.data[0]) {
                            var id = data.data.last_message.data[0].id;

                            document.getElementById('last_message_focus-'+id).focus();
                        }

                        
                    }
                );

                // Settings
                setTimeout(function () {
                    // Call Function
                    updateScroll()

                    // Setting Popover
                    $('[data-toggle="popover"]').popover()
                }, 2000)
            }

            function chatMessageLoadMore(conversation) {
                var get_chat = "/group/list/message/"+conversation+"/?page=2";
                var get_detail_group = "/group/detail/"+conversation;
                $(".getchat").html("");

                console.log(get_detail_group)
                
                $.getJSON(get_chat, [],
                    function (data, textStatus, jqXHR) {
                        // console.log(data.data.data);
                        $.each(data.data.data.reverse(), function (i, v) { 
                            var html = chatMessageHtml(v,user_id);
                            $('.getchat').prepend(html);

                        });
                    }
                );
                document.getElementById('btn_send').setAttribute('onclick',"sendMessage("+conversation+")");
                $('#group_body').attr('style', false);

                $.getJSON(get_detail_group, [],
                    function (data, textStatus, jqXHR) {
                        console.log(data);
                        $('#grup_name').html(data.data.data.title);

                        // for modal edit
                        $('#group_title').val(data.data.data.title);
                        $('#group_desc').val(data.data.data.description);
                        $('#form_edit_group').attr('action', '/conversation/update/'+conversation);

                        $('#delete-group_form').attr('action', '/conversation/delete/'+conversation);

                        if (data.data.last_message.data[0]) {
                            var id = data.data.last_message.data[0].id;

                            document.getElementById('last_message_focus-'+id).focus();
                        }

                        
                    }
                );
                
                
            }

            function chatMessagePusher(conversation) {
                var get_chat = "/group/list/message/"+conversation;
                $(".getchat").html("");
                
                $.getJSON(get_chat, [],
                    function (data, textStatus, jqXHR) {
                        $.each(data.data.data.reverse(), function (i, v) { 
                            var html = chatMessageHtml(v,user_id);
                            $('.getchat').append(html);

                        });
                    }
                );
            }

            function sendMessage(conversation_id){
                // Initialize
                let url         = '/group/list/send-message/'+conversation_id;
                let chatText    = $('#chatTextarea').val();
                let file        = $('#file-upload-message')[0].files
                let replayMId   = $('#replay-message-id').val()
               
               // Validate
               if (!chatText && file.length == 0) {
                    Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'Pesan harus diisi!'
                    })

                    return 0
               }

               let fd = new FormData()
               fd.append('message', chatText)
               fd.append('replay_messag_id', replayMId)

               if (file.length == 1) {
                   fd.append('upload_file', file[0])
               }

               // Disabled Button True
               $('#btn_send').attr('disabled', true)
               
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
                        // Disabled Button False
                        $('#btn_send').attr('disabled', false)

                        $('#chatTextarea').val('')
                        $('#file-upload-message').val('')

                        $('#color-paperclip-message').css('color', 'black')

                        chatMessage(data.data.conversation_id);

                        $('#replay-message-show-element').css('display', 'none')
                        $('#replay-message-id').val('')
                   },
                   error: e => {
                        console.log(e)

                        Swal.fire({
                         title: 'Diskusi gagal dikirim',
                         text: '500 Internal Server Error!',
                         icon: 'error'
                        })

                        // Disabled Button False
                        $('#btn_send').attr('disabled', false)
                   }
               })
            }
            

            function chatMessageHtml(chat,user_id, bottomMessage = '') {
                // Initialize
                let actionTime  = moment(chat.created_at + "+07:00", "YYYY-MM-DD HH:mm:ssZ");
                let timeAgo     = actionTime.fromNow()
                let bodyMessage = chat.body
                let textMessage = chat.body
                let messageType = 'text'

                if (chat.type == 'image') {
                    bodyMessage = `<a class="example-image-link" href="${bodyMessage}" data-lightbox="question">
                                    <img class="example-image" src="${bodyMessage}" alt="preview-img">
                                   </a>`

                    messageType = 'image'
                } else if (chat.type == 'document') {
                    bodyMessage = `<a href="${chat.body}" target="_blank" class="btn btn-sm rounded-pill btn-download-file-message">
                                        <i class="fa fa-download"></i> Dokumen
                                    </a>`

                    messageType = 'document'
                } else if (chat.type == 'video') {
                    bodyMessage = `<video width="320" height="240" controls>
                                      <source src="${chat.body}" type="video/mp4">
                                    </video>`

                    messageType = 'video'
                }

               

                if (chat.receiver_id == null) {
                    if (chat.sender.id != user_id) {
                        if (chat.replyMessage == true) {
                            return `
                            <li class="left clearfix ${bottomMessage}">
                                <div class="chat-body clearfix" id="last_message_focus-`+chat.id+`">
                                    <div class="header mb-2">
                                        <div style="border-left: 2px solid #38c172">
                                            <div class="header ml-2" style="border-bottom: none !important">
                                                <strong class="primary-font">${chat.reply_message_sender}</strong>
    
                                                <div class="float-right cursor-area reply-message" id="reply-message-${chat.id}" message-id="${chat.id}" sender-name="${chat.sender.name}" time-message="${timeAgo}" body-message="${textMessage}" message-type="${messageType}" data-container="body" data-toggle="popover" data-placement="top" data-content="Balas Chat">
                                                    <i class="fa fa-angle-down"></i>
                                                </div>
                                            </div>
    
                                            <p class="ml-2">
                                                ${chat.reply_message_body}
                                            </p>
                                        </div>
                                    </div>
                                    <p>
                                        ${bodyMessage}
                                    </p>
                                </div>
                            </li>`;
                        } else {
                            return `
                            <li class="left clearfix ${bottomMessage}">
                                <div class="chat-body clearfix" id="last_message_focus-`+chat.id+`">
                                    <div class="header mb-2">
                                        <strong class="primary-font">`+chat.sender.name+`</strong>
                                        <small class="pull-right text-muted"><i class="fa fa-clock-o"></i> `+timeAgo+`</small>
    
                                        <div class="float-right cursor-area reply-message" id="reply-message-${chat.id}" message-id="${chat.id}" sender-name="${chat.sender.name}" time-message="${timeAgo}" body-message="${textMessage}" message-type="${messageType}" data-container="body" data-toggle="popover" data-placement="top" data-content="Balas Chat">
                                            <i class="fa fa-angle-down"></i>
                                        </div>
                                    </div>
                                    <p>
                                        ${bodyMessage}
                                    </p>
                                </div>
                            </li>`;
                        }
                    } else {
                        if (chat.replyMessage == true) {
                            return `
                                <li class="right clearfix ${bottomMessage}">
                                    <div class="chat-body clearfix" id="last_message_focus-`+chat.id+`">
                                        <div class="header mb-2">
                                            <div style="border-left: 2px solid #38c172">
                                                <div class="header ml-2" style="border-bottom: none !important">
                                                    <strong class="primary-font">${chat.reply_message_sender}</strong>
    
                                                    <div class="float-right cursor-area reply-message" id="reply-message-${chat.id}" message-id="${chat.id}" sender-name="${chat.sender.name}" time-message="${timeAgo}" body-message="${textMessage}" message-type="${messageType}" data-container="body" data-toggle="popover" data-placement="top" data-content="Balas Chat">
                                                        <i class="fa fa-angle-down"></i>
                                                    </div>
                                                </div>
    
                                                <p class="ml-2">
                                                    ${chat.reply_message_body}
                                                </p>
                                            </div>
                                        </div>
                                        <p>
                                            ${bodyMessage}
                                        </p>
                                    </div>
                                </li>
                            `;
                        } else {
                            return `
                                <li class="right clearfix ${bottomMessage}">
                                    <div class="chat-body clearfix" id="last_message_focus-`+chat.id+`">
                                        <div class="header mb-2">
                                            <strong class="primary-font">`+chat.sender.name+`</strong>
                                            <small class="pull-right text-muted"><i class="fa fa-clock-o"></i> `+timeAgo+`</small>
    
                                            <div class="float-right cursor-area reply-message" id="reply-message-${chat.id}" message-id="${chat.id}" sender-name="${chat.sender.name}" time-message="${timeAgo}" body-message="${textMessage}" message-type="${messageType}" data-container="body" data-toggle="popover" data-placement="top" data-content="Balas Chat">
                                                <i class="fa fa-angle-down"></i>
                                            </div>
                                        </div>
                                        <p>
                                            ${bodyMessage}
                                        </p>
                                    </div>
                                </li>
                            `;
                        }
                    }
                } else {
                    if (chat.receiver_id == user_id) {
                        if (chat.replyMessage == true) {
                            return `
                            <li class="left clearfix ${bottomMessage}">
                                <div class="chat-body clearfix" id="last_message_focus-`+chat.id+`">
                                    <div class="header mb-2">
                                        <div style="border-left: 2px solid #38c172">
                                            <div class="header ml-2" style="border-bottom: none !important">
                                                <strong class="primary-font">${chat.reply_message_sender}</strong>
    
                                                <div class="float-right cursor-area reply-message" id="reply-message-${chat.id}" message-id="${chat.id}" sender-name="${chat.sender.name}" time-message="${timeAgo}" body-message="${textMessage}" message-type="${messageType}" data-container="body" data-toggle="popover" data-placement="top" data-content="Balas Chat">
                                                    <i class="fa fa-angle-down"></i>
                                                </div>
                                            </div>
    
                                            <p class="ml-2">
                                                ${chat.reply_message_body}
                                            </p>
                                        </div>
                                    </div>
                                    <p>
                                        ${bodyMessage}
                                    </p>
                                </div>
                            </li>`;
                        } else {
                            return `
                                <li class="left clearfix ${bottomMessage}">
                                    <div class="chat-body clearfix" id="last_message_focus-`+chat.id+`">
                                        <div class="header mb-2">
                                            <strong class="primary-font">`+chat.sender.name+`</strong>
                                            <small class="pull-right text-muted"><i class="fa fa-clock-o"></i> `+timeAgo+`</small>
                    
                                            <div class="float-right cursor-area reply-message" id="reply-message-${chat.id}" message-id="${chat.id}" sender-name="${chat.sender.name}" time-message="${timeAgo}" body-message="${textMessage}" message-type="${messageType}" data-container="body" data-toggle="popover" data-placement="top" data-content="Balas Chat">
                                                <i class="fa fa-angle-down"></i>
                                            </div>
                                        </div>
                                        <p>
                                            <a href="`+chat.link+`" target="_blank" style="color:black">
                                                ${bodyMessage}
                                            </a>
                                        </p>
                                    </div>
                                </li>`;
                        }
                    } 
                }
            }
        </script>

        {{-- Replay Message --}}
        <script>
            $(document).on('click', '.reply-message', function () {
                $('.popover-body').attr('message-id', $(this).attr('message-id'))
                $('.popover-body').attr('sender-name', $(this).attr('sender-name'))
                $('.popover-body').attr('time-message', $(this).attr('time-message'))
                $('.popover-body').attr('message-type', $(this).attr('message-type'))
                $('.popover-body').attr('body-message', $(this).attr('body-message'))
                $('.popover-body').addClass('cursor-area')
            })

            $(document).on('click', '.popover-body', function () {
                // Initialize
                let messageType = $(this).attr('message-type')

                $(`#reply-message-${$(this).attr('message-id')}`).popover('hide')

                // Append Val
                $('#replay-name-message').html($(this).attr('sender-name'))
                $('#replay-time-message').html($(this).attr('time-message'))

                if (messageType == 'text') {
                    $('#replay-body-message').html(`<span class="mt-2">${$(this).attr('body-message')}</span>`)
                } else if (messageType == 'image') {
                    $('#replay-body-message').html(`<img src="${$(this).attr('body-message')}" alt="img-preview" class="img-thumbnail mt-2" width="50" height="50">`)
                } else {
                    $('#replay-body-message').html(`<span class="mt-2">Dokumen</span>`)
                }

                $('#replay-message-show-element').css('display', '')
                $('#replay-message-id').val($(this).attr('message-id'))

                $('#chatTextarea').focus()
            })
        </script>

        <script>
            $(document).on('click', '#replay-message-close', function (e) {
                e.preventDefault()

                $('#replay-message-show-element').css('display', 'none')
                $('#replay-message-id').val('')
            })
        </script>
    @endif
    
    {{-- for next checked today --}}
    @if (auth()->user() && auth()->user()->role_id != '9' && count(auth()->user()->totalCheckToday) > 0)
        @php
            $rand = rand(15,40);
            $selectedTime = \Carbon\Carbon::parse(auth()->user()->checked()->created_at)->format('H:i:s');
            $endTime = strtotime("+$rand minutes", strtotime($selectedTime));
            $next_time = date('H:i:s', $endTime);

            $current_date = \Carbon\Carbon::now();
            $current_time = $current_date->toTimeString();

            $open_time = auth()->user() && auth()->user()->company && auth()->user()->company->OpenTime ? auth()->user()->company->OpenTime : '08:00';
            $closed_time = auth()->user() && auth()->user()->company && auth()->user()->company->ClosedTime ? auth()->user()->company->ClosedTime : '17:00';
        @endphp
        {{-- compare next random time dengan current time, condition config totalcheck and check today --}}
        @if ($current_time > $next_time && auth()->user()->company && auth()->user()->company->TotalCheck > count(auth()->user()->totalCheckToday) && $open_time < $current_time && $closed_time > $current_time)
            <script type="text/javascript">
                $(window).on('load', function() {
                    $('#motivation_modal').modal('show');
                });
            </script>
        @endif
    @endif
    {{-- end for next checked today --}}

    {{-- checked for first time today --}}
    @if (auth()->user() && auth()->user()->role_id != '9' && count(auth()->user()->totalCheckToday) == 0 && auth()->user()->company && auth()->user()->company->TotalCheck)
        @php
            $open_time = auth()->user() && auth()->user()->company && auth()->user()->company->OpenTime ? auth()->user()->company->OpenTime : '08:00';
            $closed_time = auth()->user() && auth()->user()->company && auth()->user()->company->ClosedTime ? auth()->user()->company->ClosedTime : '17:00';
            $current_date = \Carbon\Carbon::now();
            $current_time = $current_date->toTimeString();
        @endphp
        @if ($open_time < $current_time && $closed_time > $current_time)
            <script type="text/javascript">
                $(window).on('load', function() {
                    $('#motivation_modal').modal('show');
                });
            </script>
        @endif
    @endif
     
    {{-- config validasi expired --}}
    @php
        $segment = request()->segment(1);
    @endphp
    @if (auth()->user() && auth()->user()->role_id == 1 && auth()->user()->company && auth()->user()->company->ExpiredDate != null && date('Y-m-d') > auth()->user()->company->ExpiredDate && $segment != 'subscribe')
    <script type="text/javascript">
        $(window).on('load', function() {
            $('#expired_modal').modal('show');
        });
    </script>
    @endif
    <script>
        jQuery(document).ready(function(e) {
            $('#expired_modal').on('hidden.bs.modal', function () {
            window.location.href = "/subscribe/package"
            });
        });

        jQuery(document).ready(function(e) {
            $('#expired_modal_member').on('hidden.bs.modal', function () {
            window.location.href = "/subscribe/expired"
            });
        });
    </script>

    @if (auth()->user() && auth()->user()->company && auth()->user()->company->ExpiredDate != null && date('Y-m-d') > auth()->user()->company->ExpiredDate && $segment != 'subscribe')
    <script type="text/javascript">
        $(window).on('load', function() {
            $('#expired_modal_member').modal('show');
        });
    </script>
    @endif
    {{-- end config validasi --}}
    
   
    {{-- end checked for first time today --}}
    <script>

        var notificationsWrapper   = $('.notif-drop');
        var notificationsToggle    = notificationsWrapper.find('a.nav-link');
        var notificationsCountElem = notificationsToggle.find('i[data-count]');
        var notificationsCount     = parseInt(notificationsCountElem.data('count'));
        var notifications          = notificationsWrapper.find('div.notif-content');

        @if(auth()->check())
            // Enable pusher logging - don't include this in production
            Pusher.logToConsole = true;

            var pusher = new Pusher('c3d9cdd09563a06bbddd', {
            cluster: 'ap1'
            });

            var user_id = "{{ auth()->user()->id }}";
            var channel = pusher.subscribe('user.notif.' + user_id);
            var notifications = [];

            var channelChat = pusher.subscribe('events');

            var channelChatNotif = pusher.subscribe('chat-notif');

            $(document).ready(function(){
                var notificationsWrapper   = $('.notif-drop');
                var notificationsToggle    = notificationsWrapper.find('a.nav-link');
                var notificationsCountElem = notificationsToggle.find('i[data-count]');
                var notificationsCount     = parseInt(notificationsCountElem.data('count'));
                $.get('/get/notification', function(notification) {
                    addNotifications(notification, "#notifications");
                });

                channel.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', function(notification) {
                    addNotifications([notification], '#notifications');
                    
                });

                channelChat.bind('App\\Events\\RealTimeNotif', function(chat) {
                    // console.log(chat.message.conversation_id);
                    if (chat.message.sender.id != user_id) {
                        chatMessagePusher(chat.message.conversation_id);
                        document.getElementById('btn_send').setAttribute('onclick',"sendMessage("+chat.message.conversation_id+")");
                    }
                    
                });

                channelChatNotif.bind('App\\Events\\ChatNotif', function(chat) {
                    $.each(chat.participants, function (i, v) {
                        // console.log(v.body); 
                            if(v.id == user_id){

                                var title = chat.participants.message.conversation.data.title + ' - ' + chat.participants.message.sender.name;
                                
                                toastr.info(chat.participants.message.body,title,{
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": false,
                                    "progressBar": false,
                                    "positionClass": "toast-top-right",
                                    "preventDuplicates": false,
                                    "onclick": null,
                                    "showDuration": "300",
                                    "hideDuration": "300000",
                                    "timeOut": "5000",
                                    "extendedTimeOut": "1000",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut"
                                });
                            }
                    });
                         
                    
                });
            });

            // add new notifications
            function addNotifications(newNotifications, target) {
                notifications = _.concat(notifications, newNotifications);
                // show only last 5 notifications
                notifications.slice(0, 5);
                showNotifications(notifications, target);
            }

            // show notifications
            function showNotifications(notifications, target) {
                if (notifications.length) {
                    var htmlElements = notifications.map(function (notification) {
                        return makeNotification(notification);
                    });
                    $(target + 'Menu').html(htmlElements.join(''));
                    $(target).addClass('has-notifications');

                    notificationsCount += notifications.length;
                    console.log(notificationsCount);
                    notificationsCountElem.attr('data-count', notificationsCount);
                    $('.count').text(notificationsCount);
                } else {
                    $(target + 'Menu').html('<li class="dropdown-header">No notifications</li>');
                    $(target).removeClass('has-notifications');
                }
            }

            // create a notification li element
            function makeNotification(notification) {
                var notificationText = makeNotificationText(notification);

                var actionTime = moment(notification.created_at + "+07:00", "YYYY-MM-DD HH:mm:ssZ");

                var timeAgo = actionTime.fromNow();
                // notificationsCount += 1;
                // notificationsCountElem.attr('data-count', notificationsCount);
                // notificationsWrapper.find('.notif-count').text(notificationsCount);
                var read = "/get/notification/read/"+notification.id;

                return `
                <a href="`+read+`" style="color:black">
                    <li class="notification-box">
                        <div class="row">
                            <div class="col-lg-3 col-sm-3 col-3 text-center">
                                <img src="{{asset('img/300x300.png')}}" class="w-50 rounded-circle">
                            </div>
                            <div class="col-lg-8 col-sm-8 col-8">
                                <strong class="text-info">`+notification.data.sender.name+`</strong>
                                <div>
                                    `+notification.data.message+`
                                </div>
                                <small class="text-warning">`+ timeAgo +`</small>
                            </div>
                        </div>
                    </li>
                </a>`;

                // return `
                //     <li>
                //         <a href="`+ notification.data.link +`">
                //             <div class="row">
                //                 <div class="col-md-3"><div class="notify-img" style="float: left;
                //                     display: inline-block;
                //                     width: 45px;
                //                     height: 45px;
                //                     margin: 0px 0px 8px 0px;">
                //                     <img src="`+notification.data.foto+`" alt=""style="width: 50px; height: 50px;"></div></div>
                //                     <div class="col-md-9">`+notification.data.user.Name+` `+notification.data.message+`<a href="#" class="rIcon" style="float: right;
                //                         color: #999;"><i class="fa fa-dot-circle-o"></i></a>
                //                     <p class="time" style="font-size: 10px;
                //                     font-weight: 600;
                //                     top: -6px;
                //                     margin: 8px 0px 0px 0px;
                //                     padding: 0px 3px;
                //                     border: 1px solid #e2e2e2;
                //                     position: relative;
                //                     background-image: linear-gradient(#fff,#f2f2f2);
                //                     display: inline-block;
                //                     border-radius: 2px;
                //                     color: #B97745;">`+ timeAgo +`</p>
                //                 </div>
                //             </div>

                //         </a>
                // </li>`;
            }

            // get the notification text based on it's type
            function makeNotificationText(notification) {
                var text = '';
                
                    var name = 'testing';
                    text += '<strong>' + name + '</strong> followed you';
                
                return text;
            }
        @endif
    </script>

    {{-- select filter subject --}}
    <script>
        $(document).ready(function() {
            $('#subject_filter').select2({
                dropdownParent: $('#filter'),
                placeholder: "Pilih Subject",
            });
        });
    </script>
</body>
</html>
