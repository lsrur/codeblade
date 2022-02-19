# A handy and powerful code generator for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lsrur/codeblade.svg?style=flat-square)](https://packagist.org/packages/lsrur/codeblade)
[![Total Downloads](https://img.shields.io/packagist/dt/lsrur/codeblade.svg?style=flat-square)](https://packagist.org/packages/lsrur/codeblade)

As programmers we always find ourselves with the tedious need to write repetitive code for different models or tables of our application. As a code generator, Codeblade will free you from that boredom and will bring you two great features over other tools.

- Codeblade does not require you to write or maintain definition files (json, yaml or other metadata file), instead it reverse-engineers your database on the fly and exposes a data dictionary to your templates for code generation. 

- You write your own templates in pure Blade! Yes, Laravel Blade generating Laravel code such as models, controllers, views, seeders, form requests... but also Vue, React, Livewire or any source code you need, just write the template with the Blade syntax you already know.

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

Publish the configuration file:

```bash
composer require lsrur/codeblade
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

```bash
php artisan codeblade:make <template> <table1,table2> --force --copy
```

| Param|Description |
|-----|----|
|template|Template file in dot notation (samples.controller), it should exist in one of your template folders defined in the configuration file |
| table1,table2 | One or multiple table names (separated by commas) to be parsed and passed to the generator |
|--copy| Copy output code to the clipboard instead of writing files|
|--force|Oerwrite output files without asking|




## <a name="writing"></a>Writing templates
Every time you execute a "make" command, Codeblade reverse-engineers the tables involved, creating a data dictionary which passes to the code generation template in the form of an object with the following properties:

###<a name="table_object"></a>Table Class


| Property| Description|
|-----|----|
|name     |Name of the table |
|singular|The name of the table in the singular|
|modelName|Inferred model name based on Laravel naming conventions (contacts > Contact)|
|fields| Array of fields |
|relations| Array of relations  |

###<a name="field_object"></a>Field Class

| Property | Description |
|-----|----|
|name     |Name of the field|
|label    |Inferred label based on field's name (company_name -> Company Name)|
|var|Inferred var name (company_name -> $companyName)|
|primary|The field is primary key (boolean)  |
|autoincrement|The field is autoincrement (boolean)|
|index| The field has an index (boolean)|
|default|Default value (any)|
|nullable|Field is nullable (boolean)|
|base_type|The base or type (see table below)|
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

#### Base types

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
     ->comment("faker=company(),encrypt,foo=bar");

```
Then those properties will be available in your templates as direct properties of each field.

```
@foreach($tabe->fields as $field)
  @if($field->encrypt)
    // 'encrypt' will be true for company_name
  @endif

  {{$field->var}} = faker()->{{$field->faker}};
  // result: $companyName = faker()->company();
  
@endforeach

```

###<a name="field_object"></a>Relation Class

|Property |Description |
|-----|----|
|local_key| |
|type|'belongs_to_many' or 'has_many' |
|pivot|Pivot table name for 'belongs_to_many'|
|model|Related model name based on Laravel naming conventions |
|foreign_key | Related foreign key| 
|foreign_table | Related foreign table| 


###<a name="directives"></a>Blade Directives

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

###<a name="samples"></a>Example Templates
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

#### Use of params

```
php artisan codeblade:make mytemplate mytable --params=api,css=tailwind
```

```
@if($api'])
	doThis()
@endif

@if($parms['css'] == 'tailwind')
@endif

...
```


## <a name="contrib"></a>Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## <a name="security"></a>Security

If you discover any security related issues, please email lautarosrur@gmail.com instead of using the issue tracker.


## <a name="license"></a>License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


