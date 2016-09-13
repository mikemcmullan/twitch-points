@extends('layouts.master')

@section('heading', 'Chat Logs')

@section('content')
<section class="content" id="test">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Chat Logs (page @{{ pagination.current_page }})</h3>
                    <em class="pull-right">(as of @{{ formatedLoadTime }})</em>
                </div><!-- .box-header -->

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable">
                                <thead>
                                    <th>Created At</th>
                                    <th>Display Name</th>
                                    <th>Message</th>
                                </thead>
                                <tbody class="hide" v-el:loop>
                                    <tr v-for="message in logs">
                                        <td style="vertical-align: middle">@{{ message.created_at }} UTC</td>
                                        <td style="vertical-align: middle">@{{ message.display_name }}</td>
                                        <td style="vertical-align: middle">@{{{ message.message }}}</td>
                                    </tr>

                                    <tr v-if="!logs">
                                        <td colspan="3">No records found.</td>
                                    </tr>
                                </tbody>

                                <tbody v-if="loading">
                                    <tr>
                                        <td colspan="3" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                                    </tr>
                                </tbody>
                            </table>

                            <a class="btn btn-default" :disabled="noMoreResults || loading" @click="loadMore()">Load More</a>
                        </div><!-- .col-md-12 -->
                    </div><!-- .row -->
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col-md-12 -->
    </div><!-- .row -->
</section><!-- .content -->
@endsection

@can('admin-channel', $channel)
    @section('after-js')
        <script>
            var options = {
                api: {
                    token: '{{ $apiToken }}',
                    root: '//{{ config('app.api_domain') }}/{{ $channel->slug }}'
                },
                channel: '{{ $channel->slug }}'
            };
        </script>

        <script src="/assets/js/admin.js"></script>
    @endsection
@endcan
