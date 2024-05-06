<?php
/*
* Plugin Name: Irizweb Data Table
* Plugin URI: https://github.com/web4mybiz/irizweb-data-table
* Description: Plugin to create a custom table and perform CRUD operations with it.
* Version: 1.0
* Requires at least: 3.0
* Requires PHP: 5.0
* Author: Rizwan Iliyas
* Author URI: https://github.com/web4mybiz
* License:  GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Update URI:
* Text Domain: irizweb-data-table
* Domain Path: /languages
 */


defined('ABSPATH') or die('Access denied');

class IrizDataTable{
    private $table_name = 'irizdata';

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));

        add_action('init', array($this, 'create_data_table'));
        add_shortcode('iriz-dataform', array($this, 'render_dataform'));
        add_shortcode('iriz-data-list', array($this, 'render_datalist'));

        add_action('admin_post_custom_table_submit', array($this, 'handle_data_table_form_submission'));
    }


    public function plugin_activation() {
        $this->create_data_table();
    }

    public function plugin_deactivation() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    public function create_data_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(50) NOT NULL,
                email varchar(200) NOT NULL,
                address varchar(250) NOT NULL,
                city varchar(50) NOT NULL,
                state varchar(50) NOT NULL,
                country varchar(50) NOT NULL,
                PRIMARY KEY  (id)
            )";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}

$iriz_data_table = new IrizDataTable();