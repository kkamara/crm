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
               <ul class="nav nav-tabs">
                    @component('layouts.components.companyLogo')@endcomponent
               </ul>
            </div>

            <div class="panel-body">
               <div class="tab-content">

                  <div id="signup" class="tab-pane fade in active register">
                     <div class="container-fluid">
                        <div class="row">
                              <h2 class="text-center" style="color: #f0ad4e;"> <Strong> {{$title}} </Strong></h2> <hr />
                                 <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                       <div class="form-group">
                                          <div class="input-group">
                                             <div class="input-group-addon iga1">
                                                <span class="glyphicon glyphicon-envelope"></span>
                                             </div>
                                             <input type="text" class="form-control" placeholder="Enter Email" name="name">
                                          </div>
                                       </div>
                                    </div>
                                 </div>

                                <div class="col-xs-12 col-sm-12 col-md-12">

                                    <div class="col-xs-6 col-sm-4 col-sm-offset-8 col-md-4 col-sm-offset-8 front-page-links">
                                        <div class="form-group">
                                        <a href="/" data-toggle="modal"> Login page </a>
                                        </div>
                                    </div>
                                </div>
                                 <hr>
                                 <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                       <div class="form-group">
                                          <button type="submit" class="btn btn-lg btn-block btn-warning"> Submit</button>
                                       </div>
                                    </div>
                                 </div>
                        </div>
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