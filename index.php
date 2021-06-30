<?php
/**
 * @package IoT_Interact
 * @version 1.0.0
 */
/*
Plugin Name: WP NETPIE
Description: Powerful MQTT connectivity protocol.
Author: Prawared Bowonphattharawadi
Version: 1.0.0
*/

// REST API
function  save_netpie_endpoint(WP_REST_Request $req) {
    global $user_id;
    $body = $req->get_body();
    $body = json_decode($body, true);
    
    if (!get_user_meta($user_id, 'netpie_client_id')) {
        // write new user_meta
        add_user_meta($user_id, 'netpie_client_id', $body['client_id'], true);
        add_user_meta($user_id, 'netpie_token', $body['token'], true);
        add_user_meta($user_id, 'netpie_secret', $body['secret'], true);
    } else {
        // update user_meta
        update_user_meta($user_id, 'netpie_client_id', $body['client_id'], true);
        update_user_meta($user_id, 'netpie_token', $body['token'], true);
        update_user_meta($user_id, 'netpie_secret', $body['secret'], true);
    }
    
    return rest_ensure_response( 'save netpie config successfully.' );
}

function  load_netpie_endpoint() {
    global $user_id;
    $config = array(
        'client_id' => get_user_meta($user_id, 'netpie_client_id'),
        'token' => get_user_meta($user_id, 'netpie_token'),
        'secret' => get_user_meta($user_id, 'netpie_secret')
    );
    
    return rest_ensure_response(json_encode($config));
}

function pluginInit(){
    global $user_id;
    $user_id = get_current_user_id();
}

add_action('init', 'pluginInit');
    
add_action( 'rest_api_init', function () {
    register_rest_route( 'netpie/v1', '/save/', array(
        'methods' => 'POST',
        'callback' => 'save_netpie_endpoint'
    ));
    register_rest_route( 'netpie/v1', '/load/', array(
        'methods' => 'GET',
        'callback' => 'load_netpie_endpoint'
    ));
});

wp_enqueue_style('tailwind', '/wp-content/plugins/wp-netpie-plugin/templates/assets/css/style.min.css');
wp_enqueue_script('paho_mqtt', '/wp-content/plugins/wp-netpie-plugin/templates/assets/js/mqtt/mqttws31.js');
wp_enqueue_script('moment', '/wp-content/plugins/wp-netpie-plugin/templates/assets/js/moment.min.js');
wp_enqueue_script('chartjs', '/wp-content/plugins/wp-netpie-plugin/templates/assets/js/chart.min.js');
wp_enqueue_script('vue', '/wp-content/plugins/wp-netpie-plugin/templates/assets/js/vue.min.js');

function netpie_dashboard_account_link($menu_links)
{
    $menu_links = array_slice($menu_links, 0, 0, true)
        + array('netpie-dashboard' => 'NETPIE')
        + array_slice($menu_links, 0, NULL, true);

    return $menu_links;
}
add_filter('woocommerce_account_menu_items', 'netpie_dashboard_account_link', 40);

function netpie_my_account_endpoint_content()
{
    echo file_get_contents("wp-content/plugins/wp-netpie-plugin/templates/dashboard.html");
}

add_action('woocommerce_account_netpie-dashboard_endpoint', 'netpie_my_account_endpoint_content');