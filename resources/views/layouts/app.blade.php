<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body class="{{$bodyClass ?? ''}}">
  <div class="main">
    <div class="container-fluid">
      @yield('content')
    </div>
  </div>
</body>

<script src="{{ mix('/js/app.js') }}"></script>

</html>
