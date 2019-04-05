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
 * Provides settings pages.
 */
function obiba_agate_admin_settings() {

  $form['server'] = array(
    '#type' => 'fieldset',
    '#title' => t('OBiBa central authentication server (Agate)'),
    '#collapsible' => FALSE,
  );

  $info = variable_get_info('agate_url');
  $form['server']['agate_url'] = array(
    '#type' => 'textfield',
    '#title' => $info['title'],
    '#required' => TRUE,
    '#default_value' => variable_get_value('agate_url'),
    '#maxlength' => 255,
    '#description' => $info['description'],
  );

  $info = variable_get_info('agate_application_name');
  $form['server']['agate_application_name'] = array(
    '#type' => 'textfield',
    '#title' => $info['title'],
    '#required' => TRUE,
    '#default_value' => variable_get_value('agate_application_name'),
    '#maxlength' => 255,
    '#description' => $info['description'],
  );

  $info = variable_get_info('agate_application_key');
  $form['server']['agate_application_key'] = array(
    '#type' => 'password',
    '#title' => $info['title'],
    '#required' => TRUE,
    '#default_value' => variable_get_value('agate_application_key'),
    '#maxlength' => 255,
    '#description' => $info['description'],
  );

  $info = variable_get_info('obiba_logout_redirection_page');
  $form['server']['obiba_logout_redirection_page'] = array(
    '#type' => 'textfield',
    '#title' => $info['title'],
    '#required' => TRUE,
    '#default_value' => variable_get_value('obiba_logout_redirection_page'),
    '#maxlength' => 255,
    '#description' => $info['description'],
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
  $info = variable_get_info('obiba_auto_assigned_role');
  $form['account']['obiba_auto_assigned_role'] = array(
    '#type' => 'checkboxes',
    '#title' => $info['title'],
    '#description' => $info['description'],
    '#default_value' => variable_get_value('obiba_auto_assigned_role', array()),
    '#options' => $roles,
    '#access' => user_access('administer permissions'),
    DRUPAL_AUTHENTICATED_RID => $checkbox_authenticated,
  );

}

function obiba_agate_fields_admin_settings($form){
  // Export users configuration
  $form['fields_users'] = array(
    '#type' => 'fieldset',
    '#title' => t('The synchronization users Drupal/Agate configurations'),
    '#collapsible' => FALSE,
  );
  $sanitized_agate_field = array();
  $agate_fields = obiba_agate_configuration();
  unset($agate_fields['locale']);
  unset($agate_fields['username']);
  unset($agate_fields['email']);
  $info = variable_get_info('obiba_agate_user_fields_mapping');
  $form_obiba_agate_field_configuration_map = array(
    '#prefix' => _obiba_agate_field_configuration($info['title'], $info['description']),
    '#tree' => TRUE,
    '#theme' => 'obiba_agate_table_widget_component',
    '#title' => $info['title'],
    '#required' => FALSE,
    '#description' => $info['description'],
  );

  foreach (array_keys($agate_fields) as $field) {
    $sanitized_agate_field[$field] = $field;
  }
  $wrapped_obiba_agate_field_configuration_map = _obiba_agate_configuration_get_draggable_blocks_form($form_obiba_agate_field_configuration_map, $sanitized_agate_field, 'obiba_agate_user_fields_mapping');
  $form['fields_users']['obiba_agate_user_fields_mapping'] = array_merge($form_obiba_agate_field_configuration_map, $wrapped_obiba_agate_field_configuration_map);

  return system_settings_form($form);
}
/**
 * Draggable widget form implementation.
 *
 * @param $draggable_form
 * @param $draggable_items
 * @param $variable_key
 * @return mixed
 */
function _obiba_agate_configuration_get_draggable_blocks_form($draggable_form, $draggable_items, $variable_key) {
  $default_value = variable_get_value($variable_key);
  $items = $default_value ? $default_value : $draggable_items;
  foreach ($items as $key_aggregation => $aggregation) {
    switch ($key_aggregation){
      case 'lastname':
        $key_aggregation = 'lastName';
        break;
      case 'firstname':
        $key_aggregation = 'firstName';
        break;
    }
    $draggable_form[$key_aggregation] =
      array(
        'agate_value' => array(
          '#type' => 'hidden',
          '#title_display' => 'invisible',
          '#default_value' => $key_aggregation,
        ),
        'drupal_value' => array(
          '#type' => 'textfield',
          '#title_display' => 'invisible',
          '#default_value' => !empty($default_value[$key_aggregation]['drupal_value']) ? $default_value[$key_aggregation]['drupal_value'] : '',
        ),
        'description' => array(
          '#markup' => $key_aggregation,
        ),
        'enable' => array(
          '#type' => 'checkbox',
          '#default_value' => !empty($default_value[$key_aggregation]['enable']) ? $default_value[$key_aggregation]['enable'] : 0,
          '#title_display' => 'invisible',
        )
      );
  }
  return $draggable_form;
}


/**
 * Construct the title and description of a draggable table field
 *
 * @param $title
 * @param $description
 * @return string
 */
function _obiba_agate_field_configuration($title, $description) {
  return <<<EOT
    <label class="control-label">$title</label>
    <div class="help-block">$description</div>
EOT;
}

function obiba_agate_configuration(){
  $agate_client = new ObibaAgateClient();
  return $agate_client->getConfigFormJoin()['schema']['properties'];
}
