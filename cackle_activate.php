<?php

function cackle_activate() {
    cackle_install();
}

function cackle_enabled() {
    if (get_option('cackle_apiId') && get_option('cackle_siteApiKey') && get_option('cackle_accountApiKey')) {
        return true;
    }
}

function cackle_activated() {
    if (!empty($_POST['api_id']) && isset($_POST['site_api_key']) && strlen($_POST['site_api_key']) == 64 && isset($_POST['account_api_key']) && strlen($_POST['account_api_key']) == 64) {
        return true;
    }
}

function cackle_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "cackle_channel";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

        $sql = "CREATE TABLE " . $table_name . " (
	            id bigint(20) NOT NULL,
	            time bigint(11) NOT NULL,
	            modified varchar(25) DEFAULT NULL,
	            last_comment varchar(250) DEFAULT NULL,
                PRIMARY KEY (id),
	            UNIQUE KEY id (id)
	        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);

    }
    $wpdb->query("alter table $table_name modify id bigint(20) NOT NULL");
    update_option("cackle_plugin_version", CACKLE_VERSION);
}

function cackle_plugin_is_current_version(){
    $version = get_option( 'cackle_plugin_version','4.07');
    return version_compare($version, CACKLE_VERSION, '=') ? true : false;
}
if ( !cackle_plugin_is_current_version() ) cackle_install();

