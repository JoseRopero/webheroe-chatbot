jQuery(document).ready(function($) {
    $('#chat-submit').on('click', function() {
        var userInput = $('#chat-input').val().trim();
        if(userInput === '') return;

        // Mostrar el mensaje del usuario en el chat log
        $('#chat-log').append('<div class="user-message">' + userInput + '</div>');
        $('#chat-input').val('');

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
                    // Mostrar la respuesta del chatbot en el chat log
                    $('#chat-log').append('<div class="bot-message">' + response.data.reply + '</div>');
                    // Desplazar el chat log hacia abajo
                    $('#chat-log').scrollTop($('#chat-log')[0].scrollHeight);
                } else {
                    $('#chat-log').append('<div class="bot-message">Error al procesar la solicitud.</div>');
                }
            },
            error: function() {
                $('#chat-log').append('<div class="bot-message">Error de comunicación.</div>');
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
