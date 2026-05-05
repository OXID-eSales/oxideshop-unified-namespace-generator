# Unified Namespace Generator

An OXID eShop related tool to generate class files in the so called _unified namespace_.
This tools implements a composer plugin, but can also be executed as a
  standalone script like this
 ```
 vendor/bin/oe-eshop-unified_namespace_generator
 ```

See also [Documentation on docs.oxid-esales.com](https://docs.oxid-esales.com/developer/en/latest/system_architecture/unified_namespace/unified_namespace_generator.html#unified-namespace-generator)


## Development

### Running tests

Component tests can be executed with the OXID eShop's PHPUnit runner:
```bash
vendor/bin/phpunit vendor/oxid-esales/oxideshop-unified-namespace-generator
```

you might need to extend the eShop's root composer `autoload-dev` configuration and run `dump-autoload` command:

```json filename="composer.json"
    "autoload-dev": {
        "psr-4": {
            "OxidEsales\\UnifiedNameSpaceGenerator\\Tests\\": "./vendor/oxid-esales/oxideshop-unified-namespace-generator/tests"
        }
    }
```

```bash
composer dump-autoload
```
to activate autoloading for the component's test classes.

## Bugs and Issues

If you experience any bugs or issues, please report them in the section **OXID eShop (all versions)** of https://bugs.oxid-esales.com.
