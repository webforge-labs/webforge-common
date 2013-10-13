# JSON Converter

Decode a String (does not fail silently)

```php
$object = JSONConverter::create()->parse($jsonString);
```

Parse a File
```php
use Webforge\Common\System\File;

$object = JSONConverter::create()->parseFile(new File('composer.json'));
```

Stringify an object in pretty print
```php
use Webforge\Common\JS\JSONConverter;

$jsonc = new JSONConverter();
$jsonc->stringify($object, JSONConveter::PRETTY_PRINT)
```

## use with files
```php
use Webforge\Common\JS\JSONFile;

$jsonFile = new File('composer.json');

$jsonFile->modify(function() {
  return (object) array('and-now'=>'something-completely-different');
});

print $jsonFile
```

```php
$jsonFile->modify(function($json) {
  $json->name = 'webforge/new-common';
});
```

```php

```

```php

```