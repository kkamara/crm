@extends('layouts.master')

@section('breadcrumbs')
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')

    <div class="container-fluid main-container">
        <form method="get" action="{{ route('logsHome') }}" class="form-inline" style="padding:5px 0px 0px 20px">
            {{ csrf_field() }}
            
            <div class="row">
                <div class="form-group">
                    <label>Title
                        <input name="title" type="checkbox" @if(Request::has('title')) checked @endif>&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Description
                        <input name="desc" type="checkbox" @if(Request::has('description')) checked @endif>&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Created at
                        <input name="created_at" type="checkbox" @if(Request::has('created_at')) checked @endif>&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Updated at
                        <input name="updated_at" type="checkbox" @if(Request::has('updated_at')) checked @endif>&nbsp;&nbsp;
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>
                        <input {{ request('search') ? 'autofocus' : '' }} value='{{ Request::get('search') }}' name="search" type="text" placeholder="Search..." class="form-control">
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="submit" class="form-control">
                    </label>
                </div>
            </div>
        </form>

        <div class="text-center">
            Showing {{ $logs->total() }} results
        </div>
        
        <table class="table {{ count($logs) == 0 ? 'hide' : '' }}">

            <thead>
                <th width='15%'>Title</th>
                <th>Description</th>
                <th width='12%'>Created</th>
                <th width='12%'>Updated</th>
            </thead>

            <tbody>

                @forelse($logs as $log)

                    <tr>
                        <td><a href="{{ $log->path }}">{{ $log->title }}</a></td>
                        <td>{!! $log->short_description !!}</td>
                        <td>{{ $log->created_at->format('Y-m-d') }}</td>
                        @if(strtotime($log->created_at) != strtotime($log->updated_at))
                            <td>{{$log->updated_at->format('Y-m-d')}}</td>
                        @else
                            <td>Never</td>
                        @endif
                    </tr>

                @empty
                    <div class="text-center">
                        There are no logs to show.
                    </div>
                @endforelse

            </tbody>

        </table>
    </div>

        <div class="text-center">
                {{ $logs->links() }}
        </div>


@endsection