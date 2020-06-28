@extends('layouts.app')


@section('content')
<h2>Crypto Analyzer</h2>
<ul>
  <li><a href="{{ url('last-days-market-prices-diff')}}">last-days-market-prices-diff</a></li>
  <li><a href="{{ url('last-days-up-prices')}}">last-days-up-prices</a></li>
  <li><a href="{{ url('last-entries-moving-average')}}">Last entries moving average</a></li>
  <li><a href="{{ url('last-halfhour-diff')}}">Last half hour diff</a></li>
  <li><a href="{{ url('long-last-halfhour-diff')}}">Long Last half hour diff</a></li>
  <li><a href="{{ url('last-hour-diff')}}">Last hour diff</a></li>
  <li><a href="{{ url('last-3hours-diff')}}">Last 3 hours diff</a></li>
  <li><a href="{{ url('last-6hours-diff')}}">Last 6 hours diff</a></li>
  <li><a href="{{ url('last-12hours-diff')}}">Last 12 hours diff</a></li>
  <li><a href="{{ url('last-24hours-diff')}}">Last 24 hours diff</a></li>
  <li><a href="{{ url('current-bet')}}">Current bet</a></li>
  <li><a href="{{ url('bets-analyzer')}}">Bets analyzer</a></li>

</ul>
@endsection
