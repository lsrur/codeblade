@cbSaveAs(base_path("generatedcode/resources/views/".$table->name.'_index.blade.php'))
{{-- @cbSaveAs(base_path("resources/views/".$table->name.'_index.blade.php')) --}}
<h1>{{$table->name->title()}}</h1>
<h3><a href="/{{$table->name}}/create">create one</a></h3>
<table>
@@foreach(${{$table->name}} as ${{$table->name->singular()}})
  <tr>
    @foreach($table->fields as $field)
      @if($field->base_type != 'json')
      <td>@cbCurly(${{$table->name->singular()}}->{{$field->name}}) </td>
      @endif
    @endforeach
    <td><a href="/{{$table->name}}/@cbCurly(${{$table->name->singular()}}->id)">Edit</a></td>
  </tr>
@@endforeach
</table>


