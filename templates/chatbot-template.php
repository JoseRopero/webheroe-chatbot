<div id="webheroe-chatbot" class="container">
    <div class="row">
        <div class="col-12">
            <img id="toggle-chat" src="<?php echo WEBHEROE_CHATBOT_URL . 'assets/images/Button_inicio.png'; ?>" alt="Chat" style="cursor: pointer; width: 100px; height: 100px;"/>
            <div id="chat-window" class="card" style="display: none;">
                <div id="chat-log" class="card-body">
                    <!-- Mensajes aparecerán aquí -->
                </div>
                <div class="input-group position-relative">
                    <input type="text" id="chat-input" class="form-control" placeholder="Escribe tu pregunta..." aria-label="Mensaje">
                    <button id="chat-submit" type="button" class="btn btn-primary send-button">
                        <img src="<?php echo WEBHEROE_CHATBOT_URL . 'assets/images/button.png'; ?>" alt="Enviar" style="width: 40px; height: 35px;"/>
                    </button>
                </div>
                <div id="loading-indicator" class="text-center" style="display: none;">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





