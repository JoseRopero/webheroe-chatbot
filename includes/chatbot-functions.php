<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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

        add_submenu_page(
            'webheroe-chatbot',
            'Gestionar PDFs',
            'Gestionar PDFs',
            'manage_options',
            'webheroe-chatbot-pdfs',
            'webheroe_chatbot_admin_page_pdfs'
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

/**
 * Manejar la respuesta del chatbot mediante AJAX
 */
if ( ! function_exists( 'webheroe_chatbot_response' ) ) {
    function webheroe_chatbot_response(){
        // Verificamos nonce para seguridad
        if ( ! check_ajax_referer( 'webheroe_chatbot_nonce', 'nonce', false ) ) {
            error_log( 'Nonce de seguridad no válido.' );
            wp_send_json_error( 'Nonce de seguridad no válido.' );
        }

        // Verificamos que se haya enviado el mensaje
        if( ! isset( $_POST['message'] ) ){
            error_log( 'Mensaje no recibido.' );
            wp_send_json_error( 'Mensaje no recibido.' );
        }

        // Sanitizar el mensaje recibido para prevenir inyecciones de código malicioso
        $user_message = sanitize_text_field( $_POST['message'] );
        error_log( 'Mensaje del usuario: ' . $user_message );

        // Obtenemos las configuraciones necesarias
        $groq_api_key = get_option( 'webheroe_chatbot_groq_api_key' );
        $embeddings_api_key = get_option( 'webheroe_chatbot_embeddings_api_key' );
        $elasticsearch_url = get_option( 'webheroe_chatbot_elasticsearch_url' );
        $embedding_dims = get_option( 'webheroe_chatbot_embedding_dims', 1536 );
        error_log( 'Configuraciones obtenidas.' );

        // Validar configuraciones
        if ( empty( $groq_api_key ) || empty( $embeddings_api_key ) || empty( $elasticsearch_url ) ) {
            error_log( 'Configuraciones incompletas del plugin.' );
            wp_send_json_error( 'Configuraciones incompletas del plugin.' );
        }

        // Generar embedding usando OpenAI
        $embedding = generar_embedding_openai( $user_message, $embeddings_api_key );
        if ( ! $embedding ) {
            error_log( 'Error al generar el embedding.' );
            wp_send_json_error( 'Error al generar el embedding.' );
        }
        error_log( 'Embedding generado correctamente.' );

        // Obtener configuraciones de Pinecone
        $pinecone_api_key = get_option( 'webheroe_chatbot_pinecone_api_key' );
        $pinecone_index = get_option( 'webheroe_chatbot_pinecone_index' );
        $pinecone_host = get_option( 'webheroe_chatbot_pinecone_host' );
        error_log( 'Configuraciones de Pinecone obtenidas.' );

        if ( empty( $pinecone_api_key ) || empty( $pinecone_index ) || empty( $pinecone_host ) ) {
            error_log( 'Configuraciones de Pinecone incompletas.' );
            wp_send_json_error( 'Configuraciones de Pinecone incompletas.' );
        }

        // Generar un ID único para el embedding
        $id = uniqid( 'doc_' );
        error_log( 'ID generado: ' . $id );

        // Insertar el embedding en Pinecone
        $indexado_pinecone = guardar_embedding_pinecone( $id, $embedding, $pinecone_api_key, $pinecone_host );
        if ( !$indexado_pinecone ) {
            error_log( 'Error al guardar embedding en Pinecone.' );
            wp_send_json_error( 'Error al guardar embedding en Pinecone.' );
        }
        error_log( 'Embedding guardado en Pinecone.' );

        // Indexar el documento en Elasticsearch
        $contenido = $user_message; // Puedes ajustar esto según tus necesidades
        $indexado_elasticsearch = indexar_documento_en_elasticsearch( $contenido, $embedding, $id, $elasticsearch_url );
        if ( !$indexado_elasticsearch ) {
            error_log( 'Error al indexar el documento en Elasticsearch.' );
            wp_send_json_error( 'Error al indexar el documento en Elasticsearch.' );
        }
        error_log( 'Documento indexado en Elasticsearch.' );

        // Buscar embeddings similares en Pinecone
        $matches = buscar_similares_pinecone( $embedding, 5, $pinecone_api_key, $pinecone_host );
        if ( !$matches ) {
            error_log( 'Error al buscar información en Pinecone.' );
            wp_send_json_error( 'Error al buscar información en Pinecone.' );
        }
        error_log( 'Embeddings similares encontrados en Pinecone.' );

        // Recuperar documentos desde Elasticsearch
        $resultados = array();
        foreach ( $matches as $match ) {
            $id = $match['id'];
            $documento = obtener_documento_desde_elasticsearch( $id, $elasticsearch_url );
            if ( $documento ) {
                $resultados[] = $documento;
                error_log( 'Documento recuperado: ' . print_r( $documento, true ) );
            }
        }

        // Generar respuesta usando la API de Groq
        $contexto = implode( "\n", array_column( $resultados, 'contenido' ) );
        $chatbot_response = generar_respuesta_groq( $user_message, $contexto, $groq_api_key );
        if ( ! $chatbot_response ) {
            error_log( 'Error al generar la respuesta del chatbot.' );
            wp_send_json_error( 'Error al generar la respuesta del chatbot.' );
        }
        error_log( 'Respuesta del chatbot generada correctamente.' );

        // Devolvemos la respuesta al frontend
        wp_send_json_success( array( 'reply' => $chatbot_response ) );
    }
}
add_action( 'wp_ajax_webheroe_chatbot_response', 'webheroe_chatbot_response' ); // Solicitudes AJAX por usuarios autenticados 
add_action( 'wp_ajax_nopriv_webheroe_chatbot_response', 'webheroe_chatbot_response' );  // Solicitudes usuarios no autenticados

/**
 * Generar embedding usando la API de OpenAI
 */
if ( ! function_exists( 'generar_embedding_openai' ) ) {
    function generar_embedding_openai( $texto, $embeddings_api_key ) {
        // Endpoint de la API de OpenAI para generar embeddings
        $url = 'https://api.openai.com/v1/embeddings';

        // Datos a enviar
        $data = array(
            'model' => 'text-embedding-ada-002', // Modelo recomendado por OpenAI
            'input' => $texto
        );

        // Inicializar cURL
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $embeddings_api_key
        ));
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // Retornar la respuesta como string
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true ); // Verificar el certificado SSL

        // Ejecutar la solicitud
        $respuesta = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'Error en cURL (OpenAI Embeddings): ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }

        // Obtener el código de respuesta HTTP
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        // Verificar si la solicitud fue exitosa
        if ( $http_code !== 200 ) {
            error_log( 'Respuesta HTTP no exitosa: ' . $http_code );
            return false;
        }

        // Decodificar la respuesta JSON
        $resultado = json_decode( $respuesta, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'Error al decodificar JSON: ' . json_last_error_msg() );
            return false;
        }

        if ( isset( $resultado['data'][0]['embedding'] ) ) {
            return $resultado['data'][0]['embedding'];
        }

        error_log( 'El campo embedding no está presente en la respuesta.' );
        return false;
    }
}

/**
 * Indexar documento en Elasticsearch (Bonsai)
 */
if ( ! function_exists( 'indexar_documento_en_elasticsearch' ) ) {
    function indexar_documento_en_elasticsearch( $contenido, $embedding, $id, $elasticsearch_url ) {
        $index = 'embeddings'; // Asegúrate de que el índice exista y tenga el mapeo correcto

        $documento = array(
            'contenido' => $contenido,
            'embedding' => $embedding
        );

        // Inicializar cURL
        $ch = curl_init( "$elasticsearch_url/$index/_doc/$id" );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $documento ) );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        // Ejecutar la solicitud
        $respuesta = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'Error en cURL (Indexar Elasticsearch): ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }
        curl_close( $ch );

        // Decodificar la respuesta
        $resultado = json_decode( $respuesta, true );
        if ( isset( $resultado['result'] ) && in_array( $resultado['result'], array( 'created', 'updated' ) ) ) {
            return true;
        }

        return false;
    }
}

/**
 * Buscar embeddings similares en Pinecone
 *
 * @param array $vector Vector de embedding de la consulta.
 * @param int $top_k Número de resultados similares a recuperar.
 * @param string $pinecone_api_key Tu clave API de Pinecone.
 * @param string $pinecone_host El host de Pinecone proporcionado.
 * @return array|false Lista de matches si exitoso, False en caso contrario.
 */
if ( ! function_exists( 'buscar_similares_pinecone' ) ) {
    function buscar_similares_pinecone( $vector, $top_k, $pinecone_api_key, $pinecone_host ) {
        $url = $pinecone_host . "/query";

        $data = array(
            "vector" => $vector,
            "topK" => $top_k,
            "includeValues" => false,
            "includeMetadata" => true
        );

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Api-Key: ' . $pinecone_api_key
        ));
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $respuesta = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'Error en cURL (Pinecone Query): ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }
        curl_close( $ch );

        $resultado = json_decode( $respuesta, true );
        return isset( $resultado['matches'] ) ? $resultado['matches'] : false;
    }
}

/**
 * Obtener documento desde Elasticsearch (Bonsai) por ID
 *
 * @param string $id Identificador único del documento.
 * @param string $elasticsearch_url URL de Elasticsearch.
 * @return array|false Documento si existe, False en caso contrario.
 */
if ( ! function_exists( 'obtener_documento_desde_elasticsearch' ) ) {
    function obtener_documento_desde_elasticsearch( $id, $elasticsearch_url ) {
        $index = 'embeddings'; // Asegúrate de que el índice sea correcto
        $ch = curl_init( "$elasticsearch_url/$index/_doc/$id?pretty" );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $respuesta = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'Error en cURL (Obtener Documento): ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }
        curl_close( $ch );

        $resultado = json_decode( $respuesta, true );
        if ( isset( $resultado['_source'] ) ) {
            return $resultado['_source'];
        }

        return false;
    }
}

/**
 * Generar respuesta usando la API de Groq
 */
if ( ! function_exists( 'generar_respuesta_groq' ) ) {
    function generar_respuesta_groq( $mensaje_usuario, $contexto, $groq_api_key ) {
        // Endpoint de la API de Groq para generar respuestas de chat
        $url = 'https://api.groq.com/openai/v1/chat/completions'; // Asegúrate de que este sea el endpoint correcto

        // Datos a enviar
        $data = array(
            'model' => 'llama3-groq-70b-8192-tool-use-preview',
            'messages' => array(
                array( 'role' => 'system', 'content' => 'Eres un asistente útil.' ),
                array( 'role' => 'user', 'content' => $mensaje_usuario ),
                array( 'role' => 'system', 'content' => $contexto )
            ),
            'max_tokens' => 150,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        );

        // Inicializar cURL
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $groq_api_key
        ));
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        // Ejecutar la solicitud
        $respuesta = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'Error en cURL (Groq API): ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }

        // Obtener el código de respuesta HTTP
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        // Registrar la respuesta completa para depuración
        error_log( 'Respuesta de Groq API: ' . $respuesta );

        // Verificar si la solicitud fue exitosa
        if ( $http_code !== 200 ) {
            error_log( 'Respuesta HTTP no exitosa de Groq API: ' . $http_code );
            return false;
        }

        // Decodificar la respuesta JSON
        $resultado = json_decode( $respuesta, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'Error al decodificar JSON de Groq API: ' . json_last_error_msg() );
            return false;
        }

        if ( isset( $resultado['choices'][0]['message']['content'] ) ) {
            return $resultado['choices'][0]['message']['content'];
        }

        // Registrar si el campo esperado no está presente
        error_log( 'El campo "choices[0]["message"]["content"]" no está presente en la respuesta de Groq API.' );
        return false;
    }
}

/**
 * Guardar embedding en Pinecone
 *
 * @param string $id Identificador único para el documento.
 * @param array $vector Vector de embedding generado.
 * @param string $pinecone_api_key Tu clave API de Pinecone.
 * @param string $pinecone_host El host de Pinecone proporcionado.
 * @return bool True si la operación fue exitosa, False en caso contrario.
 */
if ( ! function_exists( 'guardar_embedding_pinecone' ) ) {
    function guardar_embedding_pinecone( $id, $vector, $pinecone_api_key, $pinecone_host ) {
        $url = $pinecone_host . "/vectors/upsert";

        $data = array(
            "vectors" => array(
                array(
                    "id" => $id,
                    "values" => $vector
                )
            )
        );

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Api-Key: ' . $pinecone_api_key
        ));
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $respuesta = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'Error en cURL (Pinecone Upsert): ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }
        curl_close( $ch );

        $resultado = json_decode( $respuesta, true );
        return isset( $resultado['upsertedCount'] ) && $resultado['upsertedCount'] > 0;
    }
}

/**
 * Función para reindexar un documento, añadiendo embedding a Elasticsearch y Pinecone
 *
 * @param string $contenido Contenido del documento.
 * @param string $id ID único del documento.
 * @return bool True si todo fue exitoso, False en caso contrario.
 */
if ( ! function_exists( 'indexar_documento' ) ) {
    function indexar_documento( $contenido, $id ) {
        // Generar embedding con OpenAI
        $embeddings_api_key = get_option( 'webheroe_chatbot_embeddings_api_key' );
        $embedding = generar_embedding_openai( $contenido, $embeddings_api_key );
        if ( !$embedding ) {
            return false;
        }

        // Obtener configuraciones de Pinecone
        $pinecone_api_key = get_option( 'webheroe_chatbot_pinecone_api_key' );
        $pinecone_index = get_option( 'webheroe_chatbot_pinecone_index' );
        $pinecone_host = get_option( 'webheroe_chatbot_pinecone_host' );

        if ( empty( $pinecone_api_key ) || empty( $pinecone_index ) || empty( $pinecone_host ) ) {
            return false;
        }

        // Guardar en Pinecone
        $indexado_pinecone = guardar_embedding_pinecone( $id, $embedding, $pinecone_api_key, $pinecone_host );
        if ( !$indexado_pinecone ) {
            return false;
        }

        // Indexar en Elasticsearch
        $elasticsearch_url = get_option( 'webheroe_chatbot_elasticsearch_url' );
        $indexado_elasticsearch = indexar_documento_en_elasticsearch( $contenido, $embedding, $id, $elasticsearch_url );
        if ( !$indexado_elasticsearch ) {
            return false;
        }

        return true;
    }
}

