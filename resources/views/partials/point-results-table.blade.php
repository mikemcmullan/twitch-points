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
        <td>{{ $user['rank'] or 'N/A' }}</td>
        <td>{{ $user['handle'] }}</td>
        <td>{{ $user['total_minutes_online'] }}</td>
        <td>{{ floor($user['points']) }}</td>
    </tr>
    @endif
    </tbody>
</table>