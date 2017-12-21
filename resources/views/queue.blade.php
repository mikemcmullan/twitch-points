@extends('layouts.master')

@section('heading', 'Queue')

@section('content')
<section class="content" id="queue">

    @include('partials.flash')

	<div class="row">
        <div class="col-md-6">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Control Panel</h3>
				</div><!-- .box-header -->
				<div class="box-body">
					<p>Status: <span class="label text-uppercase" :class="{
						'label-primary' : isStatusOpen,
						'label-danger'  : isStatusClosed
					}">@{{ status }}</span></p>

					<div class="btn-group btn-group-justified giveaway-controls">
						<div class="btn-group">
							<button type="button" class="btn btn-primary" @click="openQueue" :disabled="disableButtons">Open</button>
						</div>
						<div class="btn-group">
							<button type="button" class="btn btn-danger" @click="closeQueue" :disabled="disableButtons">Close</button>
						</div>
					</div>
				</div><!-- .box-body -->
			</div><!-- .box -->

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Entries (@{{ entries.length }})</h3>
					<button type="button" class="btn btn-primary btn-xs pull-right" :disabled="disableButtons" @click="clearEntries">Clear Entries</button>
                </div><!-- .box-header -->

                <div class="box-body">
					<table class="table">
						<thead>
							<tr>
								<td>Name</td>
								<td>Comment</td>
								<td></td>
							</tr>
						</thead>

						<tbody>
							<tr v-if="entries.length === 0">
								<td colspan="3">No entries.</td>
							</tr>

							<tr v-for="entry in entries">
								<td>@{{ entry.display_name }}</td>
								<td>@{{ entry.comment }}</td>
								<td class="text-right">
									<button type="button" class="btn btn-danger btn-xs" title="Delete Entrant" :disabled="disableButtons" @click="deleteEntrant(entry.id)"><i class="fa fa-trash-o"></i></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!-- .box-body -->
			</div><!-- .box -->
		</div><!-- .col-md-6 -->

		<div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Settings</h3>
                </div><!-- .box-header -->

                <div class="box-body">
					<form @submit.prevent @submit="submit" novalidate autocomplete="off">
						<div class="form-group" v-bind:class="{ 'has-error': errors.has('queue__keyword') }">
							<label for="giveaway-keyword">Keyword</label>
							{!! Form::text('keyword', $channel->getSetting('queue.keyword'), ['id' => 'queue-keyword', 'class' => 'form-control', 'v-model' => 'keyword']) !!}

							<span class="help-block" v-if="errors.has('queue__keyword')" v-text="errors.get('queue__keyword')"></span>
							<span class="help-block">Viewers would use this keyword + their comment to join the queue, <code>@{{ keyword }} Some awesome comment</code></span>
						</div><!-- .form-group -->

						<div class="form-group" v-bind:class="{ 'has-error': errors.has('queue__level') }">
							<label for="queue-level">Level:</label>

							<select class="form-control" v-model="level">
								<option value="everyone">Everyone</option>
								<option value="mod">Mod</option>
								<option value="admin">Admin</option>
								<option value="min_currency">Min Currency</option>
								<option value="min_time">Min Time</option>
							</select>

							<span class="help-block" v-if="errors.has('queue__level')" v-text="errors.get('queue__level')"></span>
							<span class="help-block">What level is required to join the queue.</span>
						</div>

						<div class="form-group" v-if="level === 'min_currency' || level === 'min_time'" v-bind:class="{ 'has-error': errors.has('queue__level-argument') }">
							<label for="queue-level">Level Argument:</label>
							{!! Form::number('cost', $channel->getSetting('queue.cost'), ['class' => 'form-control', 'id' => 'queue-level-argument', 'min' => 0, 'max' => 1000000, 'v-model' => 'levelArgument']) !!}

							<span class="help-block" v-if="errors.has('queue__level')" v-text="errors.get('queue__level-argument')"></span>
							<span class="help-block">The amount of time or currency required to join the queue.</span>
						</div>

						<div class="form-group" v-bind:class="{ 'has-error': errors.has('queue__cost') }">
							<label for="queue-cost">Cost:</label>
							{!! Form::number('cost', $channel->getSetting('queue.cost'), ['class' => 'form-control', 'id' => 'queue-cost', 'min' => 0, 'max' => 1000000, 'v-model' => 'cost']) !!}

							<span class="help-block" v-if="errors.has('queue__cost')" v-text="errors.get('queue__cost')"></span>
							<span class="help-block">How much it will cost to join the queue. Max 1,000,000</span>
						</div>

						<div class="form-group" v-bind:class="{ 'has-error': errors.has('queue__opened-text') }">
							<label for="queue-opened">Queue Opened Text</label>
							{!! Form::textarea('started-text', $channel->getSetting('queue.opened-text'), ['cols' => 30, 'rows' => 3, 'class' => 'form-control', 'id' => 'queue-opened', 'v-model' => 'openedText']) !!}

							<span class="help-block" v-if="errors.has('queue__started-text')" v-text="errors.get('queue__started-text')"></span>
							<span class="help-block">The bot will display this message when the queue opens. Max characters 250.</span>
						</div>

						<div class="form-group" v-bind:class="{ 'has-error': errors.has('queue__closed-text') }">
							<label for="queue-closed">Queue Closed Text</label>
							{!! Form::textarea('closed-text', $channel->getSetting('queue.closed-text'), ['cols' => 30, 'rows' => 3, 'class' => 'form-control', 'id' => 'queue-closed', 'v-model' => 'closedText']) !!}

							<span class="help-block" v-if="errors.has('queue__closed-text')" v-text="errors.get('queue__closed-text')"></span>
							<span class="help-block">The bot will display this message when the queue is closed. Max characters 250.</span>
						</div>

						<button type="submit" class="btn btn-primary" v-bind:disabled="saving">Save</button>
						<span v-show="alert.visible" class="animated btn" v-bind:class="alert.class" role="alert" transition="fade" stagger="2000">@{{ alert.text }}</span>
					</form>
				</div><!-- .box-body -->
			</div><!-- .box -->
		</div><!-- .col-md-6 -->
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
                channel: '{{ $channel->slug }}',
				echo: {
	                url: '{{ config('services.echo.url') }}'
	            },
				queue: {
					level: '{{ $channel->getSetting('queue.level') }}',
					status: '{{ $status }}'
				}
            };
        </script>

		<script src="{{ config('services.echo.url') }}/socket.io/socket.io.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.7/jquery.slimscroll.min.js"></script>
        <script src="{{ elixir('assets/js/admin.js') }}"></script>
    @endsection
@endif
