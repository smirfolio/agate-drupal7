<?php
/**
 * @file
 * Contains Drupal\obiba_agate\Form\MessagesForm.
 */
namespace Drupal\obiba_agate\Form;

use Drupal\obiba_agate\ObibaAgate;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

class AgateServerSettings extends ConfigFormBase {



  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
        ObibaAgate::AGATE_SERVER_SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return ObibaAgate::OBIBA_AGATE_FORM_SERVER_SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(ObibaAgate::AGATE_SERVER_SETTINGS);

    $form['server'] = [
        '#type' => 'details',
        '#title' => $this->t('OBiBa Agate authentication server'),
        '#open' => TRUE,
    ];

    $form['server'][ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Agate address'),
      '#required' => TRUE,
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'url'),
      '#maxlength' => 255,
      '#description' => $this->t('URL of the Agate server. Note that cross-domain is not supported. Example: https://agate.example.org:8444'),
    ];

    $form['server'][ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'application_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application name'),
      '#required' => TRUE,
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_name'),
      '#maxlength' => 255,
      '#description' => $this->t('The name under which the Drupal server is known by Agate.'),
    ];

    $form['server'][ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'application_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Application key'),
      '#required' => FALSE,
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_key') ?
          $config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_key') : 'changeit',
      '#maxlength' => 255,
      '#description' => $this->t('The key used by the Drupal server when issuing requests to Agate.'),
    ];

    $form['server'][ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'logout_redirection_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Logout redirection page'),
      '#required' => TRUE,
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'logout_redirection_page'),
      '#maxlength' => 255,
      '#description' => $this->t('The Page to redirect to after logout. (Default : <current> to redirect current page , we can use <front> to redirect to Home page)'),
    ];

    $form['account'] = [
        '#type' => 'details',
        '#title' => $this->t('User accounts'),
        '#open' => TRUE,
        '#description' => $this->t('A Drupal account is always created the first time a OBiBa user logs into the site. Specific Drupal roles can be applied on this account.'),
    ];

    // Taken from Drupal's User module.
    $roles = user_role_names();
    $checkbox_authenticated = [
      '#type' => 'checkbox',
      '#title' => $roles[AccountInterface::AUTHENTICATED_ROLE],
      '#default_value' => TRUE,
      '#disabled' => TRUE,
    ];
    unset($roles[AccountInterface::AUTHENTICATED_ROLE]);
    $default_check_values = $config->get(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'auto_assigned_role');
    $form['account'][ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'auto_assigned_role'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#description' => $this->t('The selected roles will be automatically assigned to each OBiBa user on login. Use this to automatically give OBiBa users additional privileges or to identify OBiBa users to other modules.\''),
      '#options' => $roles,
      '#default_value' => $default_check_values,
      '#access' => \Drupal::currentUser()->hasPermission('administer permissions'),
      AccountInterface::AUTHENTICATED_ROLE => $checkbox_authenticated,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      parent::submitForm($form, $form_state);
      $this->config(ObibaAgate::AGATE_SERVER_SETTINGS)
      ->set(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'url', $form_state->getValue(ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'url'))
      ->set(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_name', $form_state->getValue(ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'application_name'))
      ->set(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'application_key', $form_state->getValue(ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'application_key'))
      ->set(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'logout_redirection_page', $form_state->getValue(ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'logout_redirection_page'))
      ->set(ObibaAgate::CONFIG_PREFIX_SERVER . '.' . 'auto_assigned_role', $form_state->getValue(ObibaAgate::CONFIG_PREFIX_SERVER . '_' . 'auto_assigned_role'))
      ->save();
  }


}