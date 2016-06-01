@extends('layouts.master')

@section('heading', 'Quotes')

@section('content')
<section class="content" id="quotes">

    @include('partials.flash')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Quotes</h3>
                </div><!-- .box-header -->

                <div class="box-body">

                    <delete-quote-modal></delete-quote-modal>
                    <edit-quote-modal></edit-quote-modal>

                    <table class="table table-bordered table-striped" id="timers-table">
                        <thead>
                            <th style="width: 10%">ID</th>
                            <th stlye="width: 75%">Name</th>
                            <th style="width: 15%" class="text-center">Actions</th>
                        </thead>

                        <tbody class="hide" v-el:loop>
                            <tr v-for="quote in quotes">
                                <td>@{{ quote.id }}</td>
                                <td>@{{ quote.text }}</td>
                                <td class="text-center">
                                    <button type="button" @click="editModal(quote.id)" class="btn btn-primary btn-xs" title="Edit Quote"><i class="fa fa-pencil-square-o"></i></button>
                                    <button type="button" @click="deleteModal(quote.id)" class="btn btn-danger btn-xs" title="Delete Quote"><i class="fa fa-trash-o"></i></button>
                                </td>
                            </tr>

                            <tr v-if="quotes.length === 0">
                                <td colspan="3">No timers have been created.</td>
                            </tr>
                        </tbody>

                        <tbody v-if="loading">
                            <tr>
                                <td colspan="3" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->

                    <br>

                    <button class="btn btn-primary" @click="newModal()">Create Quote</button>
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
                    root: '//{{ makeDomain(config('app.api_domain'), '//') }}/{{ $channel->slug }}'
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
