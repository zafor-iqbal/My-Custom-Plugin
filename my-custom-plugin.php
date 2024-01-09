<?php
/**
 * Plugin Name: My Custom Plugin Github
 * Description: A custom plugin to create a table named 'myrecords' with specific columns, view , search and delete records.
 * Version: 1.0
 * Author: Zafor
 */


 function my_custom_plugin_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_scripts');


function create_myrecords_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myrecords';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        amount int(10) NOT NULL,
        buyer varchar(255) NOT NULL,
        receipt_id varchar(20) NOT NULL,
        items varchar(255) NOT NULL,
        buyer_email varchar(50) NOT NULL,
        buyer_ip varchar(20) DEFAULT '' NOT NULL,
        note text NOT NULL,
        city varchar(20) NOT NULL,
        phone varchar(20) NOT NULL,
        hash_key varchar(255) DEFAULT '' NOT NULL,
        entry_at date DEFAULT NULL,
        entry_by int(10) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


function update_permalink_structure() {
    // Update permalink structure to 'Post name'
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');

    // Flush rewrite rules to apply changes
    flush_rewrite_rules();
}

function my_custom_plugin_activation() {
    // Call function to create table
    create_myrecords_table();

    // Call function to update permalink structure
    update_permalink_structure();
}

register_activation_hook(__FILE__, 'my_custom_plugin_activation');




function my_custom_frontend_form() {
    ?>
<div class="wrap">
    <h2>Frontend Custom Form</h2>
    <form id="my-frontend-form" method="post">
        <!-- Hidden field for AJAX action -->
        <input type="hidden" name="action" value="my_custom_frontend_form_action">

        <!-- Fields based on the 'myrecords' table structure -->
        <p>
            <label for="amount">Amount:</label>
            <input type="number" name="amount" id="amount" required />
        </p>
        <p>
            <label for="buyer">Buyer:</label>
            <input type="text" name="buyer" id="buyer" maxlength="255" required />
        </p>
        <p>
            <label for="receipt_id">Receipt ID:</label>
            <input type="text" name="receipt_id" id="receipt_id" maxlength="20" required />
        </p>
        <p>
            <label for="items">Items:</label>
            <input type="text" name="items" id="items" maxlength="255" required />
        </p>
        <p>
            <label for="buyer_email">Buyer Email:</label>
            <input type="email" name="buyer_email" id="buyer_email" maxlength="50" required />
        </p>
        <p>
            <label for="note">Note:</label>
            <textarea name="note" id="note" required></textarea>
        </p>
        <p>
            <label for="city">City:</label>
            <input type="text" name="city" id="city" maxlength="20" required />
        </p>
        <p>
            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone" maxlength="20" required />
        </p>
        <p>
            <label for="entry_by">Entry By:</label>
            <input type="number" name="entry_by" id="entry_by" required />
        </p>

        <input type="submit" id="submit_frontend" class="button button-primary" value="Submit">
    </form>
    <div id="frontend-form-response"></div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#my-frontend-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        // Perform AJAX request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#frontend-form-response').html('<div class="notice notice-success"><p>' +
                    response + '</p></div>');
                // Reset form after successful submission
                $('#my-frontend-form').trigger('reset');
            },
            error: function() {
                $('#frontend-form-response').html(
                    '<div class="notice notice-error"><p>An error occurred.</p></div>');
            }
        });
    });
});
</script>
<?php
}

function my_custom_frontend_form_handle_ajax() {

     // Check if the user has already submitted in the last 24 hours
    //  if (isset($_COOKIE['my_custom_form_submitted'])) {
    //     echo 'You have already submitted the form. Please wait 24 hours before submitting again.';
    //     wp_die();
    // }

    global $wpdb; // Global database connection

    // Check if all expected POST data is set
    if (isset($_POST['amount'], $_POST['buyer'], $_POST['receipt_id'], $_POST['items'], $_POST['buyer_email'], $_POST['note'], $_POST['city'], $_POST['phone'], $_POST['entry_by'])) {
        
        // Sanitize and validate inputs
        $amount = intval($_POST['amount']);
        $buyer = sanitize_text_field($_POST['buyer']);
        $receipt_id = sanitize_text_field($_POST['receipt_id']);
        $items = sanitize_text_field($_POST['items']);
        $buyer_email = sanitize_email($_POST['buyer_email']);
        $note = sanitize_textarea_field($_POST['note']);
        $city = sanitize_text_field($_POST['city']);
        $phone = sanitize_text_field($_POST['phone']);
        $entry_by = intval($_POST['entry_by']);

        // Additional validations can be added here
        // Example: Check if email is valid
        if (!filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
            echo 'Invalid email format.';
            wp_die();
        }

        // Prepare additional data for insertion
        $buyer_ip = $_SERVER['REMOTE_ADDR'];
        $hash_key = wp_hash($receipt_id);
        $entry_at = current_time('mysql');

        // Insert data into the database
        $table_name = $wpdb->prefix . 'myrecords';
        $result = $wpdb->insert($table_name, array(
            'amount' => $amount,
            'buyer' => $buyer,
            'receipt_id' => $receipt_id,
            'items' => $items,
            'buyer_email' => $buyer_email,
            'buyer_ip' => $buyer_ip,
            'note' => $note,
            'city' => $city,
            'phone' => $phone,
            'hash_key' => $hash_key,
            'entry_at' => $entry_at,
            'entry_by' => $entry_by
        ));

        if ($result) {
            // Set a cookie that expires in 24 hours
            // setcookie('my_custom_form_submitted', '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
    
            echo 'Form submitted successfully.';
        } else {
            echo 'There was an error submitting the form.';
        }
    } else {
        echo 'Required form data is missing.';
    }

    wp_die(); // Terminate AJAX request
}
add_action('wp_ajax_my_custom_frontend_form_action', 'my_custom_frontend_form_handle_ajax');
add_action('wp_ajax_nopriv_my_custom_frontend_form_action', 'my_custom_frontend_form_handle_ajax');





function my_custom_plugin_view_records() {
    global $wpdb, $wp;
    $table_name = $wpdb->prefix . 'myrecords';
    $current_url = home_url(add_query_arg(array(), $wp->request));


    // Handle update request
    my_custom_plugin_process_update();

    // Handle delete request
    my_custom_plugin_handle_delete_records();

    // Check if edit form needs to be displayed
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['record_id'])) {
        my_custom_plugin_display_update_form(intval($_GET['record_id']));
        return;
    }

    // Check for search term, date range, and user ID
    $search_term = isset($_POST['search_item']) ? trim($_POST['search_item']) : '';
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    // Search form
    echo '<div class="wrap"><h2>View Records</h2>';
    echo '<form method="post" action="">';
    echo '<input type="text" name="search_item" placeholder="Search by item..." value="' . esc_attr($search_term) . '"/>';
    echo '<input type="date" name="date_from" placeholder="From date" value="' . esc_attr($date_from) . '"/>';
    echo '<input type="date" name="date_to" placeholder="To date" value="' . esc_attr($date_to) . '"/>';
    echo '<input type="number" name="user_id" placeholder="User ID" value="' . esc_attr($user_id) . '"/>';
    echo '<input type="submit" name="search_submit" value="Search" class="button"/>';
    echo '</form>';

    // Modify query based on search term, date range, and user ID
    $sql = "SELECT * FROM $table_name WHERE 1=1";
    if (!empty($search_term)) {
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';
        $sql .= $wpdb->prepare(" AND items LIKE %s", $search_term);
    }
    if (!empty($date_from)) {
        $sql .= $wpdb->prepare(" AND entry_at >= %s", $date_from);
    }
    if (!empty($date_to)) {
        $sql .= $wpdb->prepare(" AND entry_at <= %s", $date_to);
    }
    if (!empty($user_id)) {
        $sql .= $wpdb->prepare(" AND entry_by = %d", $user_id);
    }

    $records = $wpdb->get_results($sql);

    // Start of the form for deletion
    echo '<form method="post" action="">';
    wp_nonce_field('my_custom_plugin_delete_records', 'my_custom_plugin_nonce_field');

    // Display the records in a table
    echo '<table class="wp-list-table widefat fixed striped">';
    // Table headers
    echo '<thead><tr><th>Select</th><th>Amount</th><th>Buyer</th><th>Receipt ID</th><th>Items</th><th>Buyer Email</th><th>Note</th><th>City</th><th>Phone</th><th>Entry By</th><th>Action</th></tr></thead>';
    echo '<tbody>';

    foreach ($records as $record) {
        echo '<tr>';
        echo '<td><input type="checkbox" name="record_ids[]" value="' . esc_attr($record->id) . '"></td>';
        echo '<td>' . esc_html($record->amount) . '</td>';
        echo '<td>' . esc_html($record->buyer) . '</td>';
        echo '<td>' . esc_html($record->receipt_id) . '</td>';
        echo '<td>' . esc_html($record->items) . '</td>';
        echo '<td>' . esc_html($record->buyer_email) . '</td>';
        echo '<td>' . esc_html($record->note) . '</td>';
        echo '<td>' . esc_html($record->city) . '</td>';
        echo '<td>' . esc_html($record->phone) . '</td>';
        echo '<td>' . esc_html($record->entry_by) . '</td>';
        echo '<td><a href="?page=my-custom-edit-record&action=edit&record_id=' . esc_attr($record->id) . '">Edit</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    // Add delete button
    echo '<input type="submit" name="delete_records" value="Delete Selected" class="button action" onclick="return confirm(\'Are you sure you want to delete these records?\')">';
    // Add show all records button
    echo '<input type="submit" name="show_all_records" value="Show All Records" class="button action" style="margin-left: 10px;">';

    echo '</form>';
    echo '</div>';

    // Check if the show all records button has been clicked
    if (isset($_POST['show_all_records'])) {
        // Redirect to the current page (resetting any filters)
        echo '<script type="text/javascript">location.href = "' . $current_url . '";</script>';
    }
}


function my_custom_plugin_process_update() {
    // Check if it's an AJAX request and the correct action
    if (isset($_POST['action']) && $_POST['action'] == 'my_custom_plugin_update_action') {
        // Verify nonce for security
        if (!isset($_POST['my_custom_plugin_nonce_field']) || !wp_verify_nonce($_POST['my_custom_plugin_nonce_field'], 'my_custom_plugin_update_record_' . $_POST['record_id'])) {
            echo 'Nonce check failed';
            wp_die();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'myrecords';

        // Sanitize and validate the data
        $record_id = intval($_POST['record_id']);
        $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
        $buyer = isset($_POST['buyer']) ? sanitize_text_field($_POST['buyer']) : '';
        $receipt_id = isset($_POST['receipt_id']) ? sanitize_text_field($_POST['receipt_id']) : '';
        $items = isset($_POST['items']) ? sanitize_text_field($_POST['items']) : '';
        $buyer_email = isset($_POST['buyer_email']) ? sanitize_email($_POST['buyer_email']) : '';
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $entry_by = isset($_POST['entry_by']) ? intval($_POST['entry_by']) : 0;

        // Perform additional validation if needed

        // Prepare the data for update
        $data = [
            'amount' => $amount,
            'buyer' => $buyer,
            'receipt_id' => $receipt_id,
            'items' => $items,
            'buyer_email' => $buyer_email,
            'note' => $note,
            'city' => $city,
            'phone' => $phone,
            'entry_by' => $entry_by
        ];

        // Perform the database update
        $result = $wpdb->update($table_name, $data, ['id' => $record_id]);

        // Check the result and respond
        if ($result !== false) {
            echo 'Record updated successfully.';
        } else {
            echo 'There was an error updating the record.';
        }

        wp_die();
    }
}
add_action('wp_ajax_my_custom_plugin_update_action', 'my_custom_plugin_process_update');




function my_custom_plugin_handle_delete_records() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myrecords';

    if (isset($_POST['delete_records'], $_POST['record_ids'])) {
        foreach ($_POST['record_ids'] as $record_id) {
            $wpdb->delete($table_name, ['id' => intval($record_id)]);
        }
    }
}


function my_custom_plugin_display_update_form($record_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myrecords';

    // Get the record data from the database
    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $record_id));

    if ($record) {
        // Start outputting the form HTML
        echo '<div class="wrap">';
        echo '<h1>Edit Record</h1>';
        echo '<form id="my-custom-update-form" method="post">';

        // Security field
        wp_nonce_field('my_custom_plugin_update_record_' . $record_id, 'my_custom_plugin_nonce_field');
        
        // Hidden field for record ID
        echo '<input type="hidden" name="record_id" value="' . esc_attr($record->id) . '">';
        
        // Form fields with pre-populated data
        echo '<table class="form-table">';
        echo '<tr><th scope="row"><label for="amount">Amount</label></th>';
        echo '<td><input type="number" name="amount" id="amount" value="' . esc_attr($record->amount) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="buyer">Buyer</label></th>';
        echo '<td><input type="text" name="buyer" id="buyer" value="' . esc_attr($record->buyer) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="receipt_id">Receipt ID</label></th>';
        echo '<td><input type="text" name="receipt_id" id="receipt_id" value="' . esc_attr($record->receipt_id) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="items">Items</label></th>';
        echo '<td><input type="text" name="items" id="items" value="' . esc_attr($record->items) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="buyer_email">Buyer Email</label></th>';
        echo '<td><input type="email" name="buyer_email" id="buyer_email" value="' . esc_attr($record->buyer_email) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="note">Note</label></th>';
        echo '<td><textarea name="note" id="note">' . esc_textarea($record->note) . '</textarea></td></tr>';

        echo '<tr><th scope="row"><label for="city">City</label></th>';
        echo '<td><input type="text" name="city" id="city" value="' . esc_attr($record->city) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="phone">Phone</label></th>';
        echo '<td><input type="tel" name="phone" id="phone" value="' . esc_attr($record->phone) . '"/></td></tr>';

        echo '<tr><th scope="row"><label for="entry_by">Entry By</label></th>';
        echo '<td><input type="number" name="entry_by" id="entry_by" value="' . esc_attr($record->entry_by) . '"/></td></tr>';

        echo '</table>';

        // Submit button
        echo '<input type="submit" name="update_record" value="Update Record" class="button button-primary">';
        echo '</form>';
        echo '<div id="update-form-response"></div>';
        echo '</div>';

        // Include the JavaScript for handling AJAX submission
        add_action('wp_footer', 'my_custom_plugin_frontend_ajax_script');
    } else {
        echo '<div class="wrap"><h1>Edit Record</h1><p>Record not found.</p></div>';
    }
}

function my_custom_plugin_frontend_ajax_script() {
    ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#my-custom-update-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData + '&action=my_custom_plugin_update_action',
            success: function(response) {
                $('#update-form-response').html(
                    '<div class="notice notice-success is-dismissible"><p>' + response +
                    '</p></div>');
                // Redirect to the previous page after a short delay
                setTimeout(function() {
                    window.history.back();
                }, 1000); // 2 seconds delay
            },
            error: function() {
                $('#update-form-response').html(
                    '<div class="notice notice-error is-dismissible"><p>An error occurred while updating the record.</p></div>'
                );
            }
        });
    });
});
</script>
<?php
}



function my_custom_plugin_edit_record_page() {
    my_custom_plugin_process_update(); // Handle update form submission

    if (isset($_GET['record_id'])) {
        my_custom_plugin_display_update_form(intval($_GET['record_id']));
    }
}


// Shortcodes 



function my_custom_frontend_form_shortcode() {
    ob_start();
    my_custom_frontend_form();
    return ob_get_clean();
}
add_shortcode('my_custom_frontend_form', 'my_custom_frontend_form_shortcode');


function my_custom_plugin_register_block() {
    // Register the block editor script
    wp_register_script(
        'my-custom-form-block-editor', // Handle for the script
        plugin_dir_url(__FILE__) . 'my-custom-form-block.js', // Path to the block editor script
        array('wp-blocks', 'wp-element', 'wp-editor') // Dependencies
    );

    // Register your block
    register_block_type('my-custom-plugin/my-custom-form', array(
        'editor_script' => 'my-custom-form-block-editor', // Use the script handle as the editor script
        'render_callback' => 'my_custom_frontend_form_shortcode' // This renders the block on the frontend
    ));
}
add_action('init', 'my_custom_plugin_register_block');



class My_Custom_Form_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'my_custom_form_widget', // Base ID
            'My Custom Form Widget', // Name
            array('description' => 'Displays the custom form') // Args
        );
    }

    public function widget($args, $instance) {
        echo my_custom_frontend_form_shortcode(); // Display the form
    }
}

function my_custom_plugin_register_widget() {
    register_widget('My_Custom_Form_Widget');
}
add_action('widgets_init', 'my_custom_plugin_register_widget');


function my_custom_plugin_report_shortcode() {
    if (is_user_logged_in() && current_user_can('edit_others_posts')) {
        ob_start();
        my_custom_plugin_view_records(); // Assuming this is your existing function that outputs the report table
        return ob_get_clean();
    } else {
        return '<p>You must be logged in as an editor or higher to view this report.</p>';
    }
}
add_shortcode('my_custom_report', 'my_custom_plugin_report_shortcode');

function my_custom_plugin_report_block() {
    wp_enqueue_script(
        'my-custom-report-block',
        plugin_dir_url(__FILE__) . 'my-report-block.js',
        array('wp-blocks', 'wp-editor', 'wp-element')
    );
}
add_action('enqueue_block_editor_assets', 'my_custom_plugin_report_block');


function my_custom_plugin_report_block_render() {
    return my_custom_plugin_report_shortcode(); // Reuse the shortcode function.
}
register_block_type('my-custom-plugin/my-custom-report', array(
    'render_callback' => 'my_custom_plugin_report_block_render',
));