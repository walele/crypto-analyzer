<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
</head>
<body class="{{$bodyClass ?? ''}}">
  <div class="main">
    <h1><a href="{{ url('/') }}">Crypto Analyzer</a></h1>
    <div class="container">
      @yield('content')
    </div>
  </div>
</body>

<script src="{{ mix('/js/app.js') }}"></script>

</html>
