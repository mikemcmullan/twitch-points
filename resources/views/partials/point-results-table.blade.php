<table class="table table-bordered points-results-table">
    <thead>
    <tr>
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
        <td>{{ $user['handle'] }}</td>
        <td>{{ $user['total_minutes_online'] }}</td>
        <td>{{ round($user['points']) }}</td>
    </tr>
    @endif
    </tbody>
</table>