<?php
/**
 * @file
 * Obiba Agate file
 */

function obiba_agate_password_form($form, &$form_state) {
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

function obiba_agate_password_form_process() {
  $module_path_ = drupal_get_path('module', 'obiba_agate');
  drupal_add_js($module_path_ . '/js/ang/app-confirm-register.js', array('type' => 'file', 'scope' => 'footer'));
  if(strstr(current_path(), 'confirm')){
    drupal_set_title('Password registration confirmation');
  return theme('obiba_agate_user-form-password-confirm',
    array(
      'form_password_registration' => drupal_get_form('obiba_agate_password_form')
    ));
  }
  elseif(strstr(current_path(), 'reset')){
    drupal_set_title('Password Reset');
    return theme('obiba_agate_user-form-password-reset',
      array(
        'form_password_registration' => drupal_get_form('obiba_agate_password_form')
      ));
  }
}

function obiba_agate_send_password_user($resource='confirm') {
  $parameters = drupal_get_query_parameters();
  if (!empty($parameters)) {
      $agate_client = new AgateClient();
      $response = $agate_client->send_password(
        array(
          'key' => $parameters['key'],
          'password' => $parameters['password']
        ), $resource
      );
    if (empty($response['code'])) {
      drupal_set_message('You can now log in.');
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

