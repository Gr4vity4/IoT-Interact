<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

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

function iotInteractButton()
{
    echo file_get_contents("wp-content/plugins/iot-interact/templates/index.html");
}

function iotInteractSettingPage()
{
    echo '<div style="margin-top: 20px"><h2>IoT Interact Settings</h2></div>';
}

function iot_interact_plugin_settings_page()
{
    Container::make('theme_options', __('IoT Interact'))
        ->set_page_parent('options-general.php')
        ->add_fields(array(
            Field::make('html', 'crb_html', __('Section Description'))
                ->set_html(sprintf('<p>Here you can set all the options for using the NETPIE</p>', __('Here, you can add some useful description for the fields below / above this text.'))),
            Field::make('text', 'iot_interact_clientid', 'Client ID')
                ->set_attribute('maxLength', 50),
            Field::make('text', 'iot_interact_token', 'Token')
                ->set_attribute('maxLength', 50),
            Field::make('text', 'iot_interact_secret', 'Secret')
                ->set_attribute('maxLength', 50)
                ->set_attribute('type', 'password'),
        ));
}

function callbackMQTTConfig()
{
    return array(
        'clientid' => get_option('_iot_interact_clientid'),
        'token' => get_option('_iot_interact_token'),
        'secret' => get_option('_iot_interact_secret')
    );
}

// REST API
add_action('rest_api_init', function () {
    register_rest_route('iot-interact/v1', '/mqtt-config', array(
        'methods' => 'GET',
        'callback' => 'callbackMQTTConfig',
    ));
});

wp_enqueue_script('paho_mqtt', plugin_dir_url(__FILE__) . './mqtt/mqttws31.js');
wp_enqueue_script('query_mqtt', plugin_dir_url(__FILE__) . './mqtt/index.js');
add_shortcode('iot_interact_button', 'iotInteractButton');
add_action('carbon_fields_register_fields', 'iot_interact_plugin_settings_page');
