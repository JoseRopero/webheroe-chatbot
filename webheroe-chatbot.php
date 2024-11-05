<?php
/*
Plugin Name: WebHeroe Chatbot
Description: Plugin personalizado para integrar un chatbot con IA en WebHeroe.
Version: 2.0
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
        //Incluir Bootstrap
        wp_enqueue_style( 'bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' );
        wp_enqueue_style( 'webheroe-chatbot-css', WEBHEROE_CHATBOT_URL . 'assets/css/chatbot.css', array(), '1.0' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', array('jquery'), null, true );

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
        echo '<div class="wrap">';
        echo '<h1>Documentación del Plugin WebHeroe Chatbot</h1>';
        
        // Aviso sobre Composer
        echo '<h2>Instalación de Composer</h2>';
        echo '<p>Antes de activar este plugin, es necesario que instales Composer en tu servidor o en local. Composer es un gestor de dependencias para PHP que permite gestionar las librerías que usa tu proyecto.</p>';
        echo '<h3>Pasos para instalar Composer:</h3>';
        echo '<h4>Si accedes desde tu servidor:</h4>';
        echo '<ol>
                <li>Accede a tu servidor por FTP.</li>
                <li>Descarga Composer desde su <a href="https://getcomposer.org/download/" target="_blank">página oficial</a>.</li>
                <li>Sube el archivo <code>composer.phar</code> al directorio raíz de tu proyecto.</li>
                <li>Desde la línea de comandos, navega a la carpeta del plugin y ejecuta: <code>php composer.phar install</code>.</li>
              </ol>';
        echo '<h4>Si trabajas en Local (como LocalWP):</h4>';
        echo '<ol>';
        echo '<li><strong>Descargar Composer</strong>: Ve al sitio oficial de Composer: <a href="https://getcomposer.org/download/">getcomposer.org</a> y sigue las instrucciones para descargar Composer.</li>';
        echo '<li><strong>Instalar Composer</strong>: Abre la terminal de tu sistema operativo, navega a la carpeta de tu proyecto LocalWP y ejecuta los siguientes comandos:';
        echo '<pre>';
        echo 'php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"'.PHP_EOL;
        echo 'php -r "if (hash_file(\'sha384\', \'composer-setup.php\') === \'9b5de7d80fa8e8f4b01ec71c39dc679bc3e0a7f9d07b67348dd6ee4799d70a77c167af2c92bc215f7ee8d1d3c7a7605e\') { echo \'Installer verified\'; } else { echo \'Installer corrupt\'; unlink(\'composer-setup.php\'); } echo PHP_EOL;"'.PHP_EOL;
        echo 'php composer-setup.php'.PHP_EOL;
        echo 'php -r "unlink(\'composer-setup.php\');"';
        echo '</pre></li>';
        echo '<li><strong>Verificar la Instalación</strong>: Aún en la terminal, ejecuta el siguiente comando para verificar que Composer se ha instalado correctamente:';
        echo '<pre>composer --version</pre></li>';
        echo '</ol>';
        // Instrucciones para obtener las API Keys
        echo '<h2>Instrucciones para obtener las API Keys</h2>';
        echo '<p>A continuación se detallan los pasos para obtener las claves API necesarias:</p>';
        echo '<h3>1. Clave API de OpenAI</h3>';
        echo '<p>Visita la <a href="https://platform.openai.com/signup" target="_blank">página de OpenAI</a> y crea una cuenta. Luego, en tu panel, busca la sección de API keys y genera una nueva clave.</p>';
        echo '<p>Copia la clave y pégala en la sección de configuración del plugin.</p>';
    
        echo '<h3>2. Clave API de Pinecone</h3>';
        echo '<p>Regístrate en <a href="https://www.pinecone.io/" target="_blank">Pinecone</a>. Después de crear una cuenta, podrás encontrar la API key en tu perfil o en la sección de API keys.</p>';
        echo '<p>Guarda la clave en la configuración del plugin.</p>';
    
        echo '<h3>3. Clave API de Groq</h3>';
        echo '<p>Visita la <a href="https://www.groq.com/" target="_blank">página de Groq</a> y sigue el proceso de registro para obtener tu clave API.</p>';
        echo '<p>Pega la clave en la configuración del plugin.</p>';
        // Instrucciones para crear el índice en Pinecone
        echo '<h2>Creación del Índice en Kibana</h2>';
        echo '<p>Para utilizar el plugin, debes crear un índice en <code>Kibana</code> llamado <strong>"embedding"</strong>:</p>';
        echo '<h3>Pasos para crear el índice:</h3>';
        echo '<ol>';
        echo '<li>Accede a tu cuenta de <code>Bonsai</code> y dirígete a <code>Kibana</code></li>';
        echo '<li>En Kibana, ve a la sección de <code>Dev Tools</code>.</li>';
        echo '<li>Para crear un índice llamado <code>embedding</code>, ejecuta el siguiente comando en la consola de Dev Tools:</li>';
        echo '<pre>';
        echo 'PUT /embedding'.PHP_EOL;
        echo '{'.PHP_EOL;
        echo ' "settings": {'.PHP_EOL;
        echo '   "number_of_shards": 1,'.PHP_EOL;
        echo '   "number_of_replicas": 1'.PHP_EOL;
        echo ' },'.PHP_EOL;
        echo ' "embedding": {'.PHP_EOL;
        echo '   "properties": {'.PHP_EOL;
        echo '     "text": {'.PHP_EOL;
        echo '       "type": "text"'.PHP_EOL;
        echo '     },'.PHP_EOL;
        echo '     "metadata": {'.PHP_EOL;
        echo '       "type": "object"'.PHP_EOL;
        echo '     }'.PHP_EOL;
        echo '   }'.PHP_EOL;
        echo ' }'.PHP_EOL;
        echo '}'.PHP_EOL;
        echo '</pre>';
        echo '</ol>';
        echo '<h3>4. Host de Pinecone</h3>';
        echo '<p>En el panel de Pinecone, encontrarás un campo que indica la URL del host.</p>';
        echo '<p>Copia esta URL y pégala en la configuración del plugin.</p>';
        echo '<h3>5. URL de Elasticsearch (Bonsai)</h3>';
        echo '<p>Regístrate en <a href="https://bonsai.io/" target="_blank">Bonsai</a> y crea un clúster de Elasticsearch.</p>';
        echo '<p>Una vez creado, obtendrás la URL del clúster.</p>';
        echo '<p>Copia esta URL y colócala en la configuración del plugin.</p>';
        echo '<h2>Nota Importante</h2>';
        echo '<p>Si no has instalado Composer, el plugin no funcionará correctamente. Por favor, asegúrate de completar todos los pasos anteriores antes de activar el plugin.</p>';
        echo '</div>';
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
        if ( ! is_admin() ) { // Asegúrate de que esto solo se ejecute en el front-end
            error_log( 'Shortcode ejecutado correctamente.' );
            ob_start();
            include( WEBHEROE_CHATBOT_PATH . 'templates/chatbot-template.php' ); // Cargamos la plantilla para el chatbot
            return ob_get_clean();
        }
    }
}
add_shortcode( 'webheroe_chatbot', 'webheroe_chatbot_shortcode' );

