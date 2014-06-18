<?php

/**
 * @file
 * Obiba Auth module settings UI.
 */

/**
 * Provides settings pages.
 */
function obiba_auth_admin_settings() {
  $form['server'] = array(
    '#type' => 'fieldset',
    '#title' => t('OBiBa authentication server (Agate)'),
    '#collapsible' => FALSE,
  );

  $form['server']['agate_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Agate address'),
    '#required' => TRUE,
    '#default_value' => variable_get_value('agate_url'),
    '#maxlength' => 255,
    '#description' => t('URL of the Agate server. Note that cross-domain is not supported. Example: https://agate.example.org:8444'),
  );

  $form['server']['agate_application_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Application name'),
    '#required' => TRUE,
    '#default_value' => variable_get_value('agate_application_name'),
    '#maxlength' => 255,
    '#description' => t('The name under which the Drupal server is known by Agate.'),
  );

  $form['server']['agate_application_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Application key'),
    '#required' => TRUE,
    '#default_value' => variable_get_value('agate_application_key'),
    '#maxlength' => 255,
    '#description' => t('The key used by the Drupal server when issuing requests to Agate.'),
  );

  $form['account'] = array(
    '#type' => 'fieldset',
    '#title' => t('User accounts'),
    '#collapsible' => FALSE,
  );

  $form['account']['obiba_user_register'] = array(
    '#markup' => t('A Drupal account is always created the first time a OBiBa user logs into the site. Specific Drupal roles can be applied on this account.'),
  );

  // Taken from Drupal's User module.
  $roles = array_map('check_plain', user_roles(TRUE));
  $checkbox_authenticated = array(
    '#type' => 'checkbox',
    '#title' => $roles[DRUPAL_AUTHENTICATED_RID],
    '#default_value' => TRUE,
    '#disabled' => TRUE,
  );
  unset($roles[DRUPAL_AUTHENTICATED_RID]);
  $form['account']['obiba_auto_assigned_role'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles'),
    '#description' => t('The selected roles will be automatically assigned to each OBiBa user on login. Use this to automatically give OBiBa users additional privileges or to identify OBiBa users to other modules.'),
    '#default_value' => variable_get_value('obiba_auto_assigned_role', array()),
    '#options' => $roles,
    '#access' => user_access('administer permissions'),
    DRUPAL_AUTHENTICATED_RID => $checkbox_authenticated,
  );


  return system_settings_form($form);
}
