@extends('layouts.master')

@section('heading', 'Commands')

@section('content')
<section class="content" id="commands">

    @include('partials.flash')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Custom Commands</h3>
                </div><!-- .box-header -->

                <div class="box-body">

                    <edit-command-modal></edit-command-modal>
                    <delete-command-modal></delete-command-modal>

                    <table class="table table-bordered" id="custom-commands-table">
                        <thead>
                            <th stlye="width: 15%">Command</th>
                            <th style="width: 15%">Level</th>
                            <th style="width: 60%">Response</th>
                            <th style="width: 10%" class="text-center">Actions</th>
                        </thead>

                        <tbody class="hide" v-el:loop2>
                            <tr v-for="command in commands | filterBy 'custom' in 'type'" :class="{ 'command-disabled': command.disabled }">
                                <td><span class="label label-danger" v-if="command.disabled">Disabled</span> @{{ command.command }}</td>
                                <td>@{{ command.level.capitalize() }}</td>
                                <td>@{{ command.response.substring(0, 100) }}<span v-if="command.response.length > 100">...</span></td>
                                <td class="text-center">
                                    <button type="button" @click="editCommandModal(command.id)" class="btn btn-primary btn-xs" title="Edit Command"><i class="fa fa-pencil-square-o"></i></button>
                                    <button type="button" @click="disableCommand(command.id)" class="btn btn-warning btn-xs" :disabled="disableDisableBtn" title="Disable Command"><i class="fa fa-ban"></i></button>
                                    <button type="button" @click="deleteCommandModal(command.id)" class="btn btn-danger btn-xs" title="Delete Command"><i class="fa fa-trash-o"></i></button>
                                </td>
                            </tr>
                            <tr v-if="customCommands.length === 0">
                                <td colspan="4">No custom commands have been created.</td>
                            </tr>
                        </tbody>

                        <tbody v-if="loading">
                            <tr>
                                <td colspan="4" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->

                    <br>

                    <button class="btn btn-primary" @click="newCustomCommandModal()">Create Command</button>
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">System Commands</h3>
                </div><!-- .box-header -->

                <div class="box-body">
                    <table class="table table-bordered" id="system-commands-table">
                        <thead>
                            <th stlye="width: 25%">Command</th>
                            <th style="width: 15%">Level</th>
                            <th style="width: 50%">Description</th>
                            <th style="width: 10%"> Actions</th>
                        </thead>

                        <tbody class="hide" v-el:loop>
                            <tr v-for="command in commands | filterBy 'system' in 'type'">
                                <td><span class="label label-danger" v-if="command.disabled">Disabled</span> @{{ command.usage }}</td>
                                <td>@{{ command.level.capitalize() }}</td>
                                <td>@{{{ command.description }}}</td>
                                <td class="text-center">
                                    <button type="button" @click="disableCommand(command.id)" class="btn btn-warning btn-xs"  :disabled="disableDisableBtn" title="Disable Commnad"><i class="fa fa-ban"></i></button>
                                </td>
                            </tr>

                            <tr v-if="systemCommands.length === 0">
                                <td colspan="3">No system commands available.</td>
                            </tr>
                        </tbody>

                        <tbody v-if="loading">
                            <tr>
                                <td colspan="4" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->

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
        <script src="/assets/js/bundle.js"></script>
    @endsection
@endif
