# Loco Translate

Loco Translate provides a normalised way to collect & gather internationalisation assets & translations into & from Loco.
Ensure an enhanced Developer Experience (DX) when dealing with translations & multilingual websites.

|       Travis-CI        |        Style-CI         |        Downloads        |         Releases         |
|:----------------------:|:-----------------------:|:-----------------------:|:------------------------:|

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

## Dependencies

The Drupal 8 version of Loco Translate requires a [Loco](https://localise.biz) connection.

## Supporting organizations

This project is sponsored by Antistatique. We are a Swiss Web Agency,
Visit us at [www.antistatique.net](https://www.antistatique.net) or
[Contact us](mailto:info@antistatique.net).

## Getting Started

## Getting Started

We highly recommend you to install the module using `composer`.

  ```bash
  composer require drupal/loco-translate
  ```

You can also install it using the `drush` or `drupal console` cli.

  ```bash
  drush dl loco-translate
  ```

  ```bash
  drupal module:install loco-translate
  ```