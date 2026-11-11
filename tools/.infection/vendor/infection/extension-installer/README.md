[![CI](https://github.com/infection/extension-installer/workflows/Tests/badge.svg)](https://github.com/infection/extension-installer/actions)
[![Coverage Status](https://coveralls.io/repos/github/infection/extension-installer/badge.svg?branch=master)](https://coveralls.io/github/infection/extension-installer?branch=master)

# Infection - Extensions Installer

Composer plugin for automatic registering of [Infection extensions](https://packagist.org/explore/?type=infection-extension).

## How to install extension

Extension installer is bundled together with Infection core. All you need to register a custom extension is just to install a composer package.

Extension will be registered _automatically_.

Infection Extension Installer listens `post-install-cmd` and `post-update-cmd` events and as soon as it finds an Infection extension, it automatically registers it in Infection.

```bash
composer require --dev infection/codecetion-adapter

Using version 1.0.0 for infection/codeception-adapter
Package operations: 1 installs, 0 updates, 0 removals
  - Installing infection/codeception-adapter (1.0.0): Downloading 100%

infection/extension-installer: Extensions installed
> infection/codeception-adapter: installed
``` 

## How to write an extension for Infection

Infection extension is a composer-based package. Basically it is a composer package which conforms to the following requirements:

* its type field is set to `infection-extension`
* it has `extra.infection.class` subkey in its `composer.json` that references a class that will be invoked in the Infection runtime.

Example:

```json
{
    "name": "infection/codeception-adapter",
    "type": "infection-extension",
    "extra": {
        "infection": {
            "class": "Infection\\TestFramework\\Codeception\\CodeceptionAdapterFactory"
        }
    }
}
```

### Supported extensions types

Currently, Infection supports only Test Framework extensions ([example](https://github.com/infection/codeception-adapter)).

### Available extensions

All Infection extensions can be [discovered on Packagist](https://packagist.org/explore/?type=infection-extension).

## Infection - Mutation Testing Framework

Please read documentation here: [infection.github.io](http://infection.github.io)

Twitter: [@infection_php](http://twitter.com/infection_php)
