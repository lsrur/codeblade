@php($name=$table->name->singular()->studly()->append('Request'))
@cbSaveAs(base_path("generatedcode/Requests/".$name.'.php'))
{{-- @cbSaveAs(app_path("Http/Requests/".$name.'.php')) --}}

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {{$name}} extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize()
  {
    return false;
  }

  /**
  * Get the validation rules that apply to the request.
  *
  * @return array
  */
  public function rules()
  {
    return [
      @foreach ($table->fields as $field )
        @if(! $field->is_autoincrement)
        ['{{$field->name}}' => '{{$field->rule}}'],
        @endif
      @endforeach
      ];
  }
}
