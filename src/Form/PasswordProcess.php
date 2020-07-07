<?php


namespace Drupal\obiba_agate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\obiba_agate\ObibaAgate;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\BeforeCommand;

/**
 * Form Reset / Activation password process
 *
 * Class PasswordProcess
 * @package Drupal\obiba_agate\Form
 */
class PasswordProcess extends FormBase {

    public function getFormId()
    {
    return ObibaAgate::AGATE_USER_PASSWORD_ACTIVATION_FORM;
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $update = \Drupal::request()->query->get('updatePassword');
        $confirmKey = \Drupal::request()->query->get('key');
        if($update){
            $form['#prefix'] = '<div id="update-password-form"></div>';

            $form['current_password'] = array(
                '#type' => 'password',
                '#title' => t('Enter your current password'),
                '#required' => TRUE,
                '#default_value' => "",
                '#description' => t('Please enter your current password'),
                '#size' => 20,
                '#maxlength' => 20,
            );
            $form['pass'] = [
                '#type' => 'password_confirm'
            ];
            $form['update'] = [
                '#type' => 'hidden',
                '#default_value' => 1,
            ];
            $form['submit'] = [
                '#type' => 'submit',
                '#value' => 'Send Password',
                '#attributes' => [
                    'class' => [
                        'use-ajax',
                    ],
                ],
                '#ajax' => [
                    'callback' => [$this, 'submitModalFormAjax'],
                    'event' => 'click',
                ],
            ];
        }
        if(!$update && !empty($confirmKey)) {
            $form['pass'] = [
                '#type' => 'password_confirm'
            ];
            $form['key'] = array(
                '#type' => 'hidden',
                '#required' => TRUE,
                '#default_value' => $confirmKey,
            );
            $form['submit'] = [
                '#type' => 'submit',
                '#value' => 'Send Password',
            ];
        }

        return $form;
    }

    /**
     * Callback process Password form
     *
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
        $currentPath = \Drupal::service('path.current')->getPath();
        $action = explode('/agate/', $currentPath);
        if($action[1] === 'reset_password'){
            $response = \Drupal::service('obiba_agate.server.agateclient')->confirmResetPassword(
                [
                    'key' => $form_state->getValue('key'),
                    'password' => $form_state->getValue('pass'),
                ],
                'reset_password'
            );
        }
        else{
            $response = \Drupal::service('obiba_agate.server.agateclient')->confirmResetPassword(
                [
                    'key' => $form_state->getValue('key'),
                    'password' => $form_state->getValue('pass'),
                ],
                'confirm'
            );
        }

        if($response['code'] == 204 || $response['code'] == 200){
            $this->messenger()->addStatus($this->t('Password sent', []));
            $form_state->setRedirect('<front>');
        }
        else{
            $this->messenger()->addError($this->t('Server Error Code :%code, %message', [
                '%code' => $response['code'],
                '%message' => $response['message']
            ]));
        }
    }

    public function submitModalFormAjax(array &$form, FormStateInterface $form_state){
        $serverResponse = \Drupal::service('obiba_agate.server.agateclient')->updatePassword(
            [
                'password' => $form_state->getValue('pass'),
            ],
            base64_encode(\Drupal::currentUser()->getAccountName() . ':' .  $form_state->getValue('current_password'))
        );
        $response = new AjaxResponse();
        if($serverResponse['code'] == 204){
            $response->addCommand(new CloseModalDialogCommand());
            $this->messenger()->deleteAll();
            $this->messenger()->addStatus($serverResponse['message']);
            $status_messages = array('#type' => 'status_messages');
            $messages = \Drupal::service('renderer')->renderRoot($status_messages);
            $response->addCommand(new BeforeCommand('.page-header', $messages));
        }
        else{
            $this->messenger()->deleteAll();
            $this->messenger()->addError(t('Server Error please check you password or be sure the strength of your new entred password'));
            $status_messages = array('#type' => 'status_messages');
            $messages = \Drupal::service('renderer')->renderRoot($status_messages);
            $response->addCommand(new ReplaceCommand('#update-password-form', $messages));
        }
        return $response;
    }

}