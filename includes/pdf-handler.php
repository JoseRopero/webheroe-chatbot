<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Función para manejar la página de "Gestionar PDFs" en el panel de administración
 */
if ( ! function_exists( 'webheroe_chatbot_admin_page_pdfs' ) ) {
    function webheroe_chatbot_admin_page_pdfs() {
        echo '<div class="wrap"><h1>WebHeroe Chatbot - Gestionar PDFs</h1><p>Aquí puedes gestionar los PDFs para el chatbot.</p></div>';
        // Aquí puedes agregar más funcionalidades para gestionar PDFs
    }
}
