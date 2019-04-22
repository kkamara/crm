@extends('layouts.master')

@section('breadcrumbs')
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')

<div class="container-fluid main-container">

    <div class="row">

        @include('layouts.errors')

        <form method="post" action="{{ route('updateSettings') }}" class="form col-md-4 col-md-offset-4">
            {{ method_field('PUT') }}
            {{ csrf_field() }}

            <div class="form-group">
                <label>First Name :
                    <input class="form-control" name="first_name" value="{{ $user->first_name }}">
                </label>
            </div>

            <div class="form-group">
                <label>Last Name :
                    <input class="form-control" name="last_name" value="{{ $user->last_name }}">
                </label>
            </div>

            <div class="form-group">
                <label>Email :
                    <input class="form-control" name="email" value="{{ $user->email }}">
                </label>
            </div>

            <div class="form-group">
                <label>Contact Number <em>(optional)</em> :
                    <input class="form-control" name="contact_number" value="{{ $user->contact_number }}">
                </label>
            </div>

            <div class="form-group">
                <label>Building Number/Name <em>(optional)</em> :
                    <input class="form-control" name="building_number" value="{{ $user->building_number }}">
                </label>
            </div>

            <div class="form-group">
                <label>Street Name <em>(optional)</em> :
                    <input class="form-control" name="street_name" value="{{ $user->street_name }}">
                </label>
            </div>

            <div class="form-group">
                <label>City <em>(optional)</em> :
                    <input class="form-control" name="city" value="{{ $user->city }}">
                </label>
            </div>

            <div class="form-group">
                <label>Postcode <em>(optional)</em> :
                    <input class="form-control" name="postcode" value="{{ $user->postcode }}">
                </label>
            </div>

            <div class="form-group pull-right">
                <input class="btn btn-primary" type="submit">
            </div>
        </form>
    </div>
</div>

@endsection