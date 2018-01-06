@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('logsHome') }}">View Logs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('showLog', $log->id) }}">{{ $log->title }}</a></li>
    <li class="breadcrum-item active">Edit</li>

@endsection


@section('content')

<div class="container-fluid main-container">

    <div class="row">
        
        @include('layouts.errors')

        <form method="post" action="{{ route('updateLog', $log->id) }}" class="form col-md-8 col-md-offset-2">
            {{ method_field('PATCH') }}
            {{ csrf_field() }}

            <input type="hidden" value="{{ Auth::user()->id }}" name="user_modified">

            <div class="form-group">
                <label>Title :
                    <input class="form-control" name="title" value="{{ $log->title }}">
                </label>
            </div>

            <div class="form-group">
                <label>Description :
                    <textarea cols="100%" rows="10" class="form-control" name="description">{!! $log->edit_description !!}</textarea>
                </label>
            </div>

            <div class="form-group">
                <label>Body :
                    <textarea cols="100%" rows="10" class="form-control" name="body">{!! $log->edit_body !!}</textarea>
                </label>
            </div>

            <div class="form-group">
                <label>Notes :
                    <textarea cols="100%" rows="10" class="form-control" name="notes">{!! $log->edit_notes !!}</textarea>
                </label>
            </div>

            <div class="form-group pull-right">
                <input class="btn btn-primary" type="submit">
            </div>
        </form>
    </div>
</div>

@endsection