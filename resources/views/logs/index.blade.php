@extends('layouts.master')

@section('content')

    <form method="post" action="/logs" class="form-inline">
        {{ csrf_field() }}

        <div class="form-group">
            <label>
                <input name="search" type="text" placeholder="Search..." class="form-control">
            </label>
        </div>

        <div class="form-group">
            <label>
                <input name="search" type="submit" class="form-control">
            </label>
        </div>
    </form>

    <div class="text-center">
        Showing {{ $logs->perPage() }} out of {{ $logs->total() }}
    </div>
    
    <table class="table">

        <thead>
            <th>Id</th>
            <th>Title</th>
            <th>Description</th>
            <th>Created</th>
            <th>Updated</th>
        </thead>

        <tbody>

            @forelse($logs as $log)

                <tr>
                    <td><a href="#">{{ $log->id }}</a></td>
                    <td>{{ $log->title }}</td>
                    <td>{{ $log->description}}</td>
                    <td>{{ $log->created_at}}</td>
                    @if($log->updated_at)
                        <td>{{$log->updated_at->diffForHumans()}}</td>
                    @else
                        <td>n/a</td>
                    @endif
                </tr>

            @empty
                <div class="text-center">
                    There are no logs to show
                </div>
            @endforelse

        </tbody>

    </table>

    <div class="text-center">
            {{ $logs->links() }}
    </div>


@endsection