<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Smalot\PdfParser\Parser;

/**
 * Hook para procesar PDFs subidos a través de la Biblioteca de Medios.
 */
add_action( 'add_attachment', 'webheroe_chatbot_process_uploaded_pdf' );

function webheroe_chatbot_process_uploaded_pdf( $attachment_id ) {
    // Obtener la información del adjunto
    $attachment = get_post( $attachment_id );
    $mime_type = get_post_mime_type( $attachment );

    // Verificar si el archivo es un PDF
    if ( $mime_type !== 'application/pdf' ) {
        return; // No es un PDF, salir de la función
    }

    error_log( "Procesando PDF subido: ID $attachment_id, Título: " . $attachment->post_title );

    // Obtener la ruta del archivo
    $file_path = get_attached_file( $attachment_id );
    if ( ! file_exists( $file_path ) ) {
        error_log( "El archivo PDF no existe en la ruta especificada: $file_path" );
        return;
    }

    // Extraer texto del PDF
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile( $file_path );
        $text = $pdf->getText();
        error_log( "Texto completo extraído del PDF: " . substr( $text, 0, 500 ) ); // Muestra los primeros 500 caracteres
    } catch ( Exception $e ) {
        error_log( "Error al procesar el PDF (ID $attachment_id): " . $e->getMessage() );
        return;
    }

    // Limpiar el texto extraído
    $text = preg_replace('/\s+/', ' ', $text); // Reemplaza múltiples espacios por uno solo
    $text = trim( $text ); // Elimina espacios al inicio y final

    // Usar el texto completo para generar embedding
    $embeddings_api_key = get_option( 'webheroe_chatbot_embeddings_api_key' );
    if ( empty( $embeddings_api_key ) ) {
        error_log( "Clave API de OpenAI no configurada. ID: $attachment_id");
        return; // No continuar si la clave no está configurada
    }

    // Generar embedding usando el texto completo
    $embedding = generar_embedding_openai_con_reintentos( $text, $embeddings_api_key );
    if ( ! $embedding ) {
        error_log( "Error al generar el embedding para el PDF: ID $attachment_id" );
        return; // No continuar si hubo un error en la generación del embedding
    }

    // Loguear los primeros 10 valores del embedding
    $embedding_preview = implode( ', ', array_slice( $embedding, 0, 10 ) );
    error_log( "Embedding generado exitosamente para ID $attachment_id. Primeros valores: " . $embedding_preview );

    // Generar un ID único para el documento sin caracteres acentuados
    $sanitized_title = strtolower( remove_accents_custom( str_replace( array(' ', '.'), '_', $attachment->post_title ) ) );
    $doc_id = 'pdf_' . $sanitized_title . '_' . uniqid();

    // Obtener configuraciones de Pinecone
    $pinecone_api_key = get_option( 'webheroe_chatbot_pinecone_api_key' );
    $pinecone_index = get_option( 'webheroe_chatbot_pinecone_index' );
    $pinecone_host = get_option( 'webheroe_chatbot_pinecone_host' );

    if ( empty( $pinecone_api_key ) || empty( $pinecone_index ) || empty( $pinecone_host ) ) {
        error_log( "Configuraciones de Pinecone incompletas para ID: $attachment_id");
        return; // No continuar si hay configuraciones incompletas
    }

    // Definir metadatos
    $metadata = array(
        'title' => $attachment->post_title,
        'url' => wp_get_attachment_url( $attachment_id ),
        'uploaded_at' => current_time( 'mysql' ),
        'type' => 'pdf' // Añadir tipo para filtrado futuro
    );

    // Guardar embedding en Pinecone
    $pinecone_result = guardar_embedding_pinecone( 
        $doc_id, 
        $embedding, 
        $pinecone_api_key, 
        $pinecone_index, 
        $pinecone_host, 
        $metadata 
    );
    if ( ! $pinecone_result ) {
        error_log( "Error al guardar el embedding en Pinecone para ID: $doc_id");
        return; // No continuar si hubo un error al guardar en Pinecone
    }
    error_log( "Embedding guardado en Pinecone para ID $doc_id." );

    // Indexar en Elasticsearch
    $elasticsearch_url = get_option( 'webheroe_chatbot_elasticsearch_url' );
    if ( empty( $elasticsearch_url ) ) {
        error_log( "URL de Elasticsearch no configurada para ID: $doc_id");
        return; // No continuar si la URL no está configurada
    }

    $indexado_elasticsearch = indexar_documento_en_elasticsearch( $text, $embedding, $doc_id, $elasticsearch_url, $metadata );
    if ( ! $indexado_elasticsearch ) {
        error_log( "Error al indexar el documento en Elasticsearch para ID: $doc_id");
        return; // No continuar si hubo un error al indexar
    }
    error_log( "Documento indexado en Elasticsearch para ID $doc_id." );
}


/**
 * Dividir el texto en secciones por miembro del equipo
 * 
 * @param string $text Texto completo extraído del PDF.
 * @return array Arreglo asociativo donde la clave es el nombre del miembro y el valor es su descripción.
 */
/*
if ( ! function_exists( 'split_text_into_sections' ) ) {
    function split_text_into_sections( $text ) {
        $sections = array();

        // Definir los nombres de los miembros del equipo
        $team_members = array(
            'Ana Patricia G. C.',
            'Álvaro Torres'
        );

        // Normalizar el texto
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim( $text );

        foreach ( $team_members as $index => $member ) {
            if ($index < count($team_members) - 1) {
                $next_member = preg_quote( $team_members[$index + 1], '/' );
                $pattern = '/'. preg_quote( $member, '/' ) .'\s*(.*?)\s*(?='.$next_member.'|$)/is';
            } else {
                $pattern = '/'. preg_quote( $member, '/' ) .'\s*(.*)/is';
            }

            if ( preg_match( $pattern, $text, $matches ) ) {
                $sections[ $member ] = trim( $matches[1] );
            } else {
                error_log( 'No se encontró la sección para: ' . $member );
            }
        }

        return $sections;
    }
}
*/

/**
 * Obtener todo el texto extraído del PDF.
 *
 * @param string $text Texto completo extraído del PDF.
 * @return array Arreglo con el texto completo en un solo elemento.
 */
if ( ! function_exists( 'split_text_into_sections' ) ) {
    function split_text_into_sections( $text ) {
        // Normalizar el texto
        $text = preg_replace('/\s+/', ' ', $text);
        return [trim( $text )]; // Devolver un array con el texto completo
    }
}



/**
 * Eliminar acentos de una cadena de texto.
 *
 * @param string $string Cadena de texto con posibles acentos.
 * @return string Cadena de texto sin acentos.
 */
if ( ! function_exists( 'remove_accents_custom' ) ) {
    function remove_accents_custom( $string ) {
        // Reemplaza caracteres acentuados por sus equivalentes sin acento
        $transliterator = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC;');
        if ($transliterator) {
            return $transliterator->transliterate($string);
        }
        // Fallback si no se puede crear el transliterator
        return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    }
}

/**
 * Función para generar embedding con reintentos
 */
if ( ! function_exists( 'generar_embedding_openai_con_reintentos' ) ) {
    function generar_embedding_openai_con_reintentos( $text, $api_key, $max_reintentos = 3 ) {
        $reintentos = 0;
        $espera = 2; // segundos

        while ( $reintentos < $max_reintentos ) {
            $embedding = generar_embedding_openai( $text, $api_key );

            if ( $embedding ) {
                return $embedding;
            } else {
                error_log( 'Fallo al generar embedding. Reintentando en ' . $espera . ' segundos...' );
                sleep( $espera );
                $reintentos++;
                $espera *= 2; // Exponencial backoff
            }
        }

        error_log( 'Fallo al generar embedding después de ' . $max_reintentos . ' reintentos.' );
        return false;
    }
}

/**
 * Función para generar embedding usando OpenAI
 */
if ( ! function_exists( 'generar_embedding_openai' ) ) {
    function generar_embedding_openai( $text, $api_key ) {
        error_log( 'Generando embedding para el texto: ' . substr( $text, 0, 100 ) ); // Muestra los primeros 100 caracteres

        $url = 'https://api.openai.com/v1/embeddings';
        $data = [
            'model' => 'text-embedding-ada-002',
            'input' => $text
        ];

        $args = [
            'body' => json_encode( $data ),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'method' => 'POST',
            'data_format' => 'body'
        ];

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'Error al llamar a la API de OpenAI: ' . $response->get_error_message() );
            return false;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $status_code == 200 && isset( $data['data'][0]['embedding'] ) ) {
            error_log( 'Embedding generado exitosamente.' );
            return $data['data'][0]['embedding'];
        } else {
            error_log( 'Error en la respuesta de la API de OpenAI: ' . $body );
            return false;
        }
    }
}

/**
 * Función para guardar embedding en Pinecone
 */
if ( ! function_exists( 'guardar_embedding_pinecone' ) ) {
    function guardar_embedding_pinecone( $doc_id, $embedding, $api_key, $index, $host, $metadata ) {
        $url = "https://$index.$host/vectors";

        $body = [
            'id' => $doc_id,
            'values' => $embedding,
            'metadata' => $metadata
        ];

        $args = [
            'body' => json_encode( $body ),
            'headers' => [
                'Content-Type' => 'application/json',
                'Api-Key' => $api_key
            ],
            'method' => 'POST',
            'data_format' => 'body'
        ];

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'Error al guardar el embedding en Pinecone: ' . $response->get_error_message() );
            return false;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( in_array( $status_code, array( 200, 201 ) ) ) {
            error_log( "Embedding guardado en Pinecone para ID $doc_id." );
            return true;
        } else {
            error_log( "Fallo al guardar el embedding en Pinecone. Código de estado: $status_code. Respuesta: $response_body" );
            return false;
        }
    }
}

/**
 * Función para indexar documento en Elasticsearch
 */
if ( ! function_exists( 'indexar_documento_en_elasticsearch' ) ) {
    function indexar_documento_en_elasticsearch( $text, $embedding, $doc_id, $elasticsearch_url, $metadata ) {
        $url = rtrim( $elasticsearch_url, '/' ) . "/_doc/$doc_id";

        $body = [
            'text' => $text,
            'embedding' => $embedding,
            'metadata' => $metadata
        ];

        $args = [
            'body' => json_encode( $body ),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'method' => 'PUT',
            'data_format' => 'body'
        ];

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'Error al indexar en Elasticsearch: ' . $response->get_error_message() );
            return false;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( in_array( $status_code, array( 200, 201 ) ) ) {
            error_log( "Documento indexado en Elasticsearch para ID $doc_id." );
            return true;
        } else {
            error_log( "Fallo al indexar en Elasticsearch. Código de estado: $status_code. Respuesta: $response_body" );
            return false;
        }
    }
}
