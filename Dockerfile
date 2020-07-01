ARG BASE_IMAGE_TAG=8.9
FROM wengerk/drupal-for-contrib:${BASE_IMAGE_TAG}

ENV COMPOSER_MEMORY_LIMIT=-1

# Install loco/loco as required by the module
RUN composer require loco/loco:^2.0.7
