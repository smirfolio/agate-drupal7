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
$locale = 'en';
if(!empty($locale_language)){
  $locale = $locale_language;
}
?>
<script
  src="https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit&hl=<?php $locale; ?>"
  async defer></script>

<div class="row">
  <div class="col-md-6">
    <div class="obiba-bootstrap-user-register-form-wrapper">

      <div ng-app="ObibaAgate" ng-controller="RegisterFormController">
        <obiba-alert id="RegisterFormController"></obiba-alert>
          <form ng-if="!hideRegistration" name="joinForm" ng-submit="submit(form)">

          <div sf-schema="joinConfig.schema" sf-form="joinConfig.definition" sf-model="model"></div>

          <div ng-if="config.reCaptchaKey" class="form-group"
            vc-recaptcha
            theme="'light'"
            key="config.reCaptchaKey"
            on-create="setWidgetId(widgetId)"
            on-success="setResponse(response)"></div>

          <div class="md-top-margin">
            <button type="submit" class="btn btn-primary"
              ng-click="onSubmit(joinForm)">
              <?php print t('Join'); ?>
            </button>

            <a href="#/" type="button" class="btn btn-default">
              <?php print t('Cancel') ?>
            </a>
         </div>
        </form>

      <div ng-if="providers && !hasCookie && !hideRegistration">
         <hr>
         <div ng-repeat="provider in providers">
              <a id="{{provider.name}}" class="btn btn-info btn-block text-center voffset2" href="{{provider.linkSingUpPath}}">{{'realm.oidc.signup-with' | translate}} {{provider.title}}</a>
         </div>
          </div>

         <div  ng-if="!hideRegistration" class="md-top-margin">
            <?php print l(t('Already have an account ? Sign in'), 'user/login', array(
              'query' => array('destination' => '/'),
            ));
            ?>
          </div>
      </div>
    </div>
  </div>
  </div>