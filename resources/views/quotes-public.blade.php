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
                    <table class="table table-bordered table-striped" id="quotes-table">
                        <thead>
                            <th style="width: 10%">ID</th>
                            <th style="width: 90%">Text</th>
                        </thead>

                        <tbody class="hide" v-el:loop>
                            <tr v-for="quote in quotes">
                                <td>@{{ quote.id }}</td>
                                <td>@{{ quote.text }}</td>
                            </tr>
                            <tr v-if="quotes.length === 0">
                                <td colspan="2">No quotes have been created.</td>
                            </tr>
                        </tbody>

                        <tbody v-if="loading">
                            <tr>
                                <td colspan="2" class="text-center"><img src="/assets/img/loader.svg" width="32" height="32" alt="Loading..."></td>
                            </tr>
                        </tbody>
                    </table><!-- .table -->
                </div><!-- .box-body -->
            </div><!-- .box -->
        </div><!-- .col -->
    </div><!-- .row -->
</section>
@endsection

@section('after-js')
    <script>
        var options = {
            api: {
                root: '//{{ makeDomain(config('app.api_domain'), '//') }}/{{ $channel->slug }}'
            },
            csrf_token: '{{ csrf_token() }}',
            pusher: {
                key: '{{ config('services.pusher.key') }}'
            },
            channel: '{{ $channel->slug }}'
        };
    </script>

    <script src="{{ elixir('assets/js/public.js') }}"></script>
@endsection
