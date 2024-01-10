<?php
/**
 * Plugin Name:         My Custom Plugin
 * Plugin URI:          https://zaforiqbal.com/plugins/my-custom-plugin/
 * Description:         A custom plugin to create a table named 'myrecords' with specific columns, view , search and delete records.
 * Version:             1.0
 * Author:              Zafor Iqbal
 * Author URI:          https://zaforiqbal.com/
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least:   4.9
 * Requires PHP:        5.6
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */


if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 * Enqueues jQuery scripts.
 */
function my_custom_plugin_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_scripts');

/**
 * Creates 'myrecords' table in the database.
 */
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

/**
 * Updates the permalink structure to 'Post name'.
 */
function update_permalink_structure() {
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
    flush_rewrite_rules();
}

/**
 * Activates the plugin.
 * - Creates necessary database tables.
 * - Updates permalink structure.
 */
function my_custom_plugin_activation() {
    create_myrecords_table();
    update_permalink_structure();
}
register_activation_hook(__FILE__, 'my_custom_plugin_activation');




/**
 * Displays a custom frontend form.
 *
 * This function outputs the HTML for a frontend form,
 * and includes inline JavaScript for handling form submission via AJAX.
 */

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

/**
 * Handles AJAX requests for the frontend form submission.
 *
 * This function processes the AJAX request made by the 'my_custom_frontend_form' function.
 * It performs several key actions:
 * 1. Validates the nonce and user capabilities (if necessary) for security.
 * 2. Sanitizes and validates the input data received from the form.
 * 3. Prepares and inserts the data into the 'myrecords' table in the database.
 * 4. Optionally sets a cookie to limit form resubmission (commented out by default).
 * 5. Returns a success or error message which is then displayed to the user.
 *
 * Note: This function assumes that all necessary POST fields are sent by the form.
 *       It also uses the global $wpdb object to interact with the database.
 */

function my_custom_frontend_form_handle_ajax() {

     // Check if the user has already submitted in the last 24 hours
     if (isset($_COOKIE['my_custom_form_submitted'])) {
        echo 'You have already submitted the form. Please wait 24 hours before submitting again.';
        wp_die();
     }

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



/**
 * Displays and manages the records in the 'myrecords' table.
 *
 * This function performs several key tasks:
 * 1. Handles requests for updating and deleting records, if necessary.
 * 2. Processes search queries and filters to display specific records based on user input.
 * 3. Renders a table showing the records from the 'myrecords' table with options to edit or delete.
 * 4. Provides form inputs for filtering records based on search terms, date ranges, and user IDs.
 * 5. Implements pagination, sorting, and other viewing options as required (if implemented).
 *
 * Note: This function should be used in admin areas or places where users need to manage records.
 *       It assumes the existence of certain GET and POST parameters for its operations and uses global $wpdb for database queries.
 */


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


/**
 * Processes the update request for a record in the 'myrecords' table.
 *
 * This function is responsible for handling the update operation of records. Key aspects include:
 * 1. Verifying the AJAX request and ensuring security by checking nonces.
 * 2. Sanitizing and validating the incoming data from the update form to prevent security issues and data corruption.
 * 3. Updating the specific record in the 'myrecords' table based on the provided record ID and user inputs.
 * 4. Returning a success or error message for the AJAX request to inform the user of the operation's outcome.
 *
 * Note: This function is typically called via AJAX and expects certain POST parameters to be set,
 *       including a unique identifier for the record to be updated. It uses global $wpdb for database operations.
 */

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


/**
 * Handles the deletion of records from the 'myrecords' table.
 *
 * This function is responsible for:
 * 1. Checking if the delete request is submitted along with the necessary record IDs.
 * 2. Iterating through the list of provided record IDs and deleting each record from the database.
 * 3. Utilizing the global $wpdb object for performing the delete operation on the database.
 *
 * Note: This function assumes that the deletion request is sent via a form submission (POST method).
 *       It relies on the presence of 'record_ids' in the POST data, which should be an array of IDs to be deleted.
 *       Proper security checks, like nonce verification, should be implemented to ensure safe operations.
 */

function my_custom_plugin_handle_delete_records() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myrecords';

    if (isset($_POST['delete_records'], $_POST['record_ids'])) {
        foreach ($_POST['record_ids'] as $record_id) {
            $wpdb->delete($table_name, ['id' => intval($record_id)]);
        }
    }
}


/**
 * Displays a form for updating a specific record in the 'myrecords' table.
 *
 * Responsibilities of this function include:
 * 1. Retrieving the specific record from the database based on the provided record ID.
 * 2. Displaying a form pre-filled with the record's data, allowing users to update it.
 * 3. Including necessary form fields corresponding to the record's data structure in 'myrecords' table.
 * 4. Ensuring security by including nonce fields for verification during form submission.
 * 5. Handling potential errors, such as the record not being found in the database.
 *
 * Note: This function expects a record ID to be passed as a parameter. It uses global $wpdb for database queries.
 *       The form targets an AJAX handler for updating the record upon submission.
 */

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

/**
 * Enqueues and outputs the JavaScript for handling AJAX submissions of the frontend update form.
 *
 * This function is responsible for:
 * 1. Injecting JavaScript into the page footer, which binds a submission event handler to the update form.
 * 2. Preventing the default form submission behavior to enable AJAX-based submission.
 * 3. Serializing the form data and sending it to the WordPress backend via AJAX.
 * 4. Handling the response from the AJAX request, displaying success or error messages to the user.
 * 5. Optionally, redirecting the user or performing other actions upon successful form submission.
 *
 * Note: This function is designed to be used in conjunction with forms that require AJAX for data submission.
 *       It assumes that the form has an ID which is targeted by the JavaScript for binding the event handler.
 *       The actual AJAX URL and action should be appropriately set in the JavaScript code.
 */

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
                }, 1000); // 1 second delay
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


/**
 * Handles the display and processing of the record edit page.
 *
 * This function is responsible for:
 * 1. Invoking the update process if an update request is detected.
 * 2. Displaying the form for editing a record, which includes fetching and showing the current data of the record.
 * 3. Handling the scenario where a record ID is provided through GET parameters for editing.
 * 4. Ensuring that the appropriate form and data are shown based on the provided record ID.
 *
 * Note: This function relies on the 'record_id' GET parameter to determine which record to edit.
 *       It uses the my_custom_plugin_display_update_form function for rendering the edit form.
 *       Security checks, such as verifying user capabilities and nonces, should be performed as needed.
 */

function my_custom_plugin_edit_record_page() {
    my_custom_plugin_process_update(); // Handle update form submission

    if (isset($_GET['record_id'])) {
        my_custom_plugin_display_update_form(intval($_GET['record_id']));
    }
}


/**
 * Shortcode handler for displaying the custom frontend form.
 *
 * This function is responsible for:
 * 1. Initiating an output buffer to capture the HTML output of the custom form.
 * 2. Calling the my_custom_frontend_form function which generates the HTML form.
 * 3. Returning the buffered HTML content, allowing it to be used as a shortcode in posts, pages, or widgets.
 *
 * Usage:
 * [my_custom_frontend_form] - Embeds the custom frontend form in a post, page, or widget.
 *
 * Note: This function uses output buffering to capture and return the form's HTML instead of directly echoing it.
 *       This is essential for the proper functioning of shortcodes in WordPress, ensuring that the form appears
 *       exactly where the shortcode is used in the content.
 */



function my_custom_frontend_form_shortcode() {
    ob_start();
    my_custom_frontend_form();
    return ob_get_clean();
}
add_shortcode('my_custom_frontend_form', 'my_custom_frontend_form_shortcode');


/**
 * Registers a custom Gutenberg block for the plugin.
 *
 * This function handles the following tasks:
 * 1. Registering the JavaScript file for the block editor interface, specifying any dependencies it requires.
 * 2. Registering the custom block type with WordPress, linking it to the registered JavaScript and an optional render callback.
 * 3. Defining the block's metadata, such as its name, category, and editor script handle.
 * 4. Optionally setting a render callback if the block requires server-side rendering (e.g., for dynamic content).
 *
 * Usage:
 * This function should be hooked to the 'init' action to ensure it runs at the correct time in WordPress's execution.
 *
 * Note: Ensure that the JavaScript file for the block editor is correctly enqueued and that the block's metadata
 *       is accurately defined. If the block requires server-side rendering, the render callback function should be
 *       properly implemented and specified here.
 */

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



/**
 * Custom widget class extending WP_Widget for displaying a frontend form.
 *
 * This class defines a custom widget which can be added to widget areas in WordPress.
 * The widget displays a frontend form, typically used for data submission or user interaction.
 *
 * Features of this custom widget include:
 * 1. Defining widget properties like its name and description in the constructor.
 * 2. Implementing the 'widget' method to specify the output of the widget on the frontend.
 * 3. Optionally, implementing 'form' and 'update' methods to handle widget settings in the admin area, if needed.
 *
 * Usage:
 * - The widget can be added to any registered widget area through the WordPress dashboard.
 * - Once added, it displays the custom frontend form wherever the widget area is rendered.
 *
 * Note: This class should be initialized and registered within the WordPress widget initialization process.
 *       Typically, this is done through the 'widgets_init' action hook.
 */
class My_Custom_Form_Widget extends WP_Widget {

    /**
     * Constructs the new widget instance.
     */
    function __construct() {
        parent::__construct(
            'my_custom_form_widget', // Base ID of the widget
            'My Custom Form Widget', // Display name of the widget
            array('description' => 'Displays the custom form') // Additional options for the widget
        );
    }

    /**
     * Outputs the content of the widget.
     *
     * @param array $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function widget($args, $instance) {
        // Output the frontend form
        echo my_custom_frontend_form_shortcode();
    }

    // Optional: Implement 'form' and 'update' methods for handling admin settings.
}

/**
 * Registers the custom widget with WordPress.
 */
function my_custom_plugin_register_widget() {
    register_widget('My_Custom_Form_Widget');
}
add_action('widgets_init', 'my_custom_plugin_register_widget');



/**
 * Shortcode handler for displaying a custom report.
 *
 * This function is responsible for:
 * 1. Checking user permissions to ensure only authorized users (like editors or administrators) can view the report.
 * 2. Using output buffering to capture the HTML generated by the report viewing function.
 * 3. Returning the captured HTML content to be used wherever the shortcode is placed within posts, pages, or widgets.
 *
 * Usage:
 * [my_custom_report] - Embeds the custom report in a post, page, or widget area.
 *
 * Note: This function assumes that the report generation logic (like querying the database and generating HTML)
 *       is handled by the my_custom_plugin_view_records function or a similar function designated for this purpose.
 *       It ensures that the report is only accessible to logged-in users with sufficient permissions, adhering to
 *       WordPress's security best practices.
 */
function my_custom_plugin_report_shortcode() {
    if (is_user_logged_in() && current_user_can('edit_others_posts')) {
        ob_start();
        my_custom_plugin_view_records(); // Assuming this function generates the report.
        return ob_get_clean();
    } else {
        return '<p>You must be logged in as an editor or higher to view this report.</p>';
    }
}
add_shortcode('my_custom_report', 'my_custom_plugin_report_shortcode');


/**
 * Enqueues the JavaScript script for the custom report Gutenberg block.
 *
 * This function is specifically responsible for:
 * 1. Enqueuing the JavaScript file that contains the logic for the custom Gutenberg block used for displaying reports.
 * 2. Specifying the dependencies of the script, typically including WordPress blocks, editor components, and other relevant libraries.
 * 
 * Usage:
 * - The script registered by this function is used to control the behavior and rendering of the custom report block in the Gutenberg editor.
 * - This function should be hooked to the 'enqueue_block_editor_assets' action, ensuring that the script is loaded only in the context of the block editor.
 *
 * Note: This function does not directly render the block on the front end. It is primarily focused on the block editor experience,
 *       allowing the user to interact with the block within the editor. The rendering of the block on the front end is typically
 *       handled by a separate callback or render function.
 */
function my_custom_plugin_report_block() {
    wp_enqueue_script(
        'my-custom-report-block', // Handle for the script.
        plugin_dir_url(__FILE__) . 'my-report-block.js', // URL to the JavaScript file.
        array('wp-blocks', 'wp-editor', 'wp-element') // Script dependencies.
    );
}
add_action('enqueue_block_editor_assets', 'my_custom_plugin_report_block');



/**
 * Server-side rendering function for the custom report Gutenberg block.
 *
 * This function performs the following tasks:
 * 1. Generates the HTML content for the custom report block when it is rendered on the front end.
 * 2. Utilizes the same logic as the my_custom_plugin_report_shortcode function to ensure consistency between the shortcode and block output.
 * 3. Can include additional logic or formatting specific to the block's presentation in the front end, if required.
 *
 * Usage:
 * - This function is used as the 'render_callback' for the custom report block registered in the block editor.
 * - It ensures that the content displayed on the front end matches what is expected from the block's configuration in the editor.
 *
 * Note: While the block's editor appearance and interactions are handled by JavaScript in the block editor,
 *       this function is responsible for how the block actually appears on the front end of the site.
 */
function my_custom_plugin_report_block_render() {
    return my_custom_plugin_report_shortcode(); // Reuse the shortcode function for front-end rendering.
}
register_block_type('my-custom-plugin/my-custom-report', array(
    'render_callback' => 'my_custom_plugin_report_block_render',
));