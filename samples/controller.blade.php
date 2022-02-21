@php
  $controllerName = $table->model.'Controller';
@endphp

@cbSaveAs(app_path('Http/Controllers/'.$controllerName.'.php'))
{{-- @cbSaveAs(base_path('generatedcode/Http/Controllers/'.$controllerName.'.php')) --}}

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{{$table->model}};

class {{$controllerName}} extends Controller
{

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    ${{$table->name}} = {{$table->model}}::all();
    return view('{{$table->name}}_index', ['{{$table->name}}'=> ${{$table->name}}]);
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    return view('{{$table->name}}_create');
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param \Illuminate\Http\Request $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    $validated = $request->validate([
      @foreach ($table->fields as $field )
        @if(! $field->is_autoincrement)
        '{{$field->name}}' => '{{$field->rule}}',
        @endif
      @endforeach
    ]);
    {{$table->model}}::create($validated);
    return redirect('/{{$table->name}}');
  }

  /**
  * Display the specified resource.
  *
  * @param int $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
    ${{$table->name->singular()}} = {{$table->model}}::findOrFail($id);
    return view('{{$table->name}}_edit', ['{{$table->name->singular()}}'=> ${{$table->name->singular()}}]);
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param int $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    ${{$table->name->singular()}} = {{$table->model}}::findOrFail($id);
    return view('{{$table->name}}_edit', ['{{$table->name->singular()}}'=> ${{$table->name->singular()}}]);
  }

  /**
  * Update the specified resource in storage.
  *
  * @param \Illuminate\Http\Request $request
  * @param int $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, $id)
  {
    $validated = $request->validate([
        @foreach ($table->fields as $field )
          @if(! $field->is_autoincrement)
            '{{$field->name}}' => '{{$field->rule}}',
          @endif
        @endforeach
      ]);
    {{$table->model}}::findOrFail($id)->update($validated);
    return redirect('/{{$table->name}}');
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param int $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    {{$table->model}}::findOrFail($id)->delete();
    return redirect('/{{$table->name}}');
  }

}
