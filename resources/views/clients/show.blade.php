@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('clientsHome') }}">Clients</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>

@endsection

@section('content')

<div class="container-fluid main-container">
    <div class="pull-right">
        @can('edit client')
        <a href="{{ route('editClient', $client->id) }}" class="btn btn-info">Edit</a>
        @endcan
        @can('delete client')
        <button data-toggle="modal" data-target="#deleteModal" class="btn btn-danger">Delete</button>
        @endcan
    </div>
    <div class="card pull-left">
        <ul class="list-group list-group-flush ">
            <li class="list-group-item" style="border: none;">
                <strong>Company</strong> :<br> {{ $client->company }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Name</strong> :<br> 
                    @if(!empty($client->first_name) && !empty($client->last_name))
                    {{ $client->first_name.' '.$client->last_name }}
                    @endif
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Contact Number</strong> :<br> {{ $client->contact_number or '' }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Email</strong> :<br> {{ $client->email }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Building Number/Name</strong> :<br> {{ $client->building_number }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Street Name</strong> :<br> {{ $client->street_name }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>City</strong> :<br> {{ $client->city }}
            </li>
            <li class="list-group-item" style="border: none;">
                <strong>Postcode</strong> :<br> {{ $client->postcode }}
            </li>
            <li class="list-group-item" style="border: none">
                <strong>Created by</strong> :<br> <a href="#">{{ $client->user->name }} </a> at {{ $client->created_at->format('Y-m-D G:i:a') }}
            </li>
            
            @if(strtotime($client->updated_at) != strtotime($client->created_at))
            <li class="list-group-item" style="border: none">
                <strong>Updated by</strong> :<br> <a href="#">@if(!empty($client->update_by)){{ $client->updated_by->name.' at ' }}@endif</a> {{ $client->updated_at->format('Y-m-D G:i:a') }}
            </li>
            @endif
        </ul>
    </div>
    <div class='col-md-5 pull-right'>
        <img src="{{ $client->image }}" class='img-responsive' style='max-height:300px;margin-top:150px;cursor:pointer;'>
    </div>
</div>


<!-- Delete Modal -->
<div id="deleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
  
      <form class="modal-content" method="post" action="{{ route('destroyClient', $client->id) }}">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Delete {{ $client->company }}</h4>
        </div>
        <div class="modal-body">
          
            <div class='form-group'>
                <label>Are you sure you want to delete this client?
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

@endsection