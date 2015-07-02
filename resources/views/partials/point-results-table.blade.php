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

    @if (isset($javascript) && $javascript)
    <tr>
        <td>@{{handle}}</td>
        <td>@{{minutesOnline}}</td>
        <td>@{{points}}</td>
    </tr>
    @else
    <tr>
        <td>{{ $chatter['rank'] or '--' }}</td>
        <td>{{ $chatter['handle'] }}</td>
        <td>{{ presentTimeOnline($chatter['minutes']) }}</td>
        <td>{{ floor($chatter['points']) }}</td>
    </tr>
    @endif
    </tbody>
</table>