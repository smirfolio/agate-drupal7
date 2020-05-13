<?php
namespace Drupal\obiba_agate\Plugin\Validation\Constraint;


use Drupal\obiba_agate\ObibaAgate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserAgateValidationConstraintValidator extends ConstraintValidator {

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint) {
        if (!isset($entity)) {
            return;
        }

        if ($entity->bundle() == 'user') {
            // If the user already has an id we're in an entity *update* operation
            // instead of an entity *creation* operation. We don't want prevent
            // existing user from being updated.
            if ($entity->id() || \Drupal::currentUser()->hasPermission('administrator')) {
                return;
            }

            // Only On user agate creation
            // Create Agate User
            $server_response =\Drupal::service('obiba_agate.controller.agateusermanager')->createAgateUser($entity);

            // if server error
            if((!empty($server_response['code']) && $server_response['code'] !== 200) ||  $server_response['code'] === 0){
                $this->context->addViolation($server_response['message']);
            }
        }
    }
}