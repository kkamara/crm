@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('usersHome') }}">Users</a></li>
    <li class="breadcrumb-item active">Create</li>

@endsection

@section('content')

<div class="container-fluid main-container">

    <div class="row">

        @include('layouts.errors')

        <div class="col-md-6 col-md-offset-3">

            <form method="post" action="{{ route('createUser') }}" class="form col-md-8 col-md-offset-2">
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Which client(s) will this user belong to?
                        <select multiple class="form-control" name="clients[]">
                            @foreach(\App\Client::getAccessibleClients(auth()->user())->orderBy('company', 'ASC')->get() as $client)
                                <option value="{{ $client->id }}">{{ $client->company }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    <label>Which role will this user have?
                        <select class="form-control" name="role">
                            <option value="0">--- select role ---</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    <label>First Name :
                        <input class="form-control" name="first_name" value="{{ $input['first_name'] or '' }}">
                    </label>
                </div>

                <div class="form-group">
                    <label>Last Name :
                        <input class="form-control" name="last_name" value="{{ $input['last_name'] or '' }}"/>
                    </label>
                </div>

                <div class="form-group">
                    <label>Email :
                        <input class="form-control" name="email" value="{{ $input['email'] or '' }}">
                    </label>
                </div>

                <div class="form-group">
                    <label>Password :
                        <input class="form-control" type='password' name="password" value="{{ $input['password'] or '' }}"/>
                    </label>
                </div>

                <div class="form-group">
                    <label>Confirm Password :
                        <input class="form-control" type='password' name="password_confirmation" value="{{ $input['password_confirmation'] or '' }}"/>
                    </label>
                </div>

                <div class="form-group pull-right">
                    <input class="btn btn-primary" type="submit">
                </div>

            </form>
        </div>
    </div>
</div>


@endsection