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
    <script src="{{ asset('js/jquery.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.4.1/jquery.jscroll.min.js" defer></script>
    <script src="{{ asset('js/dropzone.js') }}"></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

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
   
    <style>
        .btn-primary {
            color: #fff;
            background-color: #dc9934;
            border-color: #db851a;
        }
        .card {
            border-radius: 0px 15px 0px 15px;
            overflow: hidden;
        }
        .card-header {
            color: #faf8ff;
            border-radius: 0px 15px 0px 15px;
            background-color: {{{ $color }}};
        }
        .header-custom {
            background-color: #ffffff;
        }
        a {
            color: {{{ $href_color }}}
        }

        a.header-custom {
            color: black
        }

        a:hover {
            color: black;
        }

        span.header-custom {
            color: black
        }

        .card.border-custom {
            border-color:{{{ $color }}};
        }

        /*h5 {
            color: black
        }*/
        
        .nav-pills .nav-link.active, .nav-pills .show > .nav-link{
            background-color: #17A2B8;
        }
        .dropdown-menu{
            top: 60px;
            right: 0px;
            left: unset;
            width: 460px;
            box-shadow: 0px 5px 7px -1px #c1c1c1;
            padding-bottom: 0px;
            padding: 0px;
        }
        .dropdown-menu:before{
            content: "";
            position: absolute;
            top: -20px;
            right: 12px;
            border:10px solid #c02c4b;
            border-color: transparent transparent #c02c4b transparent;
        }
        .head{
            padding:5px 15px;
            border-radius: 3px 3px 0px 0px;
        }
        .footer{
            padding:5px 15px;
            border-radius: 0px 0px 3px 3px;
        }
        .notification-box{
            padding: 10px 0px;
        }
        .bg-gray{
            background-color: #eee;
        }

        @media (max-width: 640px) {
            .dropdown-menu{
                top: 50px;
                left: -16px;
                width: 290px;
            }
            
            .nav{
                display: block;
            }
            
            .nav .nav-item,.nav .nav-item a{
                padding-left: 0px;
            }
            
            .message{
                font-size: 13px;
            }

            .mb-custom {
                margin-bottom: 20px;
            }

            .width-height-img-upload {
                width: 100px;
                height: 100px;
            }

            .text-description-upload {
                padding-left: 80px;
                font-size: 12px;
            }
        }

        .hide-element {
            display: none;
        }

        /* Home */
        .btn-white {
            background-color: white;
        }

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

        .scroll-y {
            max-height: 350px;
            overflow-y: scroll;
        }

        .scroll-x {
            align-items: stretch;
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .card-min-max-y {
            min-height: 400px;
            max-height: 400px;
        }

        /*.search-icon {
            font-family: FontAwesome;
            font-style: normal;
            font-weight: normal;
            text-decoration: inherit;
        }*/

        .border-company {
            border: 1px solid {{{ $color }}};
        }

        .card-carousel {
            height: 150px;
        }

        .card-hover:hover {
            cursor: pointer;
        }

        .mt-4-5 {
            margin-top: 1.8rem !important;
        }

        a:hover {
            text-decoration: none;
            color: white;
        }

        .card-min-max-y-half {
            min-height: 250px;
            max-height: 250px;
        }

        .width-height-img-upload {
            width: 150px;
            height: 150px;
        }

        .width-height-img-upload-checkout {
            width: 120px;
            height: 120px;
        }

        .span-header-checkin-checkout {
            font-size: 18px;
        }

        .btn-radius {
            border-radius: 10px;
        }

        .border-modal {
            border-radius: 20px;
        }

        .border-modal-header {
            border-radius: 20px 20px 0 0;
        }

        .dropzone-area {
            cursor: pointer;
        }

        .cursor-area {
            cursor: pointer;
        }

        .border-radius-card-header {
            border-radius: 0 0 0 20px !important;
        }

        @media (min-width: 568px) {
            .width-show-project {
                max-width: 80%;
            }
        }
    </style>
    @stack('style')
</head>
<body>
    {{-- Global Value --}}
    <input type="hidden" id="base-url-env" value="{{ URL::to('/') }}">
    @if(auth()->check())
    <input type="hidden" id="home-url" value="{{ route('company:home', \Str::slug(auth()->user()->company->Name)) }}">
    <input type="hidden" id="user-id-account" value="{{ auth()->user()->id }}">
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

                        {{-- Center Side Of Navbar - In Develop --}}
                        {{-- <ul class="navbar-nav">
                            <li>
                                <form action="">
                                    <input type="text" class="form-control rounded-pill icon-input" name="q" placeholder="Cari ...">
                                </form>
                            </li>
                        </ul> --}}

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
                                        @if (auth()->user()->role_id == 1 && auth()->user()->is_active == 'y' && auth()->user()->company_id != null)
                                            <a class="dropdown-item" href="{{ route('company:user.data', \Str::slug(auth()->user()->company->Name)) }}">
                                                @lang('label.data_user')
                                            </a>
                                            <a class="dropdown-item" href="{{ route('company:report.task_attendance', \Str::slug(auth()->user()->company->Name)) }}">
                                                @lang('label.report')
                                            </a>
                                            <a class="dropdown-item" href="{{ route('company:profile.company_edit', [\Str::slug(auth()->user()->company->Name), 'company' => auth()->user()->company_id]) }}">
                                                {{auth()->user()->company->Name}}
                                            </a>
                                        @endif

                                        @if (auth()->user()->role_id == 9)
                                            <a class="dropdown-item" href="{{ route('client.index') }}">
                                                @lang('label.project')
                                            </a>
                                        @endif
    
                                        @if (auth()->user()->role_id == 6)
                                            <a href="{{ route('task.index_member') }}"  class="dropdown-item">@lang('label.task')</a>
                                        @endif
    
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

        {{-- In Develop <div class="w-100">
            <div class=" text-center">
                <div class="alert alert-danger">Tugas Anda <b style="color: #FF0000;">Test Apk versi 0.3.6</b> akan mendekati Deadline!</div>
            </div>
        </div> --}}

        <main class="py-2" style="background-color: #faf8ff;">
            @yield('content')
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
                        <a class="navbar-brand" href="{{ route('auth.logout') }}"><i class="fa fa-sign-out" style="font-size: 30px;"></i></a>
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

    {{-- Include Components --}}
    @include('components.checkout-modal')

    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script> --}}
    
    {{-- Global Config --}}
    <script>
        {{-- Initialize --}}
        let csrfToken = $('meta[name="csrf-token"]').attr('content')
        let baseUrl   = $('#base-url-env').val()
        let homeUrl   = $('#home-url').val()
        let userId    = $('#user-id-account').val()
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

    @stack('script')
    
    {{-- for next checked today --}}
    @if (auth()->user() && count(auth()->user()->totalCheckToday) > 0)
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
    @if (auth()->user() && count(auth()->user()->totalCheckToday) == 0 && auth()->user()->company && auth()->user()->company->TotalCheck)
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
</body>
</html>
