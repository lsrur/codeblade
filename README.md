# A handy and powerful code generator for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lsrur/codeblade.svg?style=flat-square)](https://packagist.org/packages/lsrur/codeblade)
[![Total Downloads](https://img.shields.io/packagist/dt/lsrur/codeblade.svg?style=flat-square)](https://packagist.org/packages/lsrur/codeblade)

As programmers we always find ourselves with the tedious need to write repetitive code for different models or tables of our application. As a code generator, Codeblade will help us in this process but with two big differences compared to other tools:

- Codeblade does not require you to write or maintain definition files (json, yaml or other metadata file), instead it reverse-engineers your database on the fly and exposes a data dictionary to your templates for code generation. 

- You write your own templates in pure Blade! Yes, Laravel Blade generating Laravel code such as models, controllers, views, form requests, but also Vue, React, Livewire components or any source code you need, just write the template with the Blade syntax you already know.

## Table of Contents
1. [Requirements](#requirements)
2. [Instalation](#instalation)
3. [Configuration](#requirements)
4. [Code generation](#requirements)
5. [Writing templates](#requirements)
	1. [Sub paragraph](#subparagraph1)
6. [Contributing](#requirements)
7. [Security](#requirements)
7. [License](#requirements)





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


## Configuration
Once


## Code generation


```bash
php artisan codeblade:make <template> <table1,table2> --force --copy
```
```bash
<template> : template file 
<tables> : one or multiple table names to be parsed and passed to the generator
--copy : copy output code to the clipboard instead of writing files
--params: parameters to be passed to the template
--force  : overwrite output files without asking
```

## Writing templates
Every time you execute a "make" command, Codeblade reverse-engineers the tables involved, creating a data dictionary which passes to the code generation template in the form of an object with the following properties:


| Table | |
|-----|----|
|name     |Name of the table |
|singular|The name of the table in the singular|
|modelName|Inferred model name based on Laravel naming conventions (contacts > Contact)|
|fields| Array of fields |
|relations| Array of relations  |

| Field | |
|-----|----|
|name     |Name of the field (string)|
|label    |Inferred label based on field's name (company_name -> Company Name)|
|var|Inferred var name (company_name -> $companyName)|
|primary|The field is primary key (boolean)  |
|autoincrement|The field is autoincrement (boolean)|
|index| The field has index (boolean)|
|default|Default value (any)|
|nullable|Field is nullable (boolean)|
|base_type|The base or type (ex: char and varchar has base_type=string)|
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


| Relation |Description |
|-----|----|

#### Codeblade Blade directives 


##### @cbSaveAs
This directive tells  




#### Basic template example

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

#### cbsaveAs
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

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email lautarosrur@gmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


