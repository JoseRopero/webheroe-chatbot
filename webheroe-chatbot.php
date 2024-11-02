<?php
/*
Plugin Name: WebHeroe Chatbot
Description: Plugin personalizado para integrar un chatbot con IA en WebHeroe.
Version: 1.2
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
require_once WEBHEROE_CHATBOT_PATH . 'vendor/autoload.php';
require_once WEBHEROE_CHATBOT_PATH . 'includes/chatbot-functions.php';
require_once WEBHEROE_CHATBOT_PATH . 'includes/pdf-handler.php';

/**
 * Encolar scripts y estilos del plugin
 */
if ( ! function_exists( 'webheroe_chatbot_enqueue_assets' ) ) {
    function webheroe_chatbot_enqueue_assets() {
        wp_enqueue_style( 'webheroe-chatbot-css', WEBHEROE_CHATBOT_URL . 'assets/css/chatbot.css', array(), '1.0' );
        wp_enqueue_script( 'webheroe-chatbot-js', WEBHEROE_CHATBOT_URL . 'assets/js/chatbot.js', array( 'jquery' ), '1.0', true );

        // Localizar script para pasar datos de PHP a JS
        wp_localize_script( 'webheroe-chatbot-js', 'webheroe_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'webheroe_chatbot_nonce' )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'webheroe_chatbot_enqueue_assets' );

/**
 * Crear menú de administración para el plugin
 */
if ( ! function_exists( 'webheroe_chatbot_admin_menu' ) ) {
    function webheroe_chatbot_admin_menu() {
        add_menu_page(
            'WebHeroe Chatbot',           // Título de la página
            'Chatbot',                    // Título del menú
            'manage_options',             // Capacidad
            'webheroe-chatbot',           // Slug
            'webheroe_chatbot_admin_page',// Función de callback
            'dashicons-format-chat',      // Icono
            6                             // Posición
        );

        // Submenús
        add_submenu_page(
            'webheroe-chatbot',
            'Configuración',
            'Configuración',
            'manage_options',
            'webheroe-chatbot-settings',
            'webheroe_chatbot_settings_page'
        );
    }
}
add_action( 'admin_menu', 'webheroe_chatbot_admin_menu' );

/**
 * Página principal de administración del plugin
 */
if ( ! function_exists( 'webheroe_chatbot_admin_page' ) ) {
    function webheroe_chatbot_admin_page() {
        echo '<div class="wrap"><h1>WebHeroe Chatbot - Administración</h1><p>Bienvenido a la administración del plugin WebHeroe Chatbot.</p></div>';
    }
}

/**
 * Página de configuración del plugin
 */
if ( ! function_exists( 'webheroe_chatbot_settings_page' ) ) {
    function webheroe_chatbot_settings_page() {
        if ( isset( $_POST['guardar_configuracion'] ) ) {
            // Validar y sanitizar entradas
            $embeddings_api_key = isset($_POST['embeddings_api_key']) ? sanitize_text_field( $_POST['embeddings_api_key'] ) : '';
            $pinecone_api_key = isset($_POST['pinecone_api_key']) ? sanitize_text_field( $_POST['pinecone_api_key'] ) : '';
            $pinecone_index = isset($_POST['pinecone_index']) ? sanitize_text_field( $_POST['pinecone_index'] ) : '';
            $pinecone_host = isset($_POST['pinecone_host']) ? sanitize_text_field( $_POST['pinecone_host'] ) : '';
            $elasticsearch_url = isset($_POST['elasticsearch_url']) ? sanitize_text_field( $_POST['elasticsearch_url'] ) : '';
            $groq_api_key = isset($_POST['groq_api_key']) ? sanitize_text_field( $_POST['groq_api_key'] ) : '';
            $embedding_dims = isset($_POST['embedding_dims']) ? intval( $_POST['embedding_dims'] ) : 1536;

            // Actualizar opciones
            update_option( 'webheroe_chatbot_embeddings_api_key', $embeddings_api_key );
            update_option( 'webheroe_chatbot_pinecone_api_key', $pinecone_api_key );
            update_option( 'webheroe_chatbot_pinecone_index', $pinecone_index );
            update_option( 'webheroe_chatbot_pinecone_host', $pinecone_host );
            update_option( 'webheroe_chatbot_elasticsearch_url', $elasticsearch_url );
            update_option( 'webheroe_chatbot_groq_api_key', $groq_api_key );
            update_option( 'webheroe_chatbot_embedding_dims', $embedding_dims );

            echo '<div class="updated"><p>Configuración guardada correctamente.</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Configuración de WebHeroe Chatbot</h1>
            <form method="post">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Clave API de OpenAI</th>
                        <td><input type="text" name="embeddings_api_key" value="<?php echo esc_attr( get_option('webheroe_chatbot_embeddings_api_key') ); ?>" size="50" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Clave API de Pinecone</th>
                        <td><input type="text" name="pinecone_api_key" value="<?php echo esc_attr( get_option('webheroe_chatbot_pinecone_api_key') ); ?>" size="50" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Nombre del Índice en Pinecone</th>
                        <td><input type="text" name="pinecone_index" value="<?php echo esc_attr( get_option('webheroe_chatbot_pinecone_index') ); ?>" size="50" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Host de Pinecone</th>
                        <td><input type="text" name="pinecone_host" value="<?php echo esc_attr( get_option('webheroe_chatbot_pinecone_host') ); ?>" size="50" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">URL de Elasticsearch (Bonsai)</th>
                        <td><input type="text" name="elasticsearch_url" value="<?php echo esc_attr( get_option('webheroe_chatbot_elasticsearch_url') ); ?>" size="50" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Clave API de Groq</th>
                        <td><input type="text" name="groq_api_key" value="<?php echo esc_attr( get_option('webheroe_chatbot_groq_api_key') ); ?>" size="50" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Dimensiones del Embedding</th>
                        <td><input type="number" name="embedding_dims" value="<?php echo esc_attr( get_option('webheroe_chatbot_embedding_dims', 1536 ) ); ?>" size="50" required min="1" /></td>
                    </tr>
                </table>
                <?php submit_button( 'Guardar Configuración', 'primary', 'guardar_configuracion' ); ?>
            </form>
        </div>
        <?php
    }
}

/**
 * Registrar configuraciones del plugin
 */
if ( ! function_exists( 'webheroe_chatbot_register_settings' ) ) {
    function webheroe_chatbot_register_settings() {
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_embeddings_api_key' );
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_pinecone_api_key' );
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_pinecone_index' );
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_pinecone_host' );
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_elasticsearch_url' );
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_groq_api_key' );
        register_setting( 'webheroe_chatbot_settings', 'webheroe_chatbot_embedding_dims' );
    }
}
add_action( 'admin_init', 'webheroe_chatbot_register_settings' );

/**
 * Shortcode para el chatbot
 */
if ( ! function_exists( 'webheroe_chatbot_shortcode' ) ) {
    function webheroe_chatbot_shortcode() {
        ob_start();
        ?>
        <div id="webheroe-chatbot">
            <div id="chat-window">
                <div id="chat-log"></div>
                <input type="text" id="chat-input" placeholder="Escribe tu pregunta...">
                <button id="chat-submit">Enviar</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
add_shortcode( 'webheroe_chatbot', 'webheroe_chatbot_shortcode' );
