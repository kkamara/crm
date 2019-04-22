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

            <div class="form-group">
                <label>Which client is this for?
                    @if(!empty(auth()->user()->getClientsAssigned()))
                        
                        <select class="form-control" name='client_id' id='clientId'>

                            <option value='0'>Please select a client</option>
                            @forelse(\App\Client::getAccessibleClients(auth()->user())
                                ->orderBy('company', 'ASC')
                                ->get() as $client)
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
                    <input class="form-control" name="title" value="{{ $input['title'] or '' }}">
                </label>
            </div>

            <div class="form-group">
                <label>Description :
                    <textarea cols="100%" rows="10" class="form-control" name="description">{{ $input['description'] or '' }}</textarea>
                </label>
            </div>

            <div class="form-group">
                <label>Body :
                    <textarea cols="100%" rows="10" class="form-control" name="body">{{ $input['body'] or '' }}</textarea>
                </label>
            </div>

            <div class="form-group">
                <label>Notes (optional) :
                    <textarea cols="100%" rows="10" class="form-control" name="notes">{{ $input['notes'] or '' }}</textarea>
                </label>
            </div>

            <div class="form-group pull-right">
                <input class="btn btn-primary" type="submit">
            </div>

        </form>
    </div>
</div>


@endsection