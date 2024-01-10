

# My Custom Plugin Installation and Testing Guide

## Introduction
This guide provides detailed instructions for installing and testing the "My Custom Plugin" for WordPress. This plugin allows you to create, view, search, and delete records in a custom table named 'myrecords'.

## Prerequisites
- A working WordPress installation (version 4.9 or higher).
- Access to the WordPress admin area.
- Basic knowledge of navigating the WordPress dashboard.

## Installation
1. **Download the Plugin:**
   Ensure you have the `.zip` file of "My Custom Plugin".

2. **Upload and Activate:**
   - Navigate to your WordPress dashboard, go to `Plugins > Add New`.
   - Click `Upload Plugin`, and choose the `.zip` file.
   - After the upload, click `Activate`.

3. **Plugin Activation Hook:**
   Upon activation, the plugin automatically creates the 'myrecords' table in your WordPress database and flushes permalink. This is handled by the `register_activation_hook` within the plugin.

## How to use 
The plugin consists of various functionalities as described below:

### Frontend Form Submission
- Create a page/post let's say `Form Page` and access the form by adding the shortcode `[my_custom_frontend_form]` to the page or post.
- The form allows submission of records into the 'myrecords' table.
- AJAX is used for form submission to enhance user experience.
- Each user can submit the form once every 24 hours (cookie-based timer).
- To test multiple submission you can comment the top portion of `my_custom_frontend_form_handle_ajax` function.

### Viewing and Searching Records
- Create A post/page titled "View Records" and access the records by adding the shortcode `[my_custom_report]` to displays all entries.
- You can search records based on items, date ranges, or user IDs.
- The search functionality includes both frontend and backend processing.

### Editing and Deleting Records
- Each record can be edited or deleted from the "View Records" page.
- Editing is handled through AJAX for a seamless experience.
- Bulk deletion is also supported.

### Shortcodes
- `[my_custom_frontend_form]`: Displays the frontend submission form.
- `[my_custom_report]`: Displays the records table (for users with `edit_others_posts` capability).

### Gutenberg Blocks and Widgets
- The plugin includes Gutenberg blocks for easy insertion of the form and report into posts and pages.
- A widget is also available for adding the submission form to sidebars.

## Testing
1. **Form Submission:**
   - Navigate to the page with the form shortcode.
   - Fill in the form and submit.
   - Check the database to confirm that the data has been inserted.

2. **View and Search Records:**
   - Navigate to the page with the report shortcode.
   - Test the search functionality with different criteria.

3. **Edit and Delete Records:**
   - Try editing a record and ensure the changes are reflected.
   - Select multiple records and use the delete function.

4. **Test Shortcodes and Blocks:**
   - Add the provided shortcodes to a page and test their functionality.
   - Test the Gutenberg blocks by adding them to a post or page.

5. **Widget Functionality:**
   - Add the custom form widget to a sidebar and ensure it displays and functions correctly.

## Troubleshooting
- If the table is not created upon activation, deactivate and reactivate the plugin.
- Ensure AJAX requests are correctly formatted and that the correct URL is used.
- Check for JavaScript errors in the browser console if forms or AJAX functionalities are not working.

## Conclusion
This guide should assist you in effectively installing, using, and testing the "My Custom Plugin" in your WordPress environment. For further assistance or customizations, feel free to contact the plugin developer.

