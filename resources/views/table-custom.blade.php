@extends('layouts.app')


@section('content')
<table id="markets">
  <thead>
    <tr>
      <th>Market Name</th>
      @foreach( $columns as $column)
        <th>{{ $column }} </th>
      @endforeach
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
