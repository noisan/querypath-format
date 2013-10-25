# QueryPath FormatExtension

FormatExtension is a [QueryPath](http://querypath.org/) extension that adds the
following methods:

1. `format($callback [, $args, [, $... ]])`
2. `formatAttr($name, $callback [, $args, [, $... ]])`


## Installation

With [Composer](http://getcomposer.org/), run:

```sh
$ php composer.phar require noi/querypath-format "*"
```

Alternatively, you can edit your `composer.json` manually and add the following:

```json
{
    "require": {
        "noi/querypath-format": "*"
    }
}
```


## Usage

### format()

```php
\QueryPath\DOMQuery format(callable $callback [, mixed $args [, $... ]])
```

A quick example:

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\FormatExtension');
$qp = qp('<?xml version="1.0"?><root><div>_apple_</div><div>_orange_</div></root>');

$qp->find('div')
        ->format('strtoupper')
        ->format('trim', '_')
        ->format(function ($text) {
            return '*' . $text . '*';
        });

$qp->writeXML();
```

OUTPUT:

```xml
<?xml version="1.0"?>
<root>
  <div>*APPLE*</div>
  <div>*ORANGE*</div>
</root>
```


### formatAttr()

```php
\QueryPath\DOMQuery formatAttr(string $name, callable $callback [, mixed $args [, $... ]])
```

A quick example:

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\FormatExtension');
$qp = qp('<?xml version="1.0"?><root>' .
        '<item label="_apple_" total="12,345,678" />' .
        '<item label="_orange_" total="987,654,321" />' .
        '</root>');

$qp->find('item')
        ->formatAttr('label', 'trim', '_')
        ->formatAttr('total', 'str_replace[2]', ',', '');

$qp->find('item')->formatAttr('label', function ($value) {
    return ucfirst(strtolower($value));
});

$qp->writeXML();
```

OUTPUT:

```xml
<?xml version="1.0"?>
<root>
  <item label="Apple" total="12345678"/>
  <item label="Orange" total="987654321"/>
</root>
```


## License

FormatExtension is licensed under the MIT License - see the `LICENSE` file for details.
