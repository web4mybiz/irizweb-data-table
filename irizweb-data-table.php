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

// Include the class file for API end points
if ( ! class_exists( 'Irizweb_Data_API' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'irizweb-data-api.php';
}

class Iriz_Data_Table{
    private $table_name = 'irizdata';

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));

        add_action('init', array($this, 'create_data_table'));
        add_shortcode('iriz-dataform', array($this, 'render_shortcode_dataform'));
        add_shortcode('iriz-datalist', array($this, 'render_shortcode_datalist'));

        add_action('admin_post_iriz_data_submit', array($this, 'save_form_submission'));
    }

    // Creating table on plugin activation
    public function plugin_activation() {
        $this->create_data_table();
    }

    // Deleting table on plugin deactivation
    public function plugin_deactivation() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    // Query for table creation
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

    // Form HTML for the shortcode
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

    // Creating the data list to display for shortcode
    public function render_shortcode_datalist() {

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        // Use prepared statement to prevent SQL injection
        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name"));
        if (!empty($data)) {
            ob_start(); ?>
            <ul>
                <?php foreach ($data as $item): ?>
                    <li><?php echo esc_html($item->iriz_name.' '.$item->iriz_email.' '.$item->iriz_address.' '.$item->iriz_city.' '.$item->iriz_state.' '.$item->iriz_country); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php
            return ob_get_clean();
        } else {
            return '<p>No data available</p>';
        }
    }

    // Saving submissions in the database
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
                $san_data = sanitize_text_field($value);
                $sanitized_data[$key] = preg_replace( '/[^a-zA-Z0-9\s\.,-]/', '', $san_data );
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        
        //Insert sanitized array of data
        $result = $wpdb->insert($table_name, $sanitized_data);
        if (!$result) {
            wp_die('Error inserting data');
        }
        wp_redirect( '/' );
        exit();
    }
}

if ( class_exists( 'Iriz_Data_Table' ) ) {
    new Iriz_Data_Table();
}
if ( class_exists( 'Irizweb_Data_API' ) ) {
    new Irizweb_Data_API();
}