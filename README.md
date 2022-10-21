# OneSky Helper

This is a simple Helper class, which uses the OneSky SDK internally.

It is created to collect useful calls to the API, while staying agnostic
about the framework it resides in.

The main focus is that it can be used in Symfony Console Commands, which
then can be used in Composer, Symfony or Silex projects.


## Install

```
composer require zeecoder/onesky-helper
```

## Usage
```php
$key     = 123;
$secret  = 'abc';
$project = 1;

//legacy instantiation
$oneSky = new ZeeCoder\OneSky\Helper(['api_key' => $key, 'api_secret' => $secret, 'project_id' => $project]);
//new instantiation
$oneSky = ZeeCoder\OneSky\Helper::withConfig($key, $secret, $project);

$files = $oneSky->getProjectTranslationFiles();
//[...]
```

Check the `ZeeCoder\OneSky\Helper` class for the currently implemented API calls - the methods are pretty self-explanatory :)
