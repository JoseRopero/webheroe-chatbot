<?php
/*
Plugin Name: WebHeroe Chatbot
Description: Plugin personalizado para integrar un chatbot con IA en WebHeroe.
Version: 1.0
Author: Jose Manuel Ropero
Text Domain: webheroe-chatbot
Domain Path: /languages
*/

 // Evitar acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definir constantes para rutas y URLs del plugin
define( 'WEBHEROE_CHATBOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WEBHEROE_CHATBOT_URL', plugin_dir_url( __FILE__ ) );

// Incluir archivos esenciales del plugin
require_once WEBHEROE_CHATBOT_PATH . 'includes/chatbot-functions.php';
require_once WEBHEROE_CHATBOT_PATH . 'includes/pdf-handler.php';

// Encolar scripts y estilos
//add_action( 'wp_enqueue_scripts', 'webheroe_chatbot_enqueue_assets' );

// Crear menús en el panel de administración
//add_action( 'admin_menu', 'webheroe_chatbot_admin_menu' );

// Registrar configuraciones del plugin
//add_action( 'admin_init', 'webheroe_chatbot_register_settings' );

// Agregar shortcode para el chatbot
//add_shortcode( 'webheroe_chatbot', 'webheroe_chatbot_shortcode' );
