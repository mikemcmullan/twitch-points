@extends('layouts.master', ['channel' => $channel])

@section('content')

    <div class="container">

        <div class="row">
            <div class="col-md-12">

                <div class="page-header">
                    <h1>System Control</h1>
                </div><!-- .page-header -->

                @include('partials.flash')

                <div class="panel panel-default">
                    <div class="panel-heading">System Status</div>
                    <div class="panel-body text-center">
                        {!! Form::open(['route' => ['start_system_path', $channel->slug], 'method' => 'patch']) !!}
                            @if ($systemStarted)
                            <h3><span class="label label-success">Running</span></h3>
                            <p><button class="btn btn-danger btn-lg">Stop</button></p>
                            @else
                            <h3><span class="label label-danger">Not Running</span></h3>
                            <p><button class="btn btn-primary btn-lg">Start</button></p>
                            @endif

                            <p>
                                <label for="sync-status">Sync system status with stream status:</label>
                                {{ Form::checkbox('sync-status', 'yes', $syncStatus, ['id' => 'sync-status']) }}
                            </p>

                            <p>Note: this page can be closed after the system has been started.</p>
                        {!! Form::close() !!}
                    </div><!-- .panel-body -->
                </div><!-- .panel -->

            </div><!-- .col-*-* -->
        </div><!-- .row -->

    </div><!-- .container -->

@stop
