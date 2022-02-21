@cbSaveAs(base_path("generatedcode/resources/views/".$table->name.'_index.blade.php'))
{{-- @cbSaveAs(base_path("resources/views/".$table->name.'_create.blade.php')) --}}

<h1>Editing {{$table->name->singular()->title()}}</h1>
<form method="POST" action="/{{$table->name}}">
  @@csrf
  @foreach($table->fields as $field)
    @if($field->is_autoincrement || $field->base_type == 'json')
      @continue
    @endif
    <div>
        <label for='{{$field->name}}'>{{$field->name->title()}}</label>
        @if($field->type == 'enum' || $field->type == 'set')
          <select>
            @foreach($field->enum_options as $option)
              <option>{{$option}}</option>
            @endforeach
          </select>
        @else
          <input id="{{$field->name}}"  name="{{$field->name}}">
        @endif
        @@error('{{$field->name}}')
          <div style="color: red">@{{ $message }}</div>
        @@enderror
      </div>
  @endforeach
  <button type="submit" value="Update" >Create</button>
</form>


