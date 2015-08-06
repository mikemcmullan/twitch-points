<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AngryPug's Pug Points Tracker</title>

    <!-- Bootstrap -->
    {!! Html::style('/assets/css/bootstrap.css') !!}
    {!! Html::style('/assets/css/style.css') !!}

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<nav class="navbar navbar-default navbar-fixed-top" id="top-nav">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            {!! link_to_route('home_path', 'AngryPug\'s Pug Points Tracker', [], ['class' => 'navbar-brand']) !!}
        </div>

        <div class="collapse navbar-collapse" id="top-navbar-collapse">
        @if (Auth::check())
            <ul class="nav navbar-nav">
                <li{!! in_array(Route::currentRouteName(), ['check_points_path', 'home_path']) ? ' class="active"' : '' !!}><a href="{!! route('check_points_path') !!}">Check Points</a></li>
                <li{!! Route::currentRouteName() === 'scoreboard_path' ? ' class="active"' : '' !!}><a href="{!! route('scoreboard_path') !!}">Scoreboard</a></li>
                @if (Auth::user()->hasPermission('system-control'))
                <li{!! Route::currentRouteName() === 'system_control_path' ? ' class="active"' : '' !!}><a href="{!! route('system_control_path') !!}">System Control</a></li>
                @endif
                @if (Auth::user()->hasPermission('bot-control'))
                    <li{!! Route::currentRouteName() === 'bot_control_path' ? ' class="active"' : '' !!}><a href="{!! route('bot_control_path') !!}">Bot Control</a></li>
                @endif
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li><a href="#0">Welcome, {{ Auth::user()['name'] }}</a></li>
                <li><a href="{!! route('logout_path') !!}"><i class="fa fa-sign-out"></i> Logout</a></li>
            </ul>
        @else
            <div class="nav navbar-nav">
                <li{!! in_array(Route::currentRouteName(), ['check_points_path', 'home_path']) ? ' class="active"' : '' !!}><a href="{!! route('check_points_path') !!}">Check Points</a></li>
                <li{!! Route::currentRouteName() === 'scoreboard_path' ? ' class="active"' : '' !!}><a href="{!! route('scoreboard_path') !!}">Scoreboard</a></li>
            </div>
        @endif
        </div><!-- .navbar-collapse -->
    </div><!-- .container-fluid -->
</nav><!-- .navbar -->

@yield('content')

<footer id="bottom-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="text-center">
                    AngryPug's Pug Points Tracker was created by {!! link_to('https://twitter.com/mikemcmullan', 'Mike McMullan') !!} a.k.a {!! link_to('http://twitch.tv/mcsmike', 'MCSMike') !!}
                </div>
            </div><!-- .col-*-* -->
        </div><!-- .row -->
    </div><!-- .container -->
</footer><!-- footer -->

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>

@if (Request::is('bot-control'))
    <script>
        var bot_token = '{{ env('BOT_SOCKET_TOKEN') }}',
            bot_ws_server = '{{ env('BOT_WS_SERVER')  }}'
    </script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/vue/0.12.5/vue.min.js"></script>
    <script src="//cdn.socket.io/socket.io-1.3.5.js"></script>
    <script src="/assets/js/bot-control-min.js"></script>
@endif
</body>
</html>