# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.1] - 2024-03-01
### Changed
- re-enable PHPUnit Symfony Deprecation notice
- remove usage of deprecated ContainerAware class
- replace deprecated watchdog_exception by Drupal::logger

### Fixed
- fix issue #3329340 - PHPUnit deprecated prophecy integration
- fix phpcs use statements should be sorted alphabetically
- add phpstan.neon file

### Added
- add Drupal GitlabCI
- add coverage of Drupal 10.2.x
- add coverage of Drupal 11.0-dev

### Removed
- drop tests support on Drupal <= 9.4

## [3.0.0] - 2022-11-18
### Added
- add Drush 10 on Drupal CI
- add Drush 10 as dev dependency
- add official support of drupal 9.5 & 10.0

### Changed
- Bump major release number in order of using Drupal new semver system

### Removed
- drop support of drupal below 9.3.x

### Fixed
- fix issue #3318022 by wengerk: DrupalCI - Error: Class "Drush\Commands\DrushCommands
- fix deprecation file_save_data for Drupal 10 compatibilities
- fix deprecation drupal_get_path for Drupal 10 compatibilities
- fix TwigExtensions deprecation for Drupal 10 compatibilities
- fix deprecation of theme classy for Drupal 10 compatibilities

## [2.2.0] - 2022-10-21
### Added
- update changelog form to follow keepachangelog format
- add dependabot for Github Action dependency
- add support Drupal 9.5
- add upgrade-status check

### Removed
- disable symfony deprecations helper on phpunit
- remove satackey/action-docker-layer-caching on Github Actions
- drop support Drupal 8.8
- drop support of drupal below 9.0

## [2.1.0] - (2022-05-10)
### Added
- fix generated File on Pull operation as Permanent

## [2.0.0] - (2021-11-29)
### Added
- fix phpunit 9+ deprecation - assertInternalType
- fix Issue #3095292 by wengerk: drush loco_translate:pull can't specify the --status parameters
- fix Issue #3156326 by wengerk: Translate folder does not exists when pull
- fix Issue #3177286 by wengerk: Call to a member function getFileUri() on bool when "translations://" or "locale.settings.translation.path" not set

## [2.0.0-beta1] - (2020-07-02)
### Added
- replace drupal_ti by wengerk/docker-drupal-for-contrib
- ensure compatibility with Drupal 8.8+
- ensure compatibility with Drupal 9

## [1.0.0] - (2020-07-02)
### Added
- stable release from 8.x to 8.7.x

## [1.0.0-beta1] - (2019-10-21)
### Added
- add push automation via Cron
- add pull automation via Cron
- cover and improve the utility storage of last pull/push getter/setter
- change how the loco:pull command works by forcing the language as parameter instead of option
- add the 'index' on push/pull as optionnal
- update utility get last push/pull with default to zero instead of null
- update loco_translate schema with langcodes sequence type
- add form settings validations on push/pull automations & removed unecessary gettext

## [1.0.0-alpha1] - (2019-06-24)
### Added
- first alpha release with basic features
- push command from .po file to Loco SaSS
- pull command from Loco SaSS to Drupal database
- basic form settings with Loco SaSS API credentials

[Unreleased]: https://github.com/antistatique/drupal-loco-translate/compare/3.0.1...HEAD
[3.0.1]: https://github.com/antistatique/drupal-loco-translate/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-2.2...3.0.0
[2.2.0]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-2.1...8.x-2.2
[2.1.0]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-2.0...8.x-2.1
[2.0.0]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-2.0-beta1...8.x-2.0
[2.0.0-beta1]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-1.0...8.x-2.0-beta1
[1.0.0]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-1.0-beta1...8.x-1.0
[1.0.0-beta1]: https://github.com/antistatique/drupal-loco-translate/compare/8.x-1.0-alpha1...8.x-1.0-beta1
[1.0.0-alpha1]: https://github.com/antistatique/drupal-loco-translate/releases/tag/8.x-1.0-alpha1
