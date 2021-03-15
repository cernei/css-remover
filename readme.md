### Ununsed css remover

This tool takes chrome coverage report gathered on a single CSS file. 
For projects that use one CSS file on build, and a lot of source CSS files.
Takes `@media` queries into account

To set up the script go to `config.php` and read comments and adjust each variable if needed.

Then run:
```shell
php index.php
```

### Extractor
Get all covered css taking into account `@media` queries

```shell
php extractor.php
```
