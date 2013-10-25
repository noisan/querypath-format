# QueryPath FormatExtension

FormatExtension は [QueryPath](http://querypath.org/) 用エクステンションです。
以下の拡張メソッドを提供します。

1. `format($callback [, $args, [, $... ]])`
2. `formatAttr($name, $callback [, $args, [, $... ]])`


## インストール

[Composer](http://getcomposer.org/) を使用して以下を実行してください。

```sh
$ php composer.phar require noi/querypath-format "*"
```

または、`composer.json` を編集し、以下の行を含めてください。

```json
{
    "require": {
        "noi/querypath-format": "*"
    }
}
```


## 使い方

### format()

```php
\QueryPath\DOMQuery format(callable $callback [, mixed $args [, $... ]])
```

* このメソッドは、選択中のノードのテキスト値(InnerText)を
  `$callback` を使って変換します。
* 省略可能な可変長引数 `$args` 以降に値を指定した場合は、
  `$callback` の引数として使用します。

簡単な使用例：

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

表示結果：

```xml
<?xml version="1.0"?>
<root>
  <div>*APPLE*</div>
  <div>*ORANGE*</div>
</root>
```

ノードのテキスト値はデフォルトで `$callback` の最初の引数になります。
この引数位置を変更したい場合は、以下のような特殊な指定方法を使ってください。

* `$qp->format('str_replace[2]', ' ', '-');`
* `$qp->format(array($object, $method, 2), ' ', '-');`

上記の形式でオフセット `2` を指定すると、
ノードのテキスト値はコールバックの3番目の引数になります。
（`formatAttr()` の使用例に引数位置指定のコード例があります）


### formatAttr()

```php
\QueryPath\DOMQuery formatAttr(string $name, callable $callback [, mixed $args [, $... ]])
```

* `format()` との違いは、変換対象がノードの属性値になる点です。

簡単な使用例：

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

表示結果：

```xml
<?xml version="1.0"?>
<root>
  <item label="Apple" total="12345678"/>
  <item label="Orange" total="987654321"/>
</root>
```


## License

FormatExtension クラスのライセンスは、MITライセンスです。
詳しくは `LICENSE` ファイルの規約を確認してください。
