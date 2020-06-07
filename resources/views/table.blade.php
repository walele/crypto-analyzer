@extends('layouts.app')


@section('content')
<table id="markets">
  <thead>
    <tr>
      <th>Market Name</th>
      <th>Time Diff</th>
      <th>Time 1</th>
      <th>Price 1</th>
      <th>Time 2</th>
      <th>Price 2</th>
      <th>Perc diff</th>
    </tr>
  </thead>
  <tbody>
    @foreach( $markets as $market)
    <tr>
      <td>{{ $market['name']}}</td>
      <td>{{ $market['timeDiff']}} </td>
      <td>{{ $market['time1']}} </td>
      <td>{{ $market['price1']}} </td>
      <td>{{ $market['time2']}} </td>
      <td>{{ $market['price2']}} </td>
      <td>{{ $market['diff']}} %</td>
    </tr>
    @endforeach
  </tbody>
</table>

@endsection
