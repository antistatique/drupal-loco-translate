# Loco Translate

Loco Translate provides a normalised way to collect & gather internationalisation assets & translations into & from Loco.
Ensure an enhanced Developer Experience (DX) when dealing with translations & multilingual websites.

|       Travis-CI        |        Style-CI         |        Downloads        |         Releases         |
|:----------------------:|:-----------------------:|:-----------------------:|:------------------------:|
| [![Travis](https://travis-ci.org/antistatique/drupal-loco-translate.svg?branch=8.x-1.x)](https://travis-ci.org/antistatique/drupal-loco-translate) |  [![StyleCI](https://styleci.io/repos/85471768/shield)](https://styleci.io/repos/190755687) | [![Downloads](https://img.shields.io/badge/downloads-8.x--1.0-green.svg?style=flat-square)](https://ftp.drupal.org/files/projects/loco_translate-8.x-1.0.tar.gz) | [![Latest Stable Version](https://img.shields.io/badge/release-v1.0-blue.svg?style=flat-square)](https://www.drupal.org/project/loco_translate/releases) |


## You need Loco Translate if

* You want to use Loco as your Master Sass translation platform
* You want to push _automatically_ a .po file from your Drupal instance to Loco,
* You want your Drupal environment to be updated automatically or manually from your Loco Sass,
* You want to use a module based on the Core [Translation API](https://www.drupal.org/docs/8/api/translation-api/overview),
* You want to deal with translation outside of Drupal UI,

Loco Translate can do a lot more than that, but those are some of the obvious uses of this module.

## Features

* Offers a Dashboard to overview translations progress on Loco,
* Provide a utility script to push assets keys from Drupal to Loco,
* Expose a Drush command to pull assets & translations from Loco to Drupal,

Still under active development, checkout our [Roadmap](./ROADMAP.md).

_Everything contained in the ROADMAP document is in draft form and subject to change at any time and provided for information purposes only_

## Standard usage scenario

TBD

## Versions

Loco Translate is only available for Drupal 8 !
The module is ready to be used in Drupal 8, there are no known issues.

This version should work with all Drupal 8 releases using Drush 9+,
and it is always recommended keeping Drupal core installations up to date.

## Backerymails versions

The version `8.x-1.x` is not compatible with Drupal `8.8.x`.
Drupal `8.8.x` brings some breaking change with tests and so you
must upgrade to `8.x-2.x` version of **Loco Translate**.

## Which version should I use?

|Drupal Core|Loco Translate|
|:---------:|:------------:|
|8.7.x      |1.x           |
|8.8.x      |2.x           |
|9.x        |2.x           |

## Dependencies

This module relies on [Loco API](https://localise.biz) & the [Loco Library](https://symfony.com/doc/current/components/finder.html).

* `Loco Library` is an external PHP library to communicate with the Loco API.

We assume, that you have installed `loco/loco` using Composer.

## Supporting organizations

This project is sponsored by Antistatique. We are a Swiss Web Agency,
Visit us at [www.antistatique.net](https://www.antistatique.net) or
[Contact us](mailto:info@antistatique.net).

## Getting Started

We highly recommend you to install the module using `composer`.

  ```bash
  composer require drupal/loco_translate
  ```

You can also install it using the `drush` or `drupal console` cli.

  ```bash
  drush dl loco_translate
  ```

  ```bash
  drupal module:install loco_translate
  ```

Configure your API Keys - as required by Loco - by adding the following code in your `settings.php`

  ```php
  /**
   * Loco Translate Export Key.
   *
   * @var string
  */
  $config['loco_translate.settings']['api']['readonly_key'] = 'YOUR-KEY-HERE';

  /**
   * Loco Translate Full Access Key.
   *
   * @var string
  */
  $config['loco_translate.settings']['api']['fullaccess_key'] = 'YOUR-KEY-HERE';
  ```

## Exposed Drush Commands

This module is shipped with drush commands to assist you in your workflow.

### Push Command

The Push command will create new translations keys (a.k.a assets) into your Loco SaSS - from a reference .po files which should be in your Drupal or Local environmment:

  ```bash
  drush loco:push ./translations/fr.po --language="fr"
  ```

### Pull Command

The Pull command will fetch keys & translations from your Loco SaSS into Drupal:

  ```bash
  drush loco:pull fr
  ```
