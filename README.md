# Code Generator for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lsrur/codeblade.svg?style=flat-square)](https://packagist.org/packages/lsrur/codeblade)
[![Total Downloads](https://img.shields.io/packagist/dt/lsrur/codeblade.svg?style=flat-square)](https://packagist.org/packages/lsrur/codeblade)


Codeblade does not require you to write definition files, instead it reverse-engineers your database and exposes a data dictionary to your code generation templates. You write these templates in pure Blade! Yes, Blade generating Laravel code such as models, controllers, views, form requests, but also Vue, React or Livewire components or whatever you need, just write the template you need with the Blade syntax you already know.


## Requirements

- Laravel 9.x (Codeblade uses a new feature in Laravel 9 for inline compilation of Blade templates, it won't work with earlier versions). 
- MySQL (For now, Codeblade works only with MySQL/MariaDB connections. Reverse engineer tools for PgSQL will be available soon).

## Installation

You can install the package via composer:

```bash
composer require lsrur/codeblade
```

Publish the configuration file:

```bash
composer require lsrur/codeblade
```

Prepare the template folder in your project and copy sample templates:

```bash
php artisan codeblade:install
```


## Configuration
Once

## Usage
### Code generation command
```bash
php artisan codeblade:make <template> <table1,table2> --force --copy
```
```bash
<template> : the template file 
<tables> : one or multiple table names to be parsed and passed to the generator
--copy : copy output code to clipboard instead of writing files
--force  : will overwrite output files without asking
```

### Writing templates



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email lautarosrur@gmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


