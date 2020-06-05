<?php
/**
 * Created by IntelliJ IDEA.
 * User: samir
 * Date: 24/02/20
 * Time: 12:45 PM
 */

namespace Drupal\obiba_agate;

class ObibaAgate {
    const AGATE_SERVER_SETTINGS = 'obiba_agate.settings';

    // Agate Page Settings
    const CONFIG_PREFIX_PAGE = 'page_settings';
    const OBIBA_AGATE_FORM_PAGES_SETTINGS = 'agate_page_setting';

    // Agate Server Settings
    const CONFIG_PREFIX_SERVER = 'server';
    const OBIBA_AGATE_FORM_SERVER_SETTINGS = 'agate_server_setting';

    // Agate Mapping fields Settings
    const CONFIG_PREFIX_USER_FIELDS_MAPPING = 'user_fields_mapping';
    const AGATE_PROFILE_FIELD = 'agate_profile_field';
    const DRUPAL_PROFILE_FIELD = 'drupal_profile_field';
    const DRUPAL_ENABLED_FILED_IMPORT = 'enabled_import';
    const OBIBA_AGATE_FORM_USER_FIELDS_MAPPING_SETTINGS = 'agate_user_fields_mapping_setting';

    // Agate User register Form
    const AGATE_USER_REGISTER_FORM = 'user-register';

    // Agate User Password Activation
    const AGATE_USER_PASSWORD_ACTIVATION_FORM = 'user-reset-password';

    const AGATE_PROVIDER = 'obiba_agate';
}
