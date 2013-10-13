# Dir and File

Dir and File help you to do very common, often typed actions with php file utils

## usage
```php
$dir = new Dir('/my/root');

$dir->getFile('index.php')->writeContents('<?php echo "hello world"; ')->copy(new File('index.html', $dir));
```

```php
$dir->copy(Dir::createTemporary());
```

```php
$file->move(new File(...));
$file->copy(new File(...));
$file->copy(Dir::factoryTS(__DIR__));
```

```php
$file->getCreationTime();
$file->getModificationTime()->format('d.m.Y H:i');
$file->getAccessTime()->1i8n_format('d. F H:i');
```