# php-min

A minimal RESTful middleware framework for PHP 5.3+.

```
# Install using Composer
composer require mariuslundgard/php-min
```

# Simple example

In an ```index.php``` file:

``` php
<?php
require 'vendor/autoload.php';

(new Min\Http\Application())
    ->get('/', function ($req, $res) {
        $res->body[] = '<h1>Hello, world!</h1>';
    })
    ->process()->end();
```

Add an ```.htaccess``` file in the same directory:

```
<IfModule mod_rewrite.c>
  RewriteEngine on

  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d

  RewriteRule . index\.php [L]
</IfModule>
```

Then visit the application URL in the browser.
