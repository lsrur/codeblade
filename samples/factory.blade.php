@php($name=$table->name->singular()->studly())
@cbSaveAs(base_path('database/factories/'.$name.'Factory.php'))
{{-- @cbSaveAs(base_path('generatedcode/database/factories/'.$name.'Factory.php')) --}}

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
* @@extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\{{$name}}>
  */
  class {{$name}}Factory extends Factory
  {
  /**
  * Define the model's default state.
  *
  * @return array
  */
    public function definition()
    {
      return [
      @foreach($table->fields as $field)
        @if(!$field->is_autoincrement )
          @if($field->is_foreign)
            '{{$field->name}}' => \App\Models\{{$field->references->singular()->title()}}::pluck('{{$field->on}}')->random(),
            // '{{$field->name}}' => {{$field->references->singular()->title()}}::factory(),
          @else
            '{{$field->name}}' => $this->faker->{!!$field->faker!!},
          @endif
        @endif
      @endforeach
      ];
    }
  }
