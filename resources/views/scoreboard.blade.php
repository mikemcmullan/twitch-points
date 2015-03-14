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
                    <div class="panel-heading">Overall Scoreboard</div>
                    <div class="panel-body">
                        <table class="table table-bordered points-results-table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time Online</th>
                                <th>Points</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($chatUsers as $user)
                                <tr>
                                    <td>{{ $user['handle'] }}</td>
                                    <td>{{ $user['total_minutes_online'] }}</td>
                                    <td>{{ round($user['points']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="text-center">
                            {!! $chatUsers->render() !!}
                        </div>
                    </div><!-- .panel-body -->
                </div><!-- .panel -->
            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@stop