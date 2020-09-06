@extends('layouts.app')


@section('content')

<pre >
   @json($result, JSON_PRETTY_PRINT);
</pre>
@endsection
