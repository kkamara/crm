@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('usersHome') }}">Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('showUser', $user->id) }}">{{ $user->username }}</a></li>
    <li class="breadcrum-item active">Edit</li>

@endsection

@section('content')

<div class="container-fluid main-container">

    <div class="row">

        @include('layouts.errors')

        <div class="col-md-6 col-md-offset-3">
            <form method="post" action="{{ route('updateUser', $user->id) }}" class="form col-md-8 col-md-offset-2">
                {{ method_field('PATCH') }}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Which client(s) will this user belong to?
                        <select multiple class="form-control" name="clients[]">
                            {!! $user->getEditClientOptions($user) !!}
                        </select>
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