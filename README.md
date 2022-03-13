

# A handy and powerful code generator for Laravel

As programmers we always face the tedious need to write repetitive code for different models or tables of our application. As a code generator, CodeBlade will relieve you of that boredom but with two big differences from other similar tools:

- CodeBlade does not require you to write and maintain definition files (json, yaml or any other metadata file), instead it reverse-engineers your database on the fly and exposes a data dictionary to your templates for code generation.

- CodeBlade let you write your own templates in pure Blade! Yes, the power of Laravel Blade generating Laravel components such as models, controllers, views, seeders, factories, form requests... but also Vue, React, Livewire, html or any source code you need, just write the template with the Blade syntax you already know.


> Code Blade is at an early stage, please tell me about your experience installing and getting started with it.


## Table of Contents
1. [Requirements](#requirements)
2. [Instalation](#instalation)
3. [Configuration](#config)
4. [Code generation](#codegen)
5. [Writing templates](#writing)
	1. [Table Properties](#table_object)
	2. [Field Properties](#field_object)
	3. [Relation Properties](#relation_object)
	4. [Directives](#directives)
	5. [Examples](#samples)
6. [Contributing](#contrib)
7. [Security](#security)
7. [License](#license)


## <a name="requirements"></a>Requirements 

- Laravel 9+ (CodeBlade uses a new feature in Laravel 9 for inline compilation of Blade templates). 

- MySQL (For now, CodeBlade only works with MySQL/MariaDB connections. Reverse engineering for pgsql is on the way).

## <a name="instalation"></a>Installation

You can install the package via composer:

```
composer require lsrur/CodeBlade
```

Publish the configuration file (it will be useful):

```
php artisan vendor:publish --provider="Lsrur\Codeblade\CodebladeServiceProvider"
```

Prepare the templates folder in your project and copy the examples:

```
php artisan codeblade:install
```


## <a name="config"></a>Configuration
There are two configuration keys in config/CodeBlade.php:

| Key|Description |
|-----|----|
|template_folders|Array of folders where to look for the templates. You can include a shared template folder located outside your project.|
|pbcopy_command|Shell command for copying file contents to the clipboard (as pbcopy is for linux/osx)|

## <a name="codegen"></a>Code generation

```
php artisan codeblade:make <template> <table1,table2> --params= --force --copy
```

| Param|Description |
|-----|----|
|template|Template file in dot notation (samples.controller), it should exist in one of your template folders/subfolders defined in the configuration |
| table1,table2 | One or multiple table names (separated by commas) to be parsed and passed to the generator |
|--params=|Parameters to be passed to the template (see below)|
|--copy| Copy output code to the clipboard instead of writing files|
|--force|Oerwrite output files without asking|


### Template parameters 
CodeBlade allows you to specify one or multiple (comma separated with no spaces) parameters that will be passed to the template:

```
php artisan codeblade:make mytemplate mytable --params=flag,foo=bar
```

Those parameters will be usable from the template as follows:

```php
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
Every time we execute a "make" command, CodeBlade reverse-engineers the tables involved, creating a data dictionary which passes to the code generation template in the form of a Table object. Then we write our templates as follows:

```php
@forarch($table->fields as $field)
  @if(! $field->is_autoincrement)
    {{$field->camel()->prepend('$')}} = $request->{{$field->name}};
  @endif
@endforeach
```

### <a name="table_object"></a>Table Properties


| Property| Description|
|-----|----|
|name     |Name of the table as Stringable instance [(see stringables)](#stringables) |
|model|Inferred model name based on Laravel naming conventions|
|fields| Array of Field objects |
|primary| Array of primary keys as Field objects |
|relations| Array of Relation objects  |

### <a name="field_object"></a> Field Properties

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
|references| Referenced table as Stringable [(see stringables)](#stringables)|
|on| Referenced key on foreign table|
|rule|Inferred validation rule based on field properties (type, size, foreign, nullable, etc.) \*|
|faker|Inferred faker method ased on field type and foreign properties \*|
|cast|Cast type (Ex: json->array) \*|
|custom_property| [see custom properties below](#custom_props)| 

\* These properties can be overridden by custom_properties 

#### <a name="base_types"></a>Base types
Base types are useful for grouping fields of similar -but not the same- data types.

```php
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

 
#### <a name="custom_props"></a>Custom properties 
CodeBlade will parse the "comment" metadata of each field looking for custom properties. You can add these properties in the field definition during migration in the following way:

```php
...
Schema::create('contacts', function (Blueprint $table) {
  $table->string("company_name")
     ->comment("faker=company(),flag,foo=bar");
...
```

Then those properties will be available in your templates as direct properties of each field.

```php
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

### <a name="relation_object"></a>Relation Properties

|Property |Description |
|-----|----|
|local_key| Local referenced field name |
|type|'belongs_to_many' or 'has_many' |
|pivot|Pivot table name for 'belongs_to_many'|
|model|Related model name based on Laravel naming conventions |
|foreign_key | Related foreign key for 'has_many'| 
|foreign_table | Related foreign table for 'has_many'| 


#### <a name="stringables"></a>Stringables
Properties returned as Stringable instances can be used as-is or by chaining \Str methods:

```php
{{$Table->name}}
// contacts

{{$Table->name->singular()->title()->append('Controller'}}
// ContactController

@foreach($Table->fields as $field)
  {{$field->name->camel()->prepend('$')}} = $request->{{$field->name}};
  // $companyName = $request->company_name;
@endforeach

```

### <a name="directives"></a>Blade Directives

##### @cbSaveAs() 
Tells CodeBlade where to write the generated code. If a template does not specify this directive, the resulting code will be copied to the clipboard.

```
@cbSaveAs(app_path('Http/Controllers/'.$table->model.'Controller.php'))
// for table "contacts", the file will be written in app/Http/Controllers/ContactController.php

```

##### @cbRun() 
Tells CodeBlade to execute another template, same tables and parameters will be applied.

```php
{{-- This is a CRUD template --}}
@cbRun('model')
@cbRun('controller')
@cbRun('edit_view')
@cbRun('create_view')
@cbRun('index_view')

```

##### @cbCurly() 
Wraps the output in curly braces, useful when generating Blade or Vue views. 

```php
@cbCurly({{$table->name->singular()->prepend('$')}}->{{$field->name}})
=> {{$contact->company_name}}

<div>@cbCurly({{$table->name->singular()}}.{{$field->name}})</div>
=> <div>{{contact.company_name}}</div>
```

### <a name="samples"></a>Example Templates

Take a look at the [samples](https://github.com/lsrur/CodeBlade/tree/master/samples) folder of this repo.
In order not to interfere with your project, the template examples provided generate code in `yourprojectroot/generatedcode` folder. Change the cbSaveAs line in the templates to write in the appropriate project folders.

The examples are provided to guide you in developing your own templates, they are not admin panel builders or anything like that.


## <a name="contrib"></a>Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## <a name="security"></a>Security

If you discover any security related issues, please email lautarosrur@gmail.com instead of using the issue tracker.


## <a name="license"></a>License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


