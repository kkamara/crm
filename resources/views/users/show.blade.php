@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('usersHome') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>

@endsection

@section('content')

<div class="container-fluid main-container">
    <div class="pull-right">
        @can('edit user')
        <button data-toggle="modal" data-target="#editModal" class="btn btn-info">Edit</button>
        @endcan
        @can('delete user')
        <button data-toggle="modal" data-target="#deleteModal" class="btn btn-danger">Delete</button>
        @endcan
    </div>
    <div class="card pull-left">
        <ul class="list-group list-group-flush ">
            <li class="list-group-item" style="border: none;">
                <strong>Name</strong> :<br>
                {{ $user->first_name.' '.$user->last_name }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Contact Number</strong> :<br> {{ $user->contact_number or '' }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Email</strong> :<br> {{ $user->email }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Building Number/Name</strong> :<br> {{ $user->building_number }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Street Name</strong> :<br> {{ $user->street_name }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>City</strong> :<br> {{ $user->city }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Postcode</strong> :<br> {{ $user->postcode }}
            </li>
        </ul>
    </div>
    <div class='col-md-5 pull-right'>
        <img src="{{ $user->image }}" class='img-responsive' style='max-height:300px;margin-top:150px;cursor:pointer;'>
    </div>
</div>


<!-- Delete Modal -->
<div id="deleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

      <form class="modal-content" method="post" action="{{ route('destroyUser', $user->id) }}">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Delete {{ $user->username }}</h4>
        </div>
        <div class="modal-body">

            <div class='form-group'>
                <label>Are you sure you want to delete this user?
                    <select name="delete" class="form-control">
                        <option value="0" selected>No</option>
                        <option value="1">Yes</option>
                    </select>
                </label>
            </div>

        </div>
        <div class="modal-footer">
            <div class='pull-left'>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
            </div>
            <div class='pull-right'>
                <input type="submit" class="btn btn-primary">
            </div>
        </div>
    </form>

    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <form class="modal-content" method="post" action="{{ route('updateUser', $user->id) }}">
            {{ csrf_field() }}
            {{ method_field('PATCH') }}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit {{ $user->username }}</h4>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label>Which client(s) will this user belong to?
                        <select multiple class="form-control" name="clients[]">
                            {!! $user->getEditClientOptions($user) !!}
                        </select>
                    </label>
                </div>

            </div>
            <div class="modal-footer">
                <div class='pull-left'>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                </div>
                <div class='pull-right'>
                    <input type="submit" class="btn btn-primary">
                </div>
            </div>
        </form>

    </div>
</div>

@endsection