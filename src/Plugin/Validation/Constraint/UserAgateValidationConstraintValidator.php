<?php
namespace Drupal\obiba_agate\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserAgateValidationConstraintValidator extends ConstraintValidator {

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint) {
        // Basically we use validation to update/Create Agate user so any error will interrupt this actions
        if (!isset($entity)) {
            return;
        }

        if ($entity->bundle() == 'user') {
            // never try to update / create agate user if administrator manually edit create user
            if (\Drupal::currentUser()->hasPermission('administrator')) {
                return;
            }

            // If the user already has an id and he is  trying to update his own profile we're in an entity *update* operation
            elseif ($entity->id() &&  (\Drupal::currentUser()->id() == $entity->id())) {
                $server_response =\Drupal::service('obiba_agate.controller.agateusermanager')->updateAgateUser($entity);
            }

            // Create Agate User
            else{
                $server_response =\Drupal::service('obiba_agate.controller.agateusermanager')->createAgateUser($entity);
            }

            // if server error
            if((!empty($server_response['code']) && $server_response['code'] !== 200) ||  $server_response['code'] === 0){
                $this->context->addViolation($server_response['message']);
            }
        }
    }
}