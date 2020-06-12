@extends('layouts.app')


@section('content')
<table id="markets">
  <thead>
    <tr>
      <th>Market Name</th>
      @foreach( $columns as $column)
        <th>{!! $column !!} </th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @foreach( $markets as $key => $market)
    <tr>
      <td>{{ $key}}</td>

      @foreach( $market as $data)
      <td>{{ $data}}</td>
      @endforeach

    </tr>
    @endforeach
  </tbody>
</table>

@endsection
