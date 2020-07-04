@extends('layouts.app')


@section('content')
<h2>BetBot v0.1</h2>

<div class="">
  {!! $content !!}

  @isset($table)
    @include('betbot.table', ['table' => $table])
  @endisset
</div>
@endsection
