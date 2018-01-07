@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('logsHome') }}">Logs</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>

@endsection

@section('content')

<div class="container-fluid main-container">
    <div class="pull-right">
        @can('edit log')
        <a href="{{ route('editLog', $log->id) }}" class="btn btn-info">Edit</a>
        @endcan
        @can('delete log')
        <a href="{{ route('deleteLog', $log->id) }}" class="btn btn-danger">Delete</a>
        @endcan
    </div>
    <div class="card pull-left">
        <ul class="list-group list-group-flush ">
            <li class="list-group-item" style="border: none;">
                <strong>Title</strong> :<br> {{ $log->title }}
            </li>
            <li class="list-group-item" style="border: none">
                <strong>Description</strong> :<br> {!! $log->description !!}
            </li>
            <li class="list-group-item" style="border: none">
                <strong>Body</strong> :<br> {!! $log->body !!}
            </li>
            <li class="list-group-item" style="border: none">
                <strong>Notes</strong> :<br> {!! $log->notes !!}
            </li>
            <li class="list-group-item" style="border: none">
                <strong>Created by</strong> :<br> <a href="#">{{ $log->user->name }} </a> at {{ $log->created_at->format('D-m-Y G:i:a') }}
            </li>
            
            @if(strtotime($log->updated_at) != strtotime($log->created_at))
            <li class="list-group-item" style="border: none">
                <strong>Updated by</strong> :<br> <a href="#">@if(!empty($log->updated_by)){{ $log->updated_by->name.' at ' }}@endif</a>{{ $log->updated_at->format('Y-m-d G:i:a') }}
            </li>
            @endif
            
            <li class="list-group-item" style="border: none">
                <strong>Assigned</strong> :<br> <a href="#">{{ $log->client->company }}</a>
            </li>
        </ul>
    </div>
</div>

@endsection