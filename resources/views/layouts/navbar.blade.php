<!-- Sidebar -->
            <nav class="navbar navbar-inverse navbar-fixed-top" id="sidebar-wrapper" role="navigation">
                <ul class="nav sidebar-nav">
                    <li class="sidebar-brand">
                        <a href="/">
                            Management
                        </a>
                    </li>
                    <li>
                        <a href="/"><i class="fa fa-fw fa-home"></i> Dashboard</a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-fw fa-briefcase"></i> Clients <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="dropdown-header">Client Options</li>
                            <li><a href="{{ route('clientsHome') }}">View Clients</a></li>
                            <li><a href="{{ route('createClient') }}">Create Client</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-fw fa-users"></i> Users <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="dropdown-header">User Options</li>
                            <li><a href="#">View Users</a></li>
                            <li><a href="#">Create User Account</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-fw fa-tasks"></i> Logs <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="dropdown-header">Log Options</li>
                            <li><a href="{{ route('logsHome') }}">View Logs</a></li>
                            <li><a href="{{ route('createLog') }}">Create Log</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('showSettings') }}"><i class="fa fa-fw fa-cogs"></i> Settings</a>
                    </li>
                    <li>
                        <a href="{{ route('logout') }}"><i class="fa fa-fw fa-folder"></i> Logout</a>
                    </li>
                    {{--  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-fw fa-plus"></i> Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-header">Dropdown heading</li>
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li><a href="#">Separated link</a></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                    </li>  --}}
                </ul>
            </nav>
            <!-- /#sidebar-wrapper -->