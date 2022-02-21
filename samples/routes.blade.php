// use App\Http\Controllers\{{$table->model}}Controller;
Route::controller({{$table->model}}Controller::class)->group(function () {
  Route::get('/{{$table->name}}', 'index');
  Route::get('/{{$table->name}}/create', 'create');
  Route::get('/{{$table->name}}/{id}', 'show');
  Route::get('/{{$table->name}}/{id}/edit', 'edit');
  Route::post('/{{$table->name}}', 'store');
  Route::post('/{{$table->name}}/{id}', 'update');
  Route::post('/{{$table->name}}/{id}/delete', 'delete');
});


