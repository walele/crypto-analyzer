<h2>{{ $table->getName() }}</h2>
<table class="table-analyze">
  <thead>
    <tr>
      @foreach( $table->getColumns() as $th)
        <th>{{ $th }}</th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @foreach( $table->getRows() as $tr)
    <tr>
      @foreach( $tr as $td)
        <td>{!! $td !!}</td>
      @endforeach
    </tr>
    @endforeach
  </tbody>
</table>
