.PHONY: ci test prerequisites

# Use any most recent PHP version
PHP=$(shell which php)

# Default parallelism
JOBS=$(shell nproc)

# PHP CS Fixer
PHP_CS_FIXER=vendor/bin/php-cs-fixer
PHP_CS_FIXER_ARGS=--diff --diff-format=udiff --verbose
export PHP_CS_FIXER_IGNORE_ENV=1

# PHPUnit
PHPUNIT=vendor/bin/phpunit
PHPUNIT_COVERAGE_CLOVER=--coverage-clover=build/logs/clover.xml
PHPUNIT_ARGS=--coverage-xml=build/logs/coverage-xml --log-junit=build/logs/junit.xml $(PHPUNIT_COVERAGE_CLOVER)

# PHPStan
PHPSTAN=vendor/bin/phpstan
PHPSTAN_ARGS=analyse src tests -c .phpstan.neon

# Psalm
PSALM=vendor/bin/psalm
PSALM_ARGS=--show-info=false

# Composer
COMPOSER=$(PHP) $(shell which composer)

# Infection
INFECTION=vendor/bin/infection
MIN_MSI=97
MIN_COVERED_MSI=100
INFECTION_ARGS=--min-msi=$(MIN_MSI) --min-covered-msi=$(MIN_COVERED_MSI) --threads=$(JOBS) --log-verbosity=none --no-interaction --no-progress --show-mutations

all: test

cs:
	$(PHP_CS_FIXER) fix $(PHP_CS_FIXER_ARGS) --dry-run
	LC_ALL=C sort -c -u .gitignore

phpstan:
	$(PHPSTAN) $(PHPSTAN_ARGS) --no-progress

psalm:
	$(PSALM) $(PSALM_ARGS) --no-cache --shepherd

static-analyze: phpstan psalm

test-unit:
	$(PHPUNIT) $(PHPUNIT_ARGS)

infection:
	$(INFECTION) $(INFECTION_ARGS)

##############################################################
# Development Workflow                                       #
##############################################################

test: phpunit analyze composer-validate

.PHONY: composer-validate
composer-validate: test-prerequisites
	$(COMPOSER) validate --strict

test-prerequisites: prerequisites composer.lock

phpunit: cs-fix
	$(PHPUNIT) $(PHPUNIT_ARGS) --verbose
	cp build/logs/junit.xml build/logs/phpunit.junit.xml
	$(PHP) $(INFECTION) $(INFECTION_ARGS)

analyze: cs-fix
	$(PHPSTAN) $(PHPSTAN_ARGS)
	$(PSALM) $(PSALM_ARGS)

cs-fix: test-prerequisites
	$(PHP_CS_FIXER) fix $(PHP_CS_FIXER_ARGS)
	LC_ALL=C sort -u .gitignore -o .gitignore

##############################################################
# Prerequisites Setup                                        #
##############################################################

# We need both vendor/autoload.php and composer.lock being up to date
.PHONY: prerequisites
prerequisites: build/cache vendor/autoload.php composer.lock infection.json.dist .phpstan.neon

# Do install if there's no 'vendor'
vendor/autoload.php:
	$(COMPOSER) install --prefer-dist
	test -d vendor/infection/infection/src/StreamWrapper/ && rm -fr vendor/infection/infection/src/StreamWrapper/ && $(COMPOSER) dump-autoload || true

# If composer.lock is older than `composer.json`, do update,
# and touch composer.lock because composer not always does that
composer.lock: composer.json
	$(COMPOSER) update && touch composer.lock

build/cache:
	mkdir -p build/cache
