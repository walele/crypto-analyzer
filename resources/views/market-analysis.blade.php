@extends('layouts.app')


@section('content')
<h2>Table custom</h2>
<table id="markets">
  <thead>
    <tr>
      @foreach( $prices as $price)
        <th>{!! $price->timestamp !!} </th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    <tr>

      @foreach( $prices as $price)
      <td>{{ $price->price}}</td>
      @endforeach

    </tr>
  </tbody>
</table>

@endsection
