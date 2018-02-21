@extends('layouts.master')

@section('breadcrumbs')
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')

    <div class="container-fluid main-container">
        {{--  <form method="get" action="{{ route('usersHome') }}" class="form-inline" style="padding:5px 0px 0px 20px">
            {{ csrf_field() }}
            <br/>
            <div class="row">
                <div class="form-group">
                    <label>
                        <input {{ request('search') ? 'autofocus' : '' }} value='' name="search" type="text" placeholder="Search..." class="form-control">
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="submit" class="form-control">
                    </label>
                </div>
            </div>
        </form>  --}}
<br/>
        <div class="text-center">
            Showing {{ $users->total() }} results
        </div>
        
        <table class="table {{ count($users) == 0 ? 'hide' : '' }}">

            <thead>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Created</th>
                <th>Updated</th>
            </thead>

            <tbody>

                @forelse($users as $user)

                    <tr>
                        <td><a href="{{ $user->path() }}">{{ $user->username }}</a></td>
                        <td>{{ $user->first_name.' '.$user->last_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        @if(strtotime($user->created_at) != strtotime($user->updated_at))
                            <td>{{$user->updated_at->format('Y-m-d')}}</td>
                        @else
                            <td>Never</td>
                        @endif
                    </tr>

                @empty
                    <div class="text-center">
                        There are no users to show.
                    </div>
                @endforelse

            </tbody>

        </table>
    </div>

        <div class="text-center">
                {{ $users->links() }}
        </div>


@endsection