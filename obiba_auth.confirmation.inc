<?php
/**
 * @file
 * Obiba Auth file
 */

function obiba_auth_password_form($form, &$form_state) {
  $form['#action'] = '';
  $form['name']['password'] = array(
    '#type' => 'password',
    '#title' => t('Enter your password'),
    '#required' => TRUE,
    '#default_value' => "",
    '#description' => "Please enter your password",
    '#size' => 20,
    '#maxlength' => 20,
    '#attributes' => array(
      'id' => 'type-password'
    )
  );
  $form['name']['repassword'] = array(
    '#type' => 'password',
    '#title' => t('Re-type your password'),
    '#required' => TRUE,
    '#default_value' => "",
    '#description' => "Please retype your password",
    '#size' => 20,
    '#maxlength' => 20,
    '#attributes' => array(
      'id' => 'verif-password'
    )

  );
  $form['name']['confirmed_password'] = array(
    '#type' => 'hidden',
    '#required' => TRUE,
    '#default_value' => "",
    '#attributes' => array(
      'id' => 'password'
    )
  );
  $form['name']['key'] = array(
    '#type' => 'hidden',
    '#required' => TRUE,
    '#default_value' => "",
    '#attributes' => array(
      'id' => 'key',
    )
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Submit',
  );

  return $form;
}

function obiba_auth_confirm_registration_user() {
  $module_path_ = drupal_get_path('module', 'obiba_auth');
  drupal_add_js($module_path_ . '/js/ang/app-confirm-register.js', array('type' => 'file', 'scope' => 'footer'));

  return theme('obiba_auth_user-confirm-registration-password',
    array(
      'form_password_registration' => drupal_get_form('obiba_auth_password_form')
    ));
}

function obiba_auth_send_confirmation_registration_user() {
  $parameters = drupal_get_query_parameters();
  if (!empty($parameters)) {
    $agate_client = new AgateClient();
    $response = $agate_client->confirm_registration(
      array(
        'key' => $parameters['key'],
        'password' => $parameters['password']
      ),
      $parameters['request_type']
    );
    if (empty($response['code'])) {
      drupal_set_message('You can login');
      drupal_json_output($response);
      die;
    }
    else {
      drupal_add_http_header('Status', '400 Bad Request');
      drupal_set_message(t('Server Error : @message', array('@message' => $response['message'])), 'error');
    }
  }
  drupal_json_output(array());
}
