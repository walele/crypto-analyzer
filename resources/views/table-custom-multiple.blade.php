@extends('layouts.app')


@section('content')

@foreach( $tables as $table)

  <h2>{{ $table['name'] }}</h2>
  <table class="table-analyze">
    <thead>
      <tr>
        <th>Market Name</th>
        @foreach( $table['columns'] as $column)
          <th>{!! $column !!} </th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach( $table['markets'] as $key => $market)
      <tr>
        <td>{{ $key}}</td>

        @foreach( $market as $data)
        <td>{!! $data !!}</td>
        @endforeach

      </tr>
      @endforeach
    </tbody>
  </table>

@endforeach

@endsection
