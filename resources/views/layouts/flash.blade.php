
@if(session('message'))
    <div class="panel panel-success text-center">
        <div class="panel-heading">{{ session('message') }}</div>
    </div>
@endif