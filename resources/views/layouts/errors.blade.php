@if(count($errors) > 0)
    <div class="panel panel-danger text-center">
    @foreach($errors as $error)
        <li class="panel-heading">{{ $error }}</li>
    @endforeach
    </div>
@endif