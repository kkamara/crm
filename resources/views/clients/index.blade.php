@extends('layouts.master')

@section('breadcrumbs')
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')

    <div class="container-fluid main-container">
        <form method="get" action="{{ route('clientsHome') }}" class="form-inline" style="padding:5px 0px 0px 20px">
            {{ csrf_field() }}

            <div class="row">
                <div class="form-group">
                    <label>Company
                        <input name="company" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Representative
                        <input name="representative" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Email
                        <input name="email" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Created at
                        <input name="created_at" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Updated at
                        <input name="updated_at" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
            </div>
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
        </form>

        <div class="text-center">
            Showing {{ $clients->total() }} results
        </div>

        <table class="table {{ count($clients) == 0 ? 'hide' : '' }}">

            <thead>
                <th>Company</th>
                <th>Director/Representative</th>
                <th>Email</th>
                <th>Created</th>
                <th>Updated</th>
            </thead>

            <tbody>

                @forelse($clients as $client)

                    <tr>
                        <td><a href="{{ $client->path }}">{{ $client->company }}</a></td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->created_at->format('Y-m-d') }}</td>
                        <td>{{ $client->updated_at->format('Y-m-d') }}</td>
                    </tr>

                @empty
                    <div class="text-center">
                        There are no clients to show.
                    </div>
                @endforelse

            </tbody>

        </table>
    </div>

        <div class="text-center">
                {{ $clients->links() }}
        </div>


@endsection