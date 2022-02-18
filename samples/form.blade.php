@foreach($table->fields as $field)
@if(in_array($field->name, ['id','created_at','updated_at']))
@continue
@endif
<label for='{{$field->name}}'>{{\Str::of($field->name)->headline()}}</label>
@if($field->is_foreign)
<select></select>
@else
@if($field->base_type == 'decimal')
<input type="number" id='{{$field->name}}' name='{{$field->name}}' />
@elseif($field->base_type == 'date')
<input type="date" id='{{$field->name}}' name='{{$field->name}}' />
@elseif($field->base_type == 'text')
<textarea id='{{$field->name}}'></textarea>
@else
<input type="text" id='{{$field->name}}' name='{{$field->name}}' />
@endif
@endif
@endforeach
