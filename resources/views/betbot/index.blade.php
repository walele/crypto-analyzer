@extends('layouts.app')


@section('content')
<h2>BetBot v0.1</h2>

<div class="">
  {!! $content !!}

  @isset($tables)
    @foreach( $tables as $table)
      @include('betbot.table', ['table' => $table])
    @endforeach
  @endisset
</div>
@endsection
