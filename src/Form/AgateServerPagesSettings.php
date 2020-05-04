<?php
/**
 * @file
 * Contains Drupal\obiba_agate\Form\MessagesForm.
 */
namespace Drupal\obiba_agate\Form;

use Drupal\obiba_agate\ObibaAgate;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AgateServerPagesSettings extends ConfigFormBase {

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
    return ObibaAgate::OBIBA_AGATE_FORM_PAGES_SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $form = parent::buildForm($form, $form_state);
      $config = $this->config(ObibaAgate::AGATE_SERVER_SETTINGS);

    // Login page.
    $form['login'] = [
      '#type' => 'details',
      '#title' => t('OBiBa Login Page'),
      '#open' => TRUE,
    ];

    $form['login'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' .  'access_signin_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sign in button'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'access_signin_button'),
      '#maxlength' => 255,
      '#description' => $this->t('Sign in button caption.'),
    ];

    $form['login'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_login_page_title'] =[
      '#type' => 'textfield',
      '#title' => $this->t('Page title'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_login_page_title'),
      '#maxlength' => 255,
      '#description' => $this->t('User Login page title.'),
    ];

    $form['login'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_login_username_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username label'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_login_username_label'),
      '#maxlength' => 255,
      '#description' => $this->t('Username/password input label.'),
    ];

    $form['login'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_login_button_caption'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Log in button'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' .'obiba_login_button_caption'),
      '#maxlength' => 255,
      '#description' => $this->t('Log in button caption'),
    ];

    $form['login'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'enable_form_tooltips'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable / Disable tooltips forms'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'enable_form_tooltips'),
      '#options' => [1 => t('Yes'), 0 => t('No')],
      '#description' => $this->t('Enable / Disable tooltips forms'),
    ];

    // Registration page.
    $form['register'] = [
        '#type' => 'details',
        '#title' => t('OBiBa Registration Page'),
        '#open' => TRUE,
    ];

    $form['register'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'access_signup_button_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable the sign up button'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'access_signup_button_disabled'),
      '#description' => $this->t('Enable the sign up button.'),
    ];

    $form['register'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'access_signup_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sign up button'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'access_signup_button'),
      '#maxlength' => 255,
      '#description' => $this->t('Sign up button caption.'),
    ];

    $form['register'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_register_page_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Registration Page title'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_register_page_title'),
      '#maxlength' => 255,
      '#description' => $this->t('User registration page title.'),
    ];
    // Reset password page.
    $form['reset'] = [
        '#type' => 'details',
        '#title' => t('OBiBa Reset Password Page'),
        '#open' => TRUE,
    ];

    $form['reset'][ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_reset_password_button_caption'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email button'),
      '#default_value' => $config->get(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_reset_password_button_caption'),
      '#maxlength' => 255,
      '#description' => $this->t('Email button caption.'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      parent::submitForm($form, $form_state);
      $this->config(ObibaAgate::AGATE_SERVER_SETTINGS)
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . 'access_signin_button',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'access_signin_button'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_login_page_title',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_login_page_title'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_login_username_label',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_login_username_label'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_login_button_caption',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_login_button_caption'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'enable_form_tooltips',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'enable_form_tooltips'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' .'access_signup_button_disabled',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'access_signup_button_disabled'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'access_signup_button',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'access_signup_button'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_register_page_title',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_register_page_title'))
      ->set(ObibaAgate::CONFIG_PREFIX_PAGE . '.' . 'obiba_reset_password_button_caption',
          $form_state->getValue(ObibaAgate::CONFIG_PREFIX_PAGE . '_' . 'obiba_reset_password_button_caption'))
      ->save();
  }
}