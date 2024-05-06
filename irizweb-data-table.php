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
        add_shortcode('iriz-dataform', array($this, 'render_shortcode_dataform'));
        add_shortcode('iriz-data-list', array($this, 'render_shortcode_datalist'));

        add_action('admin_post_iriz_data_submit', array($this, 'save_form_submission'));
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
                iriz_name varchar(50) NOT NULL,
                iriz_email varchar(200) NOT NULL,
                iriz_address varchar(250) NOT NULL,
                iriz_city varchar(50) NOT NULL,
                iriz_state varchar(50) NOT NULL,
                iriz_country varchar(50) NOT NULL,
                PRIMARY KEY  (id)
            )";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function render_shortcode_dataform() {
        ob_start(); ?>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="text" name="iriz_name" placeholder="Name" required>
            <input type="email" name="iriz_email" placeholder="Email" required>
            <input type="text" name="iriz_address" placeholder="Address" required>
            <input type="text" name="iriz_city" placeholder="City" required>
            <input type="text" name="iriz_state" placeholder="State" required>
            <input type="text" name="iriz_country" placeholder="Country" required>
            <input type="hidden" name="action" value="iriz_data_submit">
            <?php wp_nonce_field('iriz_data_nonce', 'iriz_data_nonce'); ?>
            <input type="submit" value="Submit">
        </form>
        <?php
        return ob_get_clean();
    }

    public function save_form_submission() {
        if (!isset($_POST['iriz_data_nonce']) || !wp_verify_nonce($_POST['iriz_data_nonce'], 'iriz_data_nonce')) {
            wp_die('Invalid request!');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }

        // Initialize an empty array for sanitized data
        $sanitized_data = array();

        // Validate and sanitize each field
        foreach ($_POST as $key => $value) {
            if (in_array($key, array('iriz_name', 'iriz_email', 'iriz_address', 'iriz_city', 'iriz_state', 'iriz_country'))) {
                $sanitized_data[$key] = sanitize_text_field($value);
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        
        //Insert sanitized array of data
        $wpdb->insert($table_name, $sanitized_data);

        wp_safe_redirect('/');
        exit();
    }



}

$iriz_data_table = new IrizDataTable();