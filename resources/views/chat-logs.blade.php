@extends('layouts.master')

@section('heading', 'Chat Logs')

@section('content')
<section class="content" id="chat-logs">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Chat Logs</h3>
                    <em class="pull-right">(as of @{{ formatedLoadTime }})</em>
                </div><!-- .box-header -->

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-9">
                            <input type="search" class="form-control" v-model="searchKeyword" @keyup.enter="search()" placeholder="search messages">
                            <br>
                        </div>

                        <div class="col-md-3">
                            <div class="btn-group" style="width: 100%;">
                                <button class="btn btn-primary" style="width: 50%;" :disabled="loading" @click="search()">Search</button>
                                <button class="btn btn-default" style="width: 50%;" :disabled="loading" @click="reset()">Reset</button>
                            </div>
                            <br>
                        </div>

                        <div class="col-md-12">
                            <div v-if="isConversation">
                                <p><strong>Currently showing conversation around: @{{ formatDisplayDate(highlight.created_at) }}</strong></p>
                            </div>

                            <div v-if="isSearch">
                                <p><strong>Currently showing search results for: @{{ searchKeyword }}</strong></p>
                            </div>

                            <table class="table table-bordered table-hover dataTable">
                                <thead v-if="!isSearch">
                                    <th style="width: 15%;">Created At</th>
                                    <th style="width: 10%;">Display Name</th>
                                    <th style="width: 75%;">Message</th>
                                </thead>

                                <thead v-if="isSearch">
                                    <th style="width: 15%;">Created At</th>
                                    <th style="width: 10%;">Display Name</th>
                                    <th style="width: 65%;">Message</th>
                                    <th style="width: 10%" v-if="isSearch">--</th>
                                </thead>

                                <tbody class="hide">
                                    <tr v-if="showLoadNewer">
                                        <td colspan="3" class="text-center" @click="loadNewer()" style="cursor: pointer;"><a>Load newer</a></td>
                                    </tr>

                                    <tr v-if="loadingTop">
                                        <td colspan="3" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                                    </tr>

                                    <tr v-for="message in logs" :class="{ 'highlight-message': message.highlight }">
                                        <td style="vertical-align: middle">@{{ formatDisplayDate(message.created_at) }}</td>
                                        <td style="vertical-align: middle">@{{ message.display_name || message.username }}</td>
                                        <td style="vertical-align: middle;">@{{{ message.message }}}</td>
                                        <td style="vertical-align: middle" v-if="isSearch"><a style="cursor: pointer;" @click="conversation(message)">Show Conversation</a></td>
                                    </tr>

                                    <tr v-if="showLoadOlder">
                                        <td colspan="3" class="text-center" @click="loadOlder()" style="cursor: pointer;"><a>Load older</a></td>
                                    </tr>

                                    <tr v-if="logs.length === 0 && loadingBottom === false">
                                        <td colspan="@{{ isSearch ? 4 : 3 }}" >No records found.</td>
                                    </tr>
                                </tbody>

                                <tbody v-if="loadingBottom">
                                    <tr>
                                        <td colspan="@{{ isSearch ? 4 : 3 }}" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                                    </tr>
                                </tbody>
                            </table>
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
