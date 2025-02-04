#!/usr/bin/env just --justfile

_default:
    just --list --unsorted

# Install all dependencies necesesary to run Annotated Container tools
install: _install_ac

_install_ac:
    composer install

_install_labrador_cs:
    cd tools/labrador-cs
    composer install

_install_phpunit:
    cd tools/phpunit
    composer install

_install_psalm:
    cd tools/psalm
    composer install

# Run unit tests
test:
    @XDEBUG_MODE=coverage ./tools/phpunit/vendor/bin/phpunit

# Run static analysis checks on src and test
static-analysis:
    @./tools/psalm/vendor/bin/psalm

# Run code-linting tools on src and test
code-lint:
    @./tools/labrador-cs/vendor/bin/phpcs -p --standard=./tools/labrador-cs/vendor/cspray/labrador-coding-standard/ruleset.xml --exclude=Generic.Files.LineLength src test
