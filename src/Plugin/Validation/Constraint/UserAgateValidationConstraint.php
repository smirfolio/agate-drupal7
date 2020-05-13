<?php
namespace Drupal\obiba_agate\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Description here.
 *
 * @Constraint(
 *   id = "UserAgateValidation",
 *   label = @Translation("Check if user already exist in Agate Server", context="Validation"),
 *   type = "entity"
 * )
 */
class UserAgateValidationConstraint extends Constraint{

}