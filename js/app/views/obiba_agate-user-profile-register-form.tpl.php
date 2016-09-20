<?php
/**
 * @file
 * Obiba Agate Mdule.
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

        <form id="obiba-user-register" name="theForm" ng-submit="submit(form)">
          <div sf-schema="schema" sf-form="form" sf-model="model"></div>

          <div ng-if="config.key"  vc-recaptcha
            theme="'light'"
            key="config.key"
            on-create="setWidgetId(widgetId)"
            on-success="setResponse(response)"></div>

          <div class="md-top-margin">
            <button type="submit" class="btn btn-primary"
              ng-click="onSubmit(theForm)">
              <span translate>join</span>
            </button>

            <a href="#/" type="button" class="btn btn-default"
              ng-click="onCancel(theForm)">
              <span translate>cancel</span>
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="md-top-margin">
<?php print l(' Already have an account ? Sign in', 'user/login', array(
  'query' => array('destination' => '/'),
));
?>
</div>