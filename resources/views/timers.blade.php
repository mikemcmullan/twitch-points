@extends('layouts.master')

@section('heading', 'Timers')

@section('content')
<section class="content" id="timers">

    @include('partials.flash')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Timers</h3>
                </div><!-- .box-header -->

                <div class="box-body">

                    <delete-timer-modal></delete-timer-modal>
                    <edit-timer-modal></edit-timer-modal>

                    <table class="table table-bordered table-striped" id="timers-table">
                        <thead>
                            <th style="width: 7%">Status</th>
                            <th stlye="width: 63%">Name</th>
                            <th style="width: 15%">Interval</th>
                            <th style="width: 15%" class="text-center">Actions</th>
                        </thead>

                        <tbody class="hide" v-el:loop>
                            <tr v-for="timer in timers">
                                <td>
                                    <button @click="disable(timer.id)" :disabled="disableDisableBtn" class="btn label label-danger" v-if="timer.disabled">Disabled</button>
                                    <button @click="disable(timer.id)" :disabled="disableDisableBtn" class="btn label label-primary" v-if="!timer.disabled">Enabled</button>
                                </td>
                                <td>@{{ timer.name }}</td>
                                <td>@{{ timer.interval }} mins, @{{ timer.lines }} lines</td>
                                <td class="text-center">
                                    <button type="button" @click="editModal(timer.id)" class="btn btn-primary btn-xs" title="Edit Timer"><i class="fa fa-pencil-square-o"></i></button>
                                    <button type="button" @click="deleteModal(timer.id)" class="btn btn-danger btn-xs" title="Delete Timer"><i class="fa fa-trash-o"></i></button>
                                </td>
                            </tr>

                            <tr v-if="timers.length === 0">
                                <td colspan="4">No timers have been created.</td>
                            </tr>
                        </tbody>

                        <tbody v-if="loading">
                            <tr>
                                <td colspan="4" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->

                    <br>

                    <button class="btn btn-primary" @click="newModal()">Create Timer</button>
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->

</section>
@endsection

@if ($user)
    @section('after-js')
        <script>
            var options = {
                api: {
                    token: '{{ $apiToken }}',
                    root: '//{{ config('app.api_domain') }}/{{ $channel->slug }}'
                },
                csrf_token: '{{ csrf_token() }}',
                pusher: {
                    key: '{{ config('services.pusher.key') }}'
                },
                channel: '{{ $channel->slug }}'
            };
        </script>

        <script src="//js.pusher.com/3.0/pusher.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.7/jquery.slimscroll.min.js"></script>
        <script src="/assets/js/admin.js"></script>
    @endsection
@endif
