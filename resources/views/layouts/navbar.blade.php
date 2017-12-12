<!-- Sidebar -->
            <nav class="navbar navbar-inverse navbar-fixed-top" id="sidebar-wrapper" role="navigation">
                <ul class="nav sidebar-nav">
                    <li class="sidebar-brand">
                        <a href="/">
                        {{ config('app.name') }} {{ $title }}
                        </a>
                    </li>
                    <li>
                        <a href="/"><i class="fa fa-fw fa-home"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="/settings"><i class="fa fa-fw fa-cogs"></i> Settings</a>
                    </li>
                    <li>
                        <a href="logout"><i class="fa fa-fw fa-folder"></i> Logout</a>
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