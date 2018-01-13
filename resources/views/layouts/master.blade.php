<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }} | {{ config('app.name') }}</title>

    {{--  sheets  --}}
    @include('layouts.stylesheets') 
</head>
<body>
    
        <div id="wrapper">
            <div class="overlay"></div>
    
            @include('layouts.navbar')

            <!-- Page Content -->
            <div id="page-content-wrapper">
                <button type="button" class="hamburger is-closed animated fadeInLeft" data-toggle="offcanvas">
                    <span class="hamb-top"></span>
                    <span class="hamb-middle"></span>
                    <span class="hamb-bottom"></span>
                </button>
                <div class="container">
                    <div class="row">
                        <div style="color:white;" class="col-lg-12">
                            <h3 class="page-header text-center lead">
                                {{ $title }}
                            </h3>
                            <div style="color:white;font-size:14px;" class="pull-right">
                                Logged in as {{ Auth::user()->name }}
                                {{ Auth::user()->last_login }}
                            </div>
                            <div class="pull-left {{ \request()->route()->getName() == 'Dashboard' ? 'hide' : '' }}">
                                <ol class="breadcrumb">
                                    <li class="breadcrum-item"><a href="/">Home</a></li>
                                    @yield('breadcrumbs')>
                                </ol>
                            </div>
                            
                            <br>

                            @include('layouts.flash')
                            <br>
                            @yield('content')

                            @include('layouts.footer')
                        </div>
                    </div>

                </div>
            </div>
            <!-- /#page-content-wrapper -->

        </div>
        <!-- /#wrapper -->

    @section('scripts')
        <script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
    @show
</body>
</html>