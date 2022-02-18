@cbSaveAs(base_path('resources/views/'.$table->name.'-edit.blade.php'))
@php
$route = "{{route('{$table->name}',['id'=>".'$'."{$table->singular}->id])}}";
@endphp
<form method="put" action='{!!$route!!}'>
  @include("samples.form")
</form>
