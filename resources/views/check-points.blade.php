@extends('layouts.master')

@section('content')

    <div class="container">

        <div class="row">
            <div class="col-md-12">

                <div class="page-header">
                    <h1>Pug Points Checker</h1>
                </div><!-- .page-header -->

                @include('partials.flash')

                <div class="panel panel-default" id="points-panel">
                    <div class="panel-heading">How many pug points have you earned?</div>
                    <div class="panel-body">
                        {!! Form::open(['method' => 'get', 'class' => 'points-results-form']); !!}

                        <div class="form-group">
                            {!! Form::label('handle', 'Chat Handle:'); !!}
                            {!! Form::text('handle', $handle, ['class' => 'form-control']) !!}
                            <p class="help-block">Enter your twitch username into the box above and click 'Check Points'.</p>
                        </div>

                        {!! Form::submit('Check Points', ['class' => 'btn btn-primary', 'id' => 'check-points']) !!}
                        {!! Form::close(); !!}

                        @if ( ! $chatter && $handle !== '')
                            <div class="alert alert-warning">
                                Handle not found.
                            </div>
                        @endif

                        @if ($chatter)
                            @include("partials/point-results-table")
                        @endif
                    </div><!-- .panel-body -->
                </div><!-- .panel -->

                <div class="panel panel-default" id="points-panel">
                    <div class="panel-heading">Top 25 Leader board</div>
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
                            @foreach ($chatters as $chatter)
                                <tr>
                                    <td>{{ $chatter['rank'] or '--' }}</td>
                                    <td>{{ $chatter['handle'] }}</td>
                                    <td>{{ presentTimeOnline($chatter['minutes']) }}</td>
                                    <td>{{ floor($chatter['points']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {!! link_to_route('scoreboard_path', 'Entire Scoreboard', [], ['class' => 'btn btn-primary']) !!}
                    </div><!-- .panel-body -->
                </div><!-- .panel -->
            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@stop