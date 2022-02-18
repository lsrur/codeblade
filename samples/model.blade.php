@php($name=\Str::of($table->name)->singular()->studly())
@cbSaveAs(app_path("Models/".$name.'.php'))
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {{$name}} extends Model
{
protected $table = '{{$table->name}}';
@if(!$table->timestamps)
public $timestamps = false;
@endif

{{-- Attribute casting --}}
protected $casts = [
@foreach ($table->fields as $field )
@if($field->cast)
'{{$field->name}}' => '{{$field->cast}}',
@endif
@endforeach
];

@foreach ($table->fields as $field )
@if($field->is_foreign)
public function {{\Str::singular($field->references)}}()
{
return $this->belongsTo({{\Str::of($field->references)->singular()->studly()}}::class);
}
@endif
@endforeach

@foreach($table->relations as $relation)
public function {{\Str::of($relation->model)->lower()->plural()}}()
{
@if($relation->type == 'has_many')
return $this->hasMany({{$relation->model}}::class);
@endif

@if($relation->type == 'belongs_to_many')
return $this->belongsToMany({{$relation->model}}::class);
@endif
}
@endforeach
}
