@extends('layouts.master')

@section('heading', 'Commands')

@section('content')
<section class="content" id="commands">

    @include('partials.flash')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Custom Commands</h3>
                </div><!-- .box-header -->
                <div class="box-body">

                    <edit-command-modal></edit-command-modal>
                    <delete-command-modal></delete-command-modal>

                    <table class="table table-bordered" id="commands-table">

                        <thead>
                            <th stlye="width: 15%">Command</th>
                            <th style="width: 15%">Level</th>
                            <th style="width: 60%">Response</th>
                            <th style="width: 10%" class="text-center">Actions</th>
                        </thead>

                        <tbody class="hide">
                            <tr v-for="command in customCommands">
                                <td>@{{ command.command }}</td>
                                <td>@{{ command.level.capitalize() }}</td>
                                <td>@{{ command.response.substring(0, 100) }}<span v-if="command.response.length > 100">...</span></td>
                                <td class="text-center">
                                    <button type="button" @click="editCommandModal($index)" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-pencil-square-o"></i></button>
                                    <button type="button" @click="deleteCommandModal($index)" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash-o"></i></button>
                                </td>
                            </tr>
                            <tr v-if="customCommands.length === 0">
                                <td colspan="4">No commands have been created.</td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->

                    <br>

                    <button class="btn btn-primary" @click="newCommandModal()">Create Command</button>
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
