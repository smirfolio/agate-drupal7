<?php
/**
 * @file
 * Code for login block.
 */

?>

<p><?php print render($intro_text); ?></p>

<div class="row">
    <div class="col-md-4"></div>
    <div class="well col-md-4">
      <?php print drupal_render_children($form) ?>
        <div class="md-top-margin">
          <?php print l(t('Forgot your password?'), 'user/password') ?>
            <div>
              <?php if (empty(variable_get_value('access_signup_button_disabled')) || !variable_get_value('access_signup_button_disabled')): ?>
                <?php $register_url = (module_exists('obiba_agate') ? 'agate' : 'user') . '/register/';
                $option_sign_up = [];
                if (module_exists('obiba_agate')) {
                  $option_sign_up = array('fragment' => 'join');
                }
                print l(t('Not a member? Join now'), $register_url, $option_sign_up) ?>
              <?php endif; ?>
            </div>
        </div>
      <?php if (!empty($providers)): ?>
        <?php foreach ($providers as $provider_key=> $provider): ?>
              <div class="md-top-margin" >
                <?php print l(t($provider['title']),$provider['linkSingInPath'] , array(
                  'attributes' => array('class' => 'btn btn-info btn-block text-center voffset2')
                )); ?>
              </div>
        <?php endforeach;?>
      <?php endif; ?>
    </div>
    <div class="col-md-4"></div>
</div>
