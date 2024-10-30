<?php 
function sqp_enqueue_admin_scripts() {
    wp_enqueue_style('sqp-admin-styles', plugin_dir_url(__FILE__) . '../assets/css/admin-styles.css');
    wp_enqueue_script('sqp-admin-scripts', plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js', array('jquery'), false, true);
}
?>