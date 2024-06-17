#!/usr/bin/env just --justfile

_default:
    just --list --unsorted

# Install all dependencies necesesary to run Annotated Container tools
install: _install_labrador_cs _install_phpunit _install_psalm _install_ac

_install_ac:
    composer install

_install_labrador_cs:
    cd tools/labrador-cs && composer install

_install_phpunit:
    cd tools/phpunit && composer install

_install_psalm:
    cd tools/psalm && composer install

# Run unit tests
test *FLAGS:
    @XDEBUG_MODE=coverage ./tools/phpunit/vendor/bin/phpunit {{FLAGS}}

# Run static analysis checks on src and test
static-analysis *FLAGS:
    @./tools/psalm/vendor/bin/psalm --version
    @./tools/psalm/vendor/bin/psalm {{FLAGS}}

# Set the baseline of known issues to be used during static analysis
static-analysis-set-baseline:
    @./tools/psalm/vendor/bin/psalm --set-baseline=known-issues.xml --no-cache

# Update the baseline to _remove_ fixed issues. If new issues are to be added please use static-analysis-set-baseline
static-analysis-update-baseline *FLAGS:
    @./tools/psalm/vendor/bin/psalm --update-baseline --no-cache {{FLAGS}}

static-analysis-clear-cache:
    @./tools/psalm/vendor/bin/psalm --clear-cache

# Run code-linting tools on src and test
code-lint:
    @./tools/labrador-cs/vendor/bin/phpcs --version
    @./tools/labrador-cs/vendor/bin/phpcs -p --colors --standard=./tools/labrador-cs/vendor/cspray/labrador-coding-standard/ruleset.xml --exclude=Generic.Files.LineLength src test

# Resolve fixable code-linting issues
code-lint-fix:
    @./tools/labrador-cs/vendor/bin/phpcbf -p --standard=./tools/labrador-cs/vendor/cspray/labrador-coding-standard/ruleset.xml --exclude=Generic.Files.LineLength src test

# Run all CI checks. ALL checks will run, regardless of failures
ci-check:
    -@just test
    -@just static-analysis
    @echo ""
    -@just code-lint

# Generate a new Architectural Decision Record document
generate-adr:
    @./tools/adr/bin/generate-adr