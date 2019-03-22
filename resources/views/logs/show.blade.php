@extends('layouts.master')

@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('logsHome') }}">Logs</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>

@endsection

@section('content')

<div class="container-fluid main-container">
    <div class="pull-right">
        @can('edit log')
        {{-- <a href="{{ route('editLog', $log->slug) }}" class="btn btn-info">Edit</a> --}}
        <button data-toggle="modal" data-target="#editModal" class="btn btn-info">Edit</button>
        @endcan
        @can('delete log')
        <button data-toggle="modal" data-target="#deleteModal" class="btn btn-danger">Delete</button>
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
                <strong>Created by</strong> :<br> <a href="#">{{ $log->user->name }} </a> at {{ $log->created_at->format('Y-m-D G:i') }}
            </li>

            @if(null !== $log->userUpdated)
            <li class="list-group-item" style="border: none">
                <strong>Updated by</strong> :<br> <a href="#">{{ $log->userUpdated->name.' at ' }}</a>{{ $log->updated_at->format('Y-m-D G:i') }}
            </li>
            @endif

            <li class="list-group-item" style="border: none">
                <strong>Assigned to Company</strong> :<br> <a href="#">{{ $log->client->company }}</a>
            </li>
        </ul>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

      <form class="modal-content" method="post" action="{{ route('destroyLog', $log->slug) }}">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Delete {{ $log->title }}</h4>
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

<!-- Edit Modal -->
<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

      <form class="modal-content" method="post" action="{{ route('updateLog', $log->slug) }}" enctype="multipart/form-data">
        {{ csrf_field() }}
        {{ method_field('PATCH') }}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit {{ $log->title }}</h4>
        </div>
        <div class="modal-body">

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
