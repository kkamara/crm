
@if(session('flashSuccess'))
    <br>
    <div class="panel panel-success text-center">
        <div class="panel-heading">{{ session('flashSuccess') }}</div>
    </div>
@endif

@if(session('flashError'))
    <br>
    <div class="panel panel-danger text-center">
        <div class="panel-heading">{{ session('flashError') }}</div>
    </div>
@endif