@cbSaveAs(base_path('resources/views/'.$table->name.'-create.blade.php'))
@php
$route = "{{route('{$table->name}')}}";
@endphp
<form method="post" action='{!!$route!!}'>
  @include("samples.form")
</form>
