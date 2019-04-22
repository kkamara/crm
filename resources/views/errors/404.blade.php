<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404 Not Found!</title>

    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
  </head>
  <body>
    <h1 style='display:block;margin:0px auto;width:550px;margin-top:20%;'>Oops, something went wrong ;(!</h1>

    <script>
      // You can also require other files to run in this process
      require('./renderer.js')
    </script>
  </body>
</html>
