<?php
/**
 * @file
 * Obiba Agate file
 */

/*
 * Register action (Ajax resource)
 *
 * */
function obiba_agate_register_user() {

  $form_attributes = obiba_agate_schema_form();

  //Secure the request by verify if the set of POST parameters are trusted by server
  $response_captcha = $_POST['reCaptchaResponse'];
  unset($_POST['reCaptchaResponse']);

  //perform the register request
  $_POST['reCaptchaResponse'] = $response_captcha;
  $agate = new AgateClient();
  $response = $agate->add_agate_user($_POST);
  if (!empty($response['code']) && $response['code'] == 200) {
    drupal_json_output($response);
  }
  else {
    if (!empty($response['codeError'])) {
      $message_serv_array = json_decode($response['message']);
      obiba_agate_register_user_response_error($response['codeError'],
        json_encode(array("message" => $message_serv_array->message)),
        $form_attributes);
    }
  }
}

/*
 * Profile User (Ajax resource)
 * */
function obiba_agate_user_profile() {
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
      $agate = new UserProfile();
      $ticket = AgateClient::hasTicket();
      if (!empty($ticket)) {
        $profile = $agate->get_user_profile();
        global $user;
        $user_profile = user_view(user_load($user->uid));
        $user_view = drupal_render($user_profile);
        drupal_json_output(array('userProfile' => $profile, 'drupalUserDisplay'=> $user_view));
      }
      break;

    case 'PUT':
      $agate = new UserProfile();
      $ticket = AgateClient::hasTicket();
      if (!empty($ticket)) {
        $profile = $agate->update_user_profile(file_get_contents('php://input'));
        if (empty($profile['errorServer'])) {
          drupal_json_output(array('locationRedirection' => 'view'));
        }
        else {
          drupal_json_output(array_merge(array('locationRedirection' => 'edit'), $profile));
        }
      }
      break;
  }
}

function obiba_agate_user_profile_update_password($id) {
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'PUT' :
      $agate = new UserProfile();
      $user_password = drupal_json_decode(file_get_contents('php://input'));
      $response = $agate->update_user_password($id, $user_password['currentPassword'], $user_password['newPassword']);
      if (!empty($response['errorServer'])) {
        drupal_json_output($response);
      }
      break;
  }
}
