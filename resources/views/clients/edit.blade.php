@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('clientsHome') }}">Clients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('showClient', $client->slug) }}">{{ $client->company }}</a></li>
    <li class="breadcrum-item active">Edit</li>

@endsection

@section('content')

<div class="container-fluid main-container">

    <div class="row">

        @include('layouts.errors')

        <form method="post" action="{{ route('updateClient', $client->slug) }}" class="form col-md-4 col-md-offset-4" enctype="multipart/form-data">
            {{ method_field('PATCH') }}
            {{ csrf_field() }}

            <div class="form-group">
                <label>First Name <em>(optional)</em> :
                    <input class="form-control" name="first_name" value="{{ $client->first_name }}">
                </label>
            </div>

            <div class="form-group">
                <label>Last Name <em>(optional)</em> :
                    <input class="form-control" name="last_name" value="{{ $client->last_name }}">
                </label>
            </div>

            <div class="form-group">
                <label>Image <em>(optional)</em> :
                    <input type="file" class="form-control" name="image">
                </label>
            </div>

            <div class="form-group">
                <label>Email :
                    <input class="form-control" name="email" value="{{ $client->email }}">
                </label>
            </div>

            <div class="form-group">
                <label>Contact Number <em>(optional)</em> :
                    <input class="form-control" name="contact_number" value="{{ $client->contact_number }}">
                </label>
            </div>

            <div class="form-group">
                <label>Building Number/Name :
                    <input class="form-control" name="building_number" value="{{ $client->building_number }}">
                </label>
            </div>

            <div class="form-group">
                <label>Street Name :
                    <input class="form-control" name="street_name" value="{{ $client->street_name }}">
                </label>
            </div>

            <div class="form-group">
                <label>City :
                    <input class="form-control" name="city" value="{{ $client->city }}">
                </label>
            </div>

            <div class="form-group">
                <label>Postcode :
                    <input class="form-control" name="postcode" value="{{ $client->postcode }}">
                </label>
            </div>

            <div class="form-group pull-right">
                <input class="btn btn-primary" type="submit">
            </div>
        </form>
    </div>
</div>

@endsection