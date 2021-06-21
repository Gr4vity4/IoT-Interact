<?php
/**
 * @package IoT_Interact
 * @version 1.0.0
 */
/*
Plugin Name: IoT Interact
Description: Powerful interact internet of things with user!
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

wp_enqueue_style('tailwind', '/wp-content/plugins/iot-interact/templates/assets/css/style.min.css');
wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
wp_enqueue_script('paho_mqtt', '/wp-content/plugins/iot-interact/mqtt/mqttws31.js');
wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js');
wp_enqueue_script('vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.14/vue.min.js');

add_filter('woocommerce_account_menu_items', 'iot_interact_dashboard_account_link', 40);
function iot_interact_dashboard_account_link($menu_links)
{
    $menu_links = array_slice($menu_links, 0, 0, true)
        + array('iot-interact-dashboard' => 'NETPIE')
        + array_slice($menu_links, 0, NULL, true);

    return $menu_links;
}

function iot_interact_my_account_endpoint_content()
{
    echo file_get_contents("wp-content/plugins/iot-interact/templates/dashboard.html");
}

add_action('woocommerce_account_iot-interact-dashboard_endpoint', 'iot_interact_my_account_endpoint_content');

