# BETA 

# A handy and powerful code generator for Laravel

As programmers we always face the tedious need to write repetitive code for different models or tables of our application. As a code generator, Codeblade will free you from that boredom and will bring you two great features over other similar tools. 

- Codeblade does not require you to write and maintain definition files (json, yaml or any other metadata file), instead it reverse-engineers your database on the fly and exposes a data dictionary to your templates for code generation. Handy.

- Codeblade let you write your own templates in pure Blade! Yes, Laravel Blade generating Laravel code such as models, controllers, views, seeders, factories, form requests... but also Vue, React, Livewire or any source code you need, just write the template with the Blade syntax you already know. Powerful.



## Table of Contents
1. [Requirements](#requirements)
2. [Instalation](#instalation)
3. [Configuration](#config)
4. [Code generation](#codegen)
5. [Writing templates](#writing)
	1. [Table Class](#table_object)
	2. [Field Class](#field_object)
	3. [Relation Class](#relation_object)
	4. [Directives](#directives)
	5. [Examples](#samples)
6. [Contributing](#contrib)
7. [Security](#security)
7. [License](#license)





## <a name="requirements"></a>Requirements 

- Laravel 9.x (Codeblade uses a new feature in Laravel 9 for inline compilation of Blade templates, it won't work with earlier versions). 

- MySQL (For now, Codeblade only works with MySQL/MariaDB connections. Reverse engineering for pgsql is on the way).

## <a name="instalation"></a>Installation

You can install the package via composer:

```bash
composer require lsrur/codeblade
```

Publish the configuration file (it will be useful):

```bash
php artisan vendor:publish --provider="Lsrur\Codeblade\CodebladeServiceProvider"
```

Prepare the templates folder in your project and copy the examples:

```bash
php artisan codeblade:install
```


## <a name="config"></a>Configuration
There are two configuration keys in config/codeblade.php:

| Key|Description |
|-----|----|
|template_folders|Array of folders where to look for the templates. You can include a shared template folder located outside your project.|
|pbcopy_command|Shell command for copying file contents to the clipboard (as pbcopy is for linux/osx)|

## <a name="codegen"></a>Code generation

```
php artisan codeblade:make <template> <table1,table2> --force --copy
```

| Param|Description |
|-----|----|
|template|Template file in dot notation (samples.controller), it should exist in one of your template folders/subfolders defined in the configuration |
| table1,table2 | One or multiple table names (separated by commas) to be parsed and passed to the generator |
|--params=|Parameters to be passed to the template (see below)|
|--copy| Copy output code to the clipboard instead of writing files|
|--force|Oerwrite output files without asking|


### Template parameters 
Codeblade allows you to specify one or multiple (comma separated with no spaces) parameters that will be passed to the template:

```
php artisan codeblade:make mytemplate mytable --params=flag,foo=bar
```

Those parameters will be usable from the template as follows:

```
@if($params->flag)
  flag is ON
@endif

@if(! $params->noexist)
  noexist is OFF or does not exists
@endif

{{$params->foo}}
// Result 'bar'

@foreach($params->all as $key=>$value)
  {{$key}}
@endforeach
// Result flag foo

```


## <a name="writing"></a>Writing templates
Every time you execute a "make" command, Codeblade reverse-engineers the tables involved, creating a data dictionary which passes to the code generation template in the form of a Table object with the following properties:

### <a name="table_object"></a>Table Class


| Property| Description|
|-----|----|
|name     |Name of the table as Stringable instance [(see stringables)](#stringables) |
|singular|The name of the table in the singular|
|modelName|Inferred model name based on Laravel naming conventions (contacts > Contact)|
|fields| Array of Field objects |
|relations| Array of Relation objects  |

### <a name="field_object"></a> Field Class

| Property | Description |
|-----|----|
|name|Name of the field as Stringable instance [(see stringables)](#stringables)|
|primary|The field is primary key (boolean)  |
|autoincrement|The field is autoincrement (boolean)|
|index| The field has an index (boolean)|
|default|Default value (any)|
|nullable|Field is nullable (boolean)|
|base_type|The base type [(see base types)](#base_types)|
|type| Field type |
|size| Field size or total digits |
|scale|Decimal digits|
|enum_options| Array of options if field type is enum or set|
|is_foreign| Field is foreign|
|references| Referenced table|
|on| Referenced key on foreign table|
|rule|Inferred validation rule based on field properties (type, size, slcale, nullable, etc.)|
|cast|Cast type (Ex: json->array)|
|custom_property| See custom properties below| 


#### <a name="base_types"></a>Base types
base types are useful for grouping similar data types together and then using those groups in your templates instead of type by type:

```
@foreach($Table->fields as $field)
	@includeIf($field->base_type == 'string', 'partials.forms.textinput');
	@includeIf($field->base_type == 'integer', 'partials.forms.integerinput');
@endforeach
```


| MySQL Field type |Base type |
|-----|----|
|char, varchar|string| 
|text, longtext, tinytext, mediumtext|text| 
|int, smallint, mediumint, bigint|integer| 
|tinyint|boolean|
|float, decimal, double|decimal|
|date|date|
|datetime, timestamp|datetime|
|blob, binary, longblob, mediumblob, tinyblob, varbinary|binary|
|enum | enum|
|set | set|
|json, jsonb | json|

 
#### Custom properties 
Codeblade will parse the "comment" metadata of each field looking for custom properties. You can add these properties in the field definition during migration in the following way:

```
...
Schema::create('contacts', function (Blueprint $table) {
  $table->string("company_name")
     ->comment("faker=company(),flag,foo=bar");
...
```

Then those properties will be available in your templates as direct properties of each field.

```
@foreach($tabe->fields as $field)
  @if($field->flag)
    // 'flag' will be true for company_name
  @endif
  
  @if($field->foo == 'bar')
    // 
  @endif

  ${{$field->name->camel()}} = faker()->{{$field->faker}};
  // result: $companyName = faker()->company();
  
@endforeach

```

### <a name="field_object"></a>Relation Class

|Property |Description |
|-----|----|
|local_key| |
|type|'belongs_to_many' or 'has_many' |
|pivot|Pivot table name for 'belongs_to_many'|
|model|Related model name based on Laravel naming conventions |
|foreign_key | Related foreign key| 
|foreign_table | Related foreign table| 


#### <a name="stringables"></a>Stringables
The "name" property of Table and Field classes are returned as Stringable instances, so you can use them as-is or chain \Str methods:

```
{{$Table->name}}
// contacts

{{$Table->name->singular()->title()->append('Controller'}}
// ContactController

@foreach($Table->fields as $field)
  {{$field->name->camel()->prepend('$')}} = $request->{{$field->name}};
  // ... $companyName = $request->company_name;
@endforeach

```

### <a name="directives"></a>Blade Directives

##### @cbSaveAs() 
Tells Codeblade where to write the generated code. Every template must have a `@cbSaveAs` directive unless it will always be used with the --copy option (copy to the clipboard) or always called as part of another template with `@include`. 
It doesn't matter if you use slashes or backslashes, Codeblade will adjust the output to your OS.


```
@cbSaveAs(app_path('Http/Controllers/'.$table->modelName.'Controller.php'))
// for table "contacts", the file will be written in app/Http/Controllers/ContactControllar.php

```

##### @cbRun() 
Tells Codeblade to execute another template.

```
{{-- This is a CRUD template --}}
@cbRun('model')
@cbRun('controller')
@cbRun('edit_view')
@cbRun('create_view')
@cbRun('index_view')

```

### <a name="samples"></a>Example Templates
#### Basic

```
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{{$table->modelName}};

class {{$table->modelName}}Controller extends Controller
{

public function index()
{
${{$table->name}} = {{$table->modelName}}::all();

return view('{{$table->singular}}.index', compact(${{$table->name}}));
}
...
```

#### Using parameters

```
php artisan codeblade:make mytemplate mytable --params=api,css=tailwind
```

```
@if($params['api'])
 //
@endif

@if($params['css'] == 'tailwind')
  // 
@endif

...
```


## <a name="contrib"></a>Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## <a name="security"></a>Security

If you discover any security related issues, please email lautarosrur@gmail.com instead of using the issue tracker.


## <a name="license"></a>License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


