<?php
// dpm($variables);
?>
<!--
  ~ Copyright (c) 2015 OBiBa. All rights reserved.
  ~
  ~ This program and the accompanying materials
  ~ are made available under the terms of the GNU Public License v3.0.
  ~
  ~ You should have received a copy of the GNU General Public License
  ~ along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<div class="row drupal-profile">
  <div class="col-md-3"><span ng-bind-html="DrupalProfile"></span></div>
  <div class="col-md-8">
    <div sf-schema="schema" sf-form="form" sf-model="model"></div>
    <a ng-href="#/edit"
      class="btn btn-primary">
      <i class="glyphicon glyphicon-edit"></i>
      <?php print t('Edit'); ?>
    </a>
  </div>

</div>

