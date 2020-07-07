<?php


namespace Drupal\obiba_agate\Form;

use Drupal\user\Form\UserPasswordForm;
use Drupal\Core\Form\FormStateInterface;

class AgatePasswordReset extends UserPasswordForm {


    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        return parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $nameMailUser = trim($form_state->getValue('name'));

        // Drupal User
        if(!\Drupal::service('obiba_agate.controller.agateusermanager')->isExternalUser($nameMailUser)){
            parent::submitForm($form, $form_state);
        }
        else{
            // Agate User
            $responsePasswordServer = \Drupal::service('obiba_agate.server.agateclient')->resetPassword(
                ['username' => $nameMailUser]);
            if($responsePasswordServer['code'] == 200){
                // if User in agate don't process Drupal password reset
                $this->logger('user')->notice('Password reset instructions mailed to %name.',
                    ['%name' => $nameMailUser]);
                $this->messenger()->addStatus($this->t('Further instructions have been sent to your email address.'));
                $form_state->setRedirect('user.page');
            }
            else{
                // Agate Server side errors
                $this->logger('user')->error('%error.',
                    ['%error' => $responsePasswordServer['message']]);
                $this->messenger()->addError($responsePasswordServer['message']);
                $form_state->setRedirect('user.page');
            }
        }
    }

}