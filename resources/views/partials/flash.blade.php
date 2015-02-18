@if (Session::has('message'))
    <div class="alert alert-warning">
        {{ Session::get('message') }}
    </div>
@endif