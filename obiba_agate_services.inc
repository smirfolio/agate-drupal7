<?php
/**
 * @file
 * Obiba Agate Module.
 *
 * Copyright (c) 2015 OBiBa. All rights reserved.
 * This program and the accompanying materials
 * are made available under the terms of the GNU Public License v3.0.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Register action (Ajax resource).
 */
function obiba_agate_register_user() {
  $agate = new ObibaAgateClient();
  $response = $agate->addAgateUser(drupal_get_query_parameters($_POST));
    drupal_json_output($response);
}

/**
 * Profile User (Ajax resource).
 */
function obiba_agate_user_profile() {
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
      $agate = new ObibaAgateUserProfile();
      $ticket = ObibaAgateClient::hasTicket();
      if (!empty($ticket)) {
        $profile = $agate->getUserProfile();
        global $user;
        $user_profile = user_view(user_load($user->uid));
        $user_view = drupal_render($user_profile);
        drupal_json_output(array(
          'userProfile' => $profile,
          'drupalUserDisplay' => $user_view,
        ));
      }
      break;

    case 'PUT':
      $agate = new ObibaAgateUserProfile();
      $ticket = ObibaAgateClient::hasTicket();
      if (!empty($ticket)) {
        $profile = $agate->updateUserProfile(file_get_contents('php://input'));
        if (empty($profile['errorServer'])) {
          drupal_json_output(array('locationRedirection' => 'view'));
        }
        else {
          drupal_json_output(array_merge(array('locationRedirection' => 'edit'), $profile));
        }
      }
      // Update email Drupal user. add last name first name in drupal data field
      obiba_agate_save_drupal_user();
      break;
  }
}

/**
 * Function call Back to send the new password to server.
 */
function obiba_agate_user_profile_update_password() {
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'PUT':
      $agate = new ObibaAgateUserProfile();
      $user_password = drupal_json_decode(file_get_contents('php://input'));
      $response = $agate->updateUserPassword($user_password['currentPassword'], $user_password['newPassword']);
      if (!empty($response['errorServer'])) {
        drupal_json_output($response);
      }
      break;
  }
}

function obiba_agate_get_providers(){
 $agate =  new ObibaAgateClient();
  drupal_json_output($agate->getProviders());
}
