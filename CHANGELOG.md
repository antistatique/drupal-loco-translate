CHANGELOG
---------

## 8.x-2.0-beta1 (2020-07-02)
 - replace drupal_ti by wengerk/docker-drupal-for-contrib
 - ensure compatibility with Drupal 8.8+
 - ensure compatibility with Drupal 9

## 8.x-1.0 (2020-07-02)
 - stable release from 8.x to 8.7.x

## 8.x-1.0-beta1 (2019-10-21)
 - add push automation via Cron
 - add pull automation via Cron
 - cover and improve the utility storage of last pull/push getter/setter
 - change how the loco:pull command works by forcing the language as parameter instead of option
 - add the 'index' on push/pull as optionnal
 - update utility get last push/pull with default to zero instead of null
 - update loco_translate schema with langcodes sequence type
 - add form settings validations on push/pull automations & removed unecessary gettext

## 8.x-1.0-alpha1 (2019-06-24)
 - first alpha release with basic features
 - push command from .po file to Loco SaSS
 - pull command from Loco SaSS to Drupal database 
 - basic form settings with Loco SaSS API credentials
