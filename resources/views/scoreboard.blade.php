@extends('layouts.master')

@section('content')

    <div class="container">

        <div class="row">
            <div class="col-md-12">

                <div class="page-header">
                    <h1>Scoreboard</h1>
                </div><!-- .page-header -->

                @include('partials.flash')

                <div class="panel panel-default" id="points-panel">
                    <div class="panel-heading">Overall Scoreboard <em class="pull-right">({{ $chatterCount }} records in total)</em></div>
                    <div class="panel-body">
                        <table class="table table-bordered points-results-table">
                            <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Time Online</th>
                                <th>Points</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($chatters as $chatter)
                                <tr>
                                    <td>{{ $chatter['rank'] or '--'}}</td>
                                    <td>{{ $chatter['handle'] }}</td>
                                    <td>{{ $chatter['minutes'] }}</td>
                                    <td>{{ floor($chatter['points']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="text-center">
                            {!! $paginator !!}
                        </div>
                    </div><!-- .panel-body -->
                </div><!-- .panel -->
            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@stop