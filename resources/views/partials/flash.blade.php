@if (session('message'))
    <div class="alert alert-warning">
        {{ session('message') }}
    </div>
@endif
