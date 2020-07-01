# Developing on Loco Translate

* Issues should be filed at
https://www.drupal.org/project/issues/loco_translate
* Pull requests can be made against
https://github.com/antistatique/drupal-loco-transalte/pulls

## 📦 Repositories

Drupal repo

  ```bash
  git remote add origin git@git.drupal.org:project/loco_translate.git
  ```

Github repo

  ```bash
  git remote add github git@github.com:antistatique/drupal-loco-translate.git
  ```

## 🔧 Prerequisites

First of all, you will need to have the following tools installed
globally on your environment:

  * Latest dev release of Drupal 8.x.

## 🏆 Tests

Loco Translate us PHPUnit to run test coverage.

*Run Unit tests*

  ```bash
  # You must be on the drupal-root folder - usually /web.
  cd web
  ../vendor/bin/phpunit -c core \
  --group loco_translate_unit
  ```

*Run Functional tests*

For some tests you need a working database connection and for browser tests
your Drupal installation needs to be reachable via a web server.
Copy the phpunit config file:

  ```bash
  cd core
  cp phpunit.xml.dist phpunit.xml
  ```

You must provide `SIMPLETEST_BASE_URL`, Eg. `http://localhost`.
You must provide `SIMPLETEST_DB`,
Eg. `sqlite://localhost/build/loco_translate.sqlite`.

  ```bash
  # You must be on the drupal-root folder - usually /web.
  cd web
  SIMPLETEST_DB="sqlite://localhost//tmp/loco_translate.sqlite" \
  SIMPLETEST_BASE_URL='http://d8.test' \
  ../vendor/bin/phpunit -c core \
  --group loco_translate_functionnal
  ```

Debug using

  ```bash
  # You must be on the drupal-root folder - usually /web.
  cd web
  SIMPLETEST_DB="sqlite://localhost//tmp/loco_translate.sqlite" \
  SIMPLETEST_BASE_URL='http://d8.test' \
  ../vendor/bin/phpunit -c core \
  --group loco_translate \
  --printer="\Drupal\Tests\Listeners\HtmlOutputPrinter" --stop-on-error
  ```

You must provide a `BROWSERTEST_OUTPUT_DIRECTORY`,
Eg. `/path/to/webroot/sites/simpletest/browser_output`.

## 🚔 Check Drupal coding standards & Drupal best practices

You need to run composer before using PHPCS. Then register the Drupal
and DrupalPractice Standard with PHPCS:
`./vendor/bin/phpcs --config-set installed_paths \
`pwd`/vendor/drupal/coder/coder_sniffer`

### Command Line Usage

Check Drupal coding standards:

  ```bash
  ./vendor/bin/phpcs --standard=Drupal --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info,md \
  --ignore=*/vendor/*,*/node_modules/*,*/scripts/* ./
  ```

Check Drupal best practices:

  ```bash
  ./vendor/bin/phpcs --standard=DrupalPractice --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info,md \
  --ignore=*/vendor/*,*/node_modules/*,*/scripts/* ./
  ```

Automatically fix coding standards

  ```bash
  ./vendor/bin/phpcbf --standard=Drupal --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info \
  --ignore=*/vendor/*,*/node_modules/*,*/scripts/* ./
  ```

### Improve global code quality using PHPCPD & PHPMD

Add requirements if necessary using `composer`:

  ```bash
  composer require --dev 'phpmd/phpmd:^2.6' 'sebastian/phpcpd:^3.0'
  ```

Detect overcomplicated expressions & Unused parameters, methods, properties

  ```bash
  ./vendor/bin/phpmd ./ text ./phpmd.xml --suffixes \
  php,module,inc,install,test,profile,theme,css,info,txt \
  --exclude vendor,scripts,tests
  ```

Copy/Paste Detector

  ```bash
  ./vendor/bin/phpcpd ./ \
  --names=*.php,*.module,*.inc,*.install,*.test,*.profile,*.theme,*.css,*.info,*.txt \
  --names-exclude=*.md,*.info.yml --progress --ansi \
  --exclude=scripts --exclude=vendor --exclude=tests
  ```

### Enforce code standards with git hooks

Maintaining code quality by adding the custom post-commit hook to yours.

  ```bash
  cat ./scripts/hooks/post-commit >> ./.git/hooks/post-commit
  ```
