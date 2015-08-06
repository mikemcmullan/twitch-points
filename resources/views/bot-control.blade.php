@extends('layouts.master')

@section('content')

    <div class="container">

        <div class="row">
            <div class="col-md-12">

                <div class="page-header">
                    <h1>Bot Control</h1>
                </div><!-- .page-header -->

                @include('partials.flash')

                <div class="panel panel-default">
                    <div class="panel-heading">Bot Control</div>
                    <div class="panel-body">
                        <div id="bot-log">
                            <div v-repeat="alerts" class="alert alert-@{{ level }}" role="alert">@{{ msg }}</div>
                            <p>Status: <span class="label label-primary" v-text="bot_status"></span></p>
                            <p>
                                <a v-attr="disabled: ! buttons.start" v-on="click: startBot" class="btn btn-primary btn-sm">Start Bot</a>
                                <a v-attr="disabled: ! buttons.stop" v-on="click: stopBot" class="btn btn-danger btn-sm">Stop Bot</a>
                                {{--<a v-attr="disabled: ! buttons.join" v-on="click: joinChannel" class="btn btn-primary btn-sm">Join Channel</a>--}}
                                {{--<a v-attr="disabled: ! buttons.leave" v-on="click: leaveChannel" class="btn btn-danger btn-sm">Leave Channel</a>--}}
                                <a class="btn btn-link btn-sm pull-right">
                                    <span class="glyphicon glyphicon-refresh bot-log-refresh"></span>
                                </a>
                            </p>
                            <div class="well bot-log">
                                <ul class="bot-log-list">
                                    <li v-repeat="entries" v-html="$value"></li>
                                </ul>
                            </div>
                        </div>
                    </div><!-- .panel-body -->
                </div><!-- .panel -->

            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@stop