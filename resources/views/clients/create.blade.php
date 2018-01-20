@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('clientsHome') }}">Clients</a></li>
    <li class="breadcrumb-item active">Create</li>

@endsection

@section('content')


<div class="container-fluid main-container">

    <div class="row">
        
        @include('layouts.errors')

        <form method="post" action="{{ route('createClient') }}" class="form col-md-4 col-md-offset-4" enctype="multipart/form-data">
            {{ csrf_field() }}

            <div class="form-group">
                <label>Company Name :
                    <input class="form-control" name="company" value="{{ $input['company'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>First Name (optional) :
                    <input class="form-control" name="first_name" value="{{ $input['first_name'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Last Name (optional) :
                    <input class="form-control" name="last_name" value="{{ $input['last_name'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Image (optional) :
                    <input type="file" class="form-control" name="image" value="{{ $input['image'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Assign Client Users :
                    <select multiple class="form-control" name="client_users[]">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->first_name.' '.$user->last_name.' - '.$user->username }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="form-group">
                <label>Email :
                    <input type="email" class="form-control" name="email" value="{{ $input['email'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Contact Number (optional)&nbsp; :
                    <input class="form-control" name="contact_number" value="{{ $input['contact_number'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Building Number/Name :
                    <input class="form-control" name="building_number" value="{{ $input['building_number'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Street Name :
                    <input class="form-control" name="street_name" value="{{ $input['street_name'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Postcode :
                    <input class="form-control" name="postcode" value="{{ $input['postcode'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>City :
                    <input class="form-control" name="city" value="{{ $input['city'] or '' }}">
                </label>
            </div>

            <div class="form-group pull-right">
                <input class="btn btn-primary" type="submit">
            </div>

        </form>
    </div>
</div>


@endsection