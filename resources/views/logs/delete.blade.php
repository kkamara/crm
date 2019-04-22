@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('logsHome') }}">Logs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('showLog', $log->slug) }}">{{ $log->title }}</a></li>
    <li class="breadcrumb-item active">Delete</li>

@endsection

@section('content')

<div class="container-fluid main-container">

    <div class="row">
        <div class="card pull-left">
            <ul class="list-group list-group-flush ">
                <li class="list-group-item" style="border: none;">
                    <strong>Title</strong> :<br> {{ $log->title }}
                </li>
                <li class="list-group-item" style="border: none">
                    <strong>Description</strong>:<br> {!! $log->description !!}
                </li>
                <li class="list-group-item" style="border: none">
                    <strong>Body</strong>:<br> {!! $log->body !!}
                </li>
                <li class="list-group-item" style="border: none">
                    <strong>Notes</strong>:<br> {!! $log->notes !!}
                </li>
                <li class="list-group-item" style="border: none">
                    <strong>Created by</strong>:<br> <a href="#">{{ $log->user->name }} </a> at {{ $log->created_at->format('D-m-Y G:i:a') }}
                </li>

                @if(strtotime($log->updated_at) != strtotime($log->created_at))
                <li class="list-group-item" style="border: none">
                    <strong>Updated by</strong>:<br> <a href="#">{{ $log->updated_by->name }}</a> at {{ $log->updated_at->format('Y-m-d G:i:a') }}
                </li>
                @endif
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="text-center">
            <form class="form-inline" method="POST" action="{{ route('destroyLog', $log->slug) }}">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}

                <div class="form-group">
                    <label>Are you sure you want to delete this log?
                        <select name="delete" class="form-control">
                            <option value="0" selected>No</option>
                            <option value="1">Yes</option>
                        </select>

                        <input type="submit" class="form-control btn btn-primary">
                    </label>
                </div>

            </form>
        </div>
    </div>
    <br>
</div>

@endsection