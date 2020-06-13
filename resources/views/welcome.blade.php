@extends('layouts.app')


@section('content')
<h2>Crypto Analyzer</h2>
<ul>
  <li><a href="{{ url('last-days-market-prices-diff')}}">last-days-market-prices-diff</a></li>
  <li><a href="{{ url('last-days-up-prices')}}">last-days-up-prices</a></li>
  <li><a href="{{ url('last-hour-diff')}}">Last hours diff</a></li>
  <li><a href="{{ url('last-6hours-diff')}}">Last 6 hours diff</a></li>
</ul>
@endsection
