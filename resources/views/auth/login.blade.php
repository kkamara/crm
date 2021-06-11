<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>{{$title}} | {{config('app.name')}}</title>

    @include('layouts.stylesheets')
    <link rel="stylesheet" href="/css/frontpage.css" type="text/css">
</head>
<body>

<div class="container">
      <div class="row">
         <div class="col-md-6 col-md-offset-3">
         <div class="panel with-nav-tabs panel-info">
            <div class="panel-heading">
               @component('layouts.components.companyLogo')@endcomponent
            </div>

            <div class="panel-body">
               <div class="tab-content">
                  <div id="login" class="tab-pane fade in active register">
                     <div class="container-fluid">
                        <form class="row" method="POST" action="/login">
                        {{ csrf_field() }}
                           <h2 class="text-center" style="color: #5cb85c;"> <strong> {{$title}}  </strong></h2>
                           <hr />
                           <small>
                              <p style='color:#000'>Creds</p>
                              <ul>
                                 @foreach($loginEmails as $loginEmail)
                                     <li>{{$loginEmail['email']}}</li>
                                 @endforeach
                              </ul>
                           </small>
                           <hr />
                            @include('layouts.errors')
                              <div class="row">
                                 <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                       <div class="input-group">
                                          <div class="input-group-addon">
                                             <span class="glyphicon glyphicon-user"></span>
                                          </div>
                                          <input autofocus type="email" placeholder="Email Address" name="email" class="form-control">
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              <div class="row">
                                 <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                       <div class="input-group">
                                          <div class="input-group-addon">
                                             <span class="glyphicon glyphicon-lock"></span>
                                          </div>

                                          <input type="password" placeholder="Password" name="password" class="form-control" value="secret" />
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input value="1" checked type="checkbox" name="remember_me"> Remember Me
                                        </label>
                                    </div>
                                 </div>

                                  <div class="col-xs-6 col-sm-6 col-md-6 front-page-links">
                                    <div class="form-group">
                                       <a href="/forgot" data-toggle="modal"> Forgot Password? </a>
                                    </div>
                                 </div>
                              </div>
                              <hr />
                              <div class="row">
                                 <div class="col-xs-12 col-sm-12 col-md-12">
                                    <input type="submit" value="Login" class="btn btn-success btn-block btn-lg">
                                 </div>
                              </div>

                        </form>
                     </div> 
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

    @include('layouts.footer')

    @section('layouts.scripts')

    @endsection
    
</body>
</html>