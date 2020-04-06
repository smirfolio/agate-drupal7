<?php
/**
 * @file
 * Contains Drupal\obiba_agate\Form\MessagesForm.
 */
namespace Drupal\obiba_agate\Form;

use Drupal\obiba_agate\obibaAgate;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AgateServerPagesSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      obibaAgate::AGATE_SERVER_PAGES_SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return obibaAgate::OBIBA_AGATE_FORM_PAGES_SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config(obibaAgate::AGATE_SERVER_PAGES_SETTINGS);

    // Login page.
    $form['login'] = array(
      '#type' => 'fieldset',
      '#title' => t('OBiBa Login Page'),
      '#collapsible' => FALSE,
    );

    $form['login'][obibaAgate::CONFIG_PREFIX_PAGE . 'access_signin_button'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Sign in button'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signin_button'),
      '#maxlength' => 255,
      '#description' => $this->t('Sign in button caption.'),
    );

    $form['login'][obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_page_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Page title'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_page_title'),
      '#maxlength' => 255,
      '#description' => $this->t('User Login page title.'),
    );

    $form['login'][obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_username_label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Username label'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_username_label'),
      '#maxlength' => 255,
      '#description' => $this->t('Username/password input label.'),
    );

    $form['login'][obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_button_caption'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Log in button'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_button_caption'),
      '#maxlength' => 255,
      '#description' => $this->t('Log in button caption'),
    );


    $form['login'][obibaAgate::CONFIG_PREFIX_PAGE . 'enable_form_tooltips'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enable / Disable tooltips forms'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'enable_form_tooltips'),
      '#options' => array(1 => t('Yes'), 0 => t('No')),
      '#description' => $this->t('Enable / Disable tooltips forms'),
    );

    // Registration page.
    $form['register'] = array(
      '#type' => 'fieldset',
      '#title' => t('OBiBa Registration Page'),
      '#collapsible' => FALSE,
    );

    $form['register'][obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button_disabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Disable the sign up button'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button_disabled'),
      '#description' => $this->t('Enable the sign up button.'),
    );

    $form['register'][obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Sign up button'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button'),
      '#maxlength' => 255,
      '#description' => $this->t('Sign up button caption.'),
    );

    $form['register'][obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_register_page_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Registration Page title'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_register_page_title'),
      '#maxlength' => 255,
      '#description' => $this->t('User registration page title.'),
    );
    // Reset password page.
    $form['reset'] = array(
      '#type' => 'fieldset',
      '#title' => t('OBiBa Reset Password Page'),
      '#collapsible' => FALSE,
    );

    $form['reset'][obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_reset_password_button_caption'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email button'),
      '#default_value' => $config->get(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_reset_password_button_caption'),
      '#maxlength' => 255,
      '#description' => $this->t('Email button caption.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

      \Drupal::service('config.factory')->getEditable(obibaAgate::AGATE_SERVER_PAGES_SETTINGS)
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signin_button', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signin_button'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_page_title', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_page_title'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_username_label', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_username_label'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_button_caption', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_login_button_caption'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'enable_form_tooltips', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'enable_form_tooltips'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button_disabled', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button_disabled'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'access_signup_button'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_register_page_title', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_register_page_title'))
      ->set(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_reset_password_button_caption', $form_state->getValue(obibaAgate::CONFIG_PREFIX_PAGE . 'obiba_reset_password_button_caption'))
      ->save();

    parent::submitForm($form, $form_state);

  }


}