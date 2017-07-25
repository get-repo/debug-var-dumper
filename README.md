# Dev Var Debug Dumper
PHP function to pretty print and debug anything


**************
# Installation
```bash
composer config repositories.get-repo/debug-var-dumper git https://github.com/get-repo/debug-var-dumper
composer require get-repo/debug-var-dumper
```


**************
# Details
p() — Pretty print any variable from any context
--------------------------------------
Output anything in cli or html mode
```php
mixed p (
    mixed $var,
    bool $exit = true,
    bool $output = true,
    bool $htmlentities = false,
    int $cpt = 0
)
```

l() — Pretty print in any context (cli, web...)
--------------------------------------
Output a message with colors
```php
string l (
    string $message,
    mixed $color = null,
    bool $return = false
)
```

