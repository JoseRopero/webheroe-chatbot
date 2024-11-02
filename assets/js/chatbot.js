jQuery(document).ready(function($) {
    $('#chat-submit').on('click', function() {
        var userInput = $('#chat-input').val().trim();
        if(userInput === '') return;

        // Mostrar el mensaje del usuario en el chat log
        $('#chat-log').append('<div class="user-message">' + userInput + '</div>');
        $('#chat-input').val('');

        // Indicador de carga
        var loading = $('<div class="bot-message">Pensando...</div>');
        $('#chat-log').append(loading);

        // Enviar la solicitud AJAX al backend
        $.ajax({
            type: 'POST',
            url: webheroe_ajax.ajax_url,
            data: {
                action: 'webheroe_chatbot_response',
                message: userInput,
                nonce: webheroe_ajax.nonce
            },
            success: function(response) {
                if(response.success) {
                    loading.replaceWith('<div class="bot-message">' + response.data.reply + '</div>');
                } else {
                    loading.replaceWith('<div class="bot-message">Error al procesar la solicitud.</div>');
                }
            },
            error: function() {
                loading.replaceWith('<div class="bot-message">Error de comunicación.</div>');
            }
        });
    });

    // Enviar mensaje con la tecla Enter
    $('#chat-input').on('keypress', function(e) {
        if(e.which === 13) {
            $('#chat-submit').click();
        }
    });
});
