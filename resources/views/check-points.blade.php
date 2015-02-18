@extends('layouts.master')

@section('content')

    <div class="container">

        <div class="row">
            <div class="col-md-12">

                <div class="page-header">
                    <h1>Check Points</h1>
                </div><!-- .page-header -->

                @include('partials.flash')

                <div class="panel panel-default" id="points-panel">
                    <div class="panel-heading">How many points have you earned?</div>
                    <div class="panel-body">
                        {!! Form::open(['method' => 'get', 'class' => 'points-results-form']); !!}

                        <div class="form-group">
                            {!! Form::label('handle', 'Chat Handle:'); !!}
                            {!! Form::text('handle', $handle, ['class' => 'form-control']) !!}
                        </div>

                        {!! Form::submit('Check Points', ['class' => 'btn btn-primary', 'id' => 'check-points']) !!}
                        {!! Form::close(); !!}

                        {{--{!! var_dump($user) !!}--}}

                        @if (isset($user) && empty($user))
                            <div class="alert alert-warning">
                                Handle not found.
                            </div>
                        @endif

                        @if (isset($user) && $user)
                            @include("partials/point-results-table")
                        @endif
                    </div><!-- .panel-body -->
                </div><!-- .panel -->
            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@stop