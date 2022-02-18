@php
$controllerName = $table->modelName.'Controller';
@endphp

@cbSaveAs(app_path('Http/Controllers/'.$controllerName.'.php'))

// Generated with Codeblade template samples.controller

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{{$table->modelName}};

class {{$controllerName}} extends Controller
{

/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index()
{
${{$table->name}} = {{$table->modelName}}::all();

return view('{{$table->singular}}.index', compact(${{$table->name}}));
}

/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
return view('{{$table->name}}.create');
}

/**
* Store a newly created resource in storage.
*
* @param \Illuminate\Http\Request $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
$request->validate([
@foreach ($table->fields as $field )
['{{$field->name}}' => '{{$field->rule}}'],
@endforeach
]);
{{$table->modelName}}::insert($request->validated);
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
${{$table->singular}} = {{$table->modelName}}::findOrFail($id);
return view('{{$table->singular}}.show', compact(${{$table->singular}}));
}

/**
* Show the form for editing the specified resource.
*
* @param int $id
* @return \Illuminate\Http\Response
*/
public function edit($id)
{
${{$table->singular}} = {{$table->modelName}}::findOrFail($id);
return view('{{$table->singular}}.edit', compact(${{$table->singular}}));
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
$request->validate([
@foreach ($table->fields as $field )
@if($field->name !='id')
['{{$field->name}}' => '{{$field->rule}}'],
@endif
@endforeach
]);
{{$table->modelName}}::findOrFail($id)->update($request->validated);
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
{{$table->modelName}}::findOrFail($id)->delete();
return redirect('/{{$table->name}}');
}
}
