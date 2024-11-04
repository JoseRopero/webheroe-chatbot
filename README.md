# 📄 WebHeroe Chatbot

<img src="./assets/images/WebHeroe_Chatbot.webp" alt="Logo del Plugin" width="50%">

![Licencia](https://img.shields.io/badge/licencia-GPLv2-blue.svg)
![Versión](https://img.shields.io/badge/version-1.2.0-green.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-brightgreen.svg)

**WebHeroe Chatbot** es un plugin de WordPress diseñado para integrar un chatbot inteligente que puede responder preguntas basadas en documentos PDF que tú subas. Con la ayuda de inteligencia artificial, este chatbot es una herramienta ideal para mejorar la interacción en tu sitio web.

## 🚀 Características

- **Interacción en Tiempo Real**: Ofrece respuestas instantáneas a las preguntas de los usuarios.
- **Soporte para PDF**: Carga cualquier documento PDF y permite al chatbot responder preguntas sobre su contenido.
- **Integración de IA**: Utiliza el modelo Meta Code Llama 70B para generar respuestas precisas y contextuales.
- **Fácil de Configurar**: Con un menú de administración intuitivo para gestionar configuraciones y claves API.

## 📦 Instalación

1. **Descarga el Plugin**:
   - - Haz clic en el botón **"Code"** y selecciona **"Download ZIP"**.

2. **Sube el Plugin**:
   - Accede a tu panel de administración de WordPress.
   - Ve a `Plugins` > `Añadir nuevo` > `Subir plugin`.
   - Selecciona el archivo ZIP y haz clic en `Instalar ahora`.

3. **Activa el Plugin**:
   - Después de la instalación, haz clic en `Activar`.

4. **Configura el Plugin**:
   - Ve a `Chatbot` en el menú de administración.
   - Introduce tus claves API y configura las opciones según tus necesidades.

## 🗝️ Obtener Claves API y Configuraciones

### 1. Clave API de OpenAI

- Visita [OpenAI](https://openai.com/api/) y regístrate o inicia sesión en tu cuenta.
- Crea una nueva clave API desde el panel de control.
- Copia la clave y pégala en la sección de configuración del plugin.

### 2. Clave API de Pinecone

- Dirígete a [Pinecone](https://www.pinecone.io/) y regístrate para obtener una cuenta.
- Accede a tu panel de Pinecone y genera una nueva clave API.
- Guarda la clave en la configuración del plugin.

### 3. Creación del Índice en Kibana

- Accede a tu cuenta de **Bonsai** y dirígete a **Kibana**.
- En Kibana, ve a la sección de **Dev Tools**.
- Para crear un índice llamado `embedding`, ejecuta el siguiente comando en la consola de Dev Tools:

```json
PUT /embedding
{
  "settings": {
    "number_of_shards": 1,
    "number_of_replicas": 1
  },
  "mappings": {
    "properties": {
      "text": {
        "type": "text"
      },
      "embedding": {
        "type": "float"  // Ajusta esto según el tipo de datos que necesites para el embedding
      },
      "metadata": {
        "type": "object"  // Para almacenar metadatos adicionales
      }
    }
  }
}
```

### 4. Nombre del Índice en Pinecone

- Al crear el índice en Pinecone, asegúrate de que el nombre del índice sea exactamente `embedding` para que funcione correctamente con el plugin.

### 5. Host de Pinecone

- En el panel de Pinecone, encontrarás un campo que indica la URL del host.
- Copia esta URL y pégala en la configuración del plugin.

### 6. URL de Elasticsearch (Bonsai)

- Regístrate en [Bonsai](https://bonsai.io/) y crea un clúster de Elasticsearch.
- Una vez creado, obtendrás la URL del clúster.
- Copia esta URL y colócala en la configuración del plugin.

### 7. Clave API de Groq

- Ve a [Groq](https://groq.com/) y regístrate o inicia sesión.
- Genera una clave API desde tu panel de Groq.
- Pega la clave en la configuración del plugin.

### 8. Dimensiones del Embedding

- Este campo se refiere a la cantidad de dimensiones que tendrá el vector de embedding generado. 
- Normalmente, un modelo como `text-embedding-ada-002` utiliza 1536 dimensiones, que es un buen punto de partida.
- Puedes ajustar este valor según tus necesidades, pero asegúrate de que sea compatible con el modelo que estás utilizando.

## 📄 Cómo Usar

1. **Sube un PDF**: Dirígete a la Biblioteca de Medios y sube tu archivo PDF.
2. **Haz Preguntas**: Inserta el shortcode `[webheroe_chatbot]` en cualquier página o entrada donde desees que aparezca el chatbot.
3. **Interactúa**: Pregunta sobre el contenido del PDF y obtén respuestas instantáneas.

## 🔧 Contribuciones

¡Las contribuciones son bienvenidas! Si deseas ayudar a mejorar WebHeroe Chatbot, sigue estos pasos:

1. Haz un fork del repositorio.
2. Crea tu rama (`git checkout -b feature-nueva-funcionalidad`).
3. Haz tus cambios y agrega las modificaciones (`git commit -m 'Añadir nueva funcionalidad'`).
4. Sube tus cambios (`git push origin feature-nueva-funcionalidad`).
5. Abre un pull request.

## 📜 Licencia

Este plugin está licenciado bajo la [GPLv2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html) o superior.

## 📧 Contacto

Si tienes alguna pregunta o sugerencia, no dudes en contactar a:

**Jose Manuel Ropero**  
Email: [josemanuelropero@hotmail.com](mailto:josemanuelropero@hotmail.com) 

---

¡Gracias por usar WebHeroe Chatbot! Estamos emocionados de ver cómo lo usas para mejorar la interacción con tus usuarios.
