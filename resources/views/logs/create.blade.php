@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('logsHome') }}">Logs</a></li>
    <li class="breadcrumb-item active">Create</li>

@endsection

@section('content')


<div class="container-fluid main-container">

    <div class="row">
        
        @include('layouts.errors')

        <form method="post" action="{{ route('createLog') }}" class="form col-md-8 col-md-offset-2">
            {{ csrf_field() }}
            <input type="hidden" value="{{ Auth::user()->id }}" name="user_modified">

            <div class="form-group">
                <label>Which client is this for?
                    @if(!empty(auth()->user()->getClientsAssigned()))
                        
                        <select class="form-control" name='client_id' id='clientId'>

                            <option value='0'>Please select a client</option>
                            @forelse(auth()->user()->getClientsAssigned() as $client)
                                <option value='{{ $client->id }}'>
                                    @if(!empty($client->company))
                                        {{ $client->company }}
                                    @else
                                        {{ $client->first_name.' '.$client->last_name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    @else
                        <select class='form-control' disabled='true' name='client_id' id='clientId'>
                            <option value='0'>No clients assigned</option>
                        </select>
                    @endif
                </label>
            </div>

            <div class="form-group">
                <label>Title :
                    <input class="form-control" name="title">
                </label>
            </div>

            <div class="form-group">
                <label>Description :
                    <textarea cols="100%" rows="10" class="form-control" name="description"></textarea>
                </label>
            </div>

            <div class="form-group">
                <label>Body :
                    <textarea cols="100%" rows="10" class="form-control" name="body"></textarea>
                </label>
            </div>

            <div class="form-group">
                <label>Notes :
                    <textarea cols="100%" rows="10" class="form-control" name="notes"></textarea>
                </label>
            </div>

            <div class="form-group pull-right">
                <input class="btn btn-primary" type="submit">
            </div>

        </form>
    </div>
</div>


@endsection