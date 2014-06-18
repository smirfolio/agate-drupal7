<?php
/**
 * @file
 * Obiba Auth file
 */

/**
 * Implements hook_variable_info().
 */
function obiba_auth_variable_info($options) {
  $variable['agate_url'] = array(
    'title' => t('Agate address'),
    'description' => t('URL of the Agate server. Note that cross-domain is not supported. Example: https://agate.example.org:8444'),
    'type' => 'string',
    'default' => 'https://localhost:8444',
  );
  $variable['agate_application_name'] = array(
    'title' => t('Application name'),
    'description' => t('The name under which the Drupal server is known by Agate.'),
    'type' => 'string',
    'default' => 'drupal',
  );
  $variable['agate_application_key'] = array(
    'title' => t('Application key'),
    'description' => t('The key used by the Drupal server when issuing requests to Agate.'),
    'type' => 'string',
    'default' => 'changeit',
  );
  $variable['obiba_auto_assigned_role'] = array(
    'title' => t('Roles'),
    'description' => t('The selected roles will be automatically assigned to each OBiBa user on login. Use this to automatically give OBiBa users additional privileges or to identify OBiBa users to other modules.'),
    'type' => 'options',
  );
  return $variable;
}
