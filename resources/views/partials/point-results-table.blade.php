<table class="table table-bordered points-results-table">
    <thead>
    <tr>
        <th>Rank</th>
        <th>Name</th>
        <th>Minutes Online</th>
        <th>Points</th>
    </tr>
    </thead>

    <tbody>
        <tr>
            <td>{{ $chatter['rank'] or '--' }}</td>
            <td>{{ $chatter['handle'] }}</td>
            <td>{{ presentTimeOnline($chatter['minutes']) }} {!! $chatter['mod'] ? '<span class="label label-primary">MOD</span>' : '' !!}</td>
            <td>{{ floor($chatter['points']) }}</td>
        </tr>
    </tbody>
</table>
