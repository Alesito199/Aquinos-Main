const toggle = document.getElementById('theme-toggle');
const body = document.body;

toggle.addEventListener('click', () => {
  const currentTheme = body.getAttribute('data-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  body.setAttribute('data-theme', newTheme);
  toggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
});

const codeBg = document.getElementById('code-bg');
const codeLines = [
  // JavaScript
  'const aquinoSolution = new SoftwareBusiness();',
  'function createCustomSoftware() {',
  '  return innovation.buildSolution();',
  '}',
  'let expertise = ["n8n", "WebApps", "Mobile", "AI"];',
  'const success = await deploy(aquinoSolution);',
  'export { quality, innovation, trust };',
  
  // PHP
  '<?php class AquinoSolution {',
  '$expertise = ["PHP", "Laravel", "MySQL"];',
  'public function buildWebApp() {',
  '  return $this->createSolution();',
  '}',
  'echo "Aquino\'s Solution - PHP Expert";',
  '?>',
  
  // JSON
  '{',
  '  "company": "Aquinos Solution",',
  '  "services": ["n8n", "PHP", "React"],',
  '  "contact": "0983363503",',
  '  "quality": "premium"',
  '}',
  
  // Python
  'class AquinosDeveloper:',
  '    def __init__(self, skills):',
  '        self.skills = ["Python", "Django", "AI"]',
  '    def automate_workflow(self):',
  '        return n8n.create_automation()',
  'developer = AquinosDeveloper("Expert")',
  
  // SQL
  'SELECT * FROM projects WHERE status = "success";',
  'UPDATE automation SET efficiency = 100;',
  'DELETE FROM bugs WHERE status = "fixed";',
  'CREATE TABLE success_stories (',
  '  id INT PRIMARY KEY,',
  '  client VARCHAR(255),',
  '  solution TEXT',
  ');'
];

function createCodeStream() {
  if (!codeBg) return;
  
  const line = document.createElement('div');
  line.className = 'code-stream';
  line.textContent = codeLines[Math.floor(Math.random() * codeLines.length)];
  
  // Posición aleatoria
  line.style.left = Math.random() * 80 + '%';
  line.style.animationDuration = (Math.random() * 3 + 2) + 's';
  line.style.opacity = Math.random() * 0.7 + 0.3;
  
  codeBg.appendChild(line);
  
  // Remover después de la animación
  setTimeout(() => {
    if (line.parentNode) {
      line.parentNode.removeChild(line);
    }
  }, 5000);
}

// Crear líneas de código cada cierto tiempo
setInterval(createCodeStream, 2000);

// Limpiar líneas antiguas periódicamente
setInterval(() => {
  if (!codeBg) return;
  const lines = codeBg.querySelectorAll('.code-stream');
  if (lines.length > 10) {
    for (let i = 0; i < lines.length - 10; i++) {
      if (lines[i].parentNode) {
        lines[i].parentNode.removeChild(lines[i]);
      }
    }
  }
}, 1000);

const chatToggle = document.getElementById('chat-toggle');
const chatBox = document.getElementById('chat-box');
const chatClose = document.getElementById('chat-close');
const chatOptions = document.querySelectorAll('.chat-option');

// Abrir/cerrar chat
chatToggle.addEventListener('click', () => {
  chatBox.classList.toggle('active');
});

chatClose.addEventListener('click', () => {
  chatBox.classList.remove('active');
});

// Opciones del chat
chatOptions.forEach(option => {
  option.addEventListener('click', () => {
    const optionType = option.getAttribute('data-option');
    
    switch (optionType) {
      case 'cotizacion':
        window.open('mailto:alexs199.ale@gmail.com?subject=Solicitud de Cotización&body=Hola, me interesa obtener una cotización para mi proyecto. Por favor contáctenme.', '_blank');
        chatBox.classList.remove('active');
        break;
      case 'demo':
        alert('Demo disponible próximamente. Mientras tanto, contáctanos para más información.');
        chatBox.classList.remove('active');
        break;
      case 'n8n':
        window.open('mailto:alexs199.ale@gmail.com?subject=Automatizaciones con n8n&body=Hola, me interesa automatizar procesos con n8n. Quisiera más información.', '_blank');
        chatBox.classList.remove('active');
        break;
      case 'whatsapp':
        window.open('https://wa.me/5930983363503?text=Hola, me interesa conocer más sobre sus servicios de desarrollo de software.', '_blank');
        chatBox.classList.remove('active');
        break;
    }
  });
});

// Cerrar chat al hacer clic fuera
document.addEventListener('click', (e) => {
  if (!e.target.closest('.chat-widget')) {
    chatBox.classList.remove('active');
  }
});

// Smooth scrolling para navegación
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// Animaciones on scroll
const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
    }
  });
}, observerOptions);

// Observar elementos para animaciones
document.addEventListener('DOMContentLoaded', () => {
  const elementsToAnimate = document.querySelectorAll('.servicio, .featured-project, .automation-item, .contact-method');
  
  elementsToAnimate.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
  });
});

// Segundo botón flotante para n8n
const n8nButton = document.createElement('div');
n8nButton.className = 'n8n-widget';
n8nButton.innerHTML = `
  <button id="n8n-toggle" class="n8n-toggle" title="Automatización con n8n">
    <i class="fas fa-robot"></i>
  </button>
  <div id="n8n-box" class="n8n-box">
    <div class="n8n-header">
      <h3>🤖 Automatización</h3>
      <button id="n8n-close" class="n8n-close">&times;</button>
    </div>
    <div class="n8n-content">
      <p>Automatiza tus procesos con workflows inteligentes</p>
      <div class="n8n-options">
        <button class="n8n-option" data-n8n="asistente">
          💬 Conversar con el asistente
        </button>
        <button class="n8n-option" data-n8n="facturas">
          🧾 Asistente AI para Facturas
        </button>
      </div>
      
      <!-- Chat del asistente integrado -->
      <div id="n8n-assistant-chat" class="chat-assistant" style="display: none;">
        <div class="chat-messages" id="n8n-chat-messages">
          <div class="message bot-message">
            <div class="message-content">
              <i class="fas fa-robot"></i>
              <span>¡Hola! Soy Nathan, tu asistente de automatización n8n. ¿En qué puedo ayudarte con tus workflows?</span>
            </div>
          </div>
        </div>
        <div class="chat-input-container">
          <input type="text" id="n8n-chat-input" placeholder="Pregunta sobre n8n, automatización, workflows..." maxlength="500">
          <button id="n8n-send-message" class="send-btn">
            <i class="fas fa-paper-plane"></i>
          </button>
          <button id="n8n-back-to-options" class="back-btn">
            <i class="fas fa-arrow-left"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
`;

document.body.appendChild(n8nButton);

const n8nToggle = document.getElementById('n8n-toggle');
const n8nBox = document.getElementById('n8n-box');
const n8nClose = document.getElementById('n8n-close');
const n8nOptions = document.querySelectorAll('.n8n-option');

n8nToggle.addEventListener('click', () => {
  n8nBox.classList.toggle('active');
});

n8nClose.addEventListener('click', () => {
  n8nBox.classList.remove('active');
});

n8nOptions.forEach(option => {
  option.addEventListener('click', () => {
    const optionType = option.getAttribute('data-n8n');
    
    if (optionType === 'asistente') {
      showN8nAssistantChat();
      return;
    }
    
    if (optionType === 'facturas') {
      // Abrir página de facturas dedicada
      window.open('facturas.html', '_blank');
      n8nBox.classList.remove('active');
      return;
    }
    
    let message = '';
    
    switch (optionType) {
      case 'webhook':
        message = 'Hola, me interesa integrar webhooks con n8n para automatizar procesos en tiempo real.';
        break;
      case 'email':
        message = 'Hola, necesito automatizar el envío de emails y notificaciones con n8n.';
        break;
      case 'database':
        message = 'Hola, quiero sincronizar bases de datos automáticamente usando n8n.';
        break;
      case 'custom':
        message = 'Hola, necesito un workflow personalizado con n8n para mi negocio.';
        break;
    }
    
    window.open(`https://wa.me/5930983363503?text=${encodeURIComponent(message)}`, '_blank');
    n8nBox.classList.remove('active');
  });
});

// Cerrar n8n box al hacer clic fuera
document.addEventListener('click', (e) => {
  if (!e.target.closest('.n8n-widget')) {
    n8nBox.classList.remove('active');
  }
});

// Mostrar opciones de facturas
function showInvoiceOptions() {
  const n8nOptions = document.querySelector('.n8n-options');
  const n8nAssistantChat = document.getElementById('n8n-assistant-chat');
  
  // Ocultar chat si está visible
  if (n8nAssistantChat.style.display === 'flex') {
    hideN8nAssistantChat();
  }
  
  // Crear la interfaz de facturas simplificada
  n8nOptions.innerHTML = `
    <div class="invoice-options">
      <h4 style="color: var(--text-primary); margin-bottom: 1rem; text-align: center;">
        🧾 Asistente AI para Facturas
      </h4>
      
      <button class="n8n-option" onclick="openInvoiceForm()" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; font-weight: bold;">
        <i class="fas fa-upload"></i>
        Cargar Factura
      </button>
      
      <button class="n8n-option" onclick="openWhatsAppInvoice()">
        <i class="fab fa-whatsapp"></i>
        Enviar por WhatsApp
      </button>
      
      <button class="n8n-option" onclick="openEmailInvoice()">
        <i class="fas fa-envelope"></i>
        Enviar por Email
      </button>
      
      <button class="n8n-option" onclick="backToN8nOptions()" style="background: var(--bg-secondary); color: var(--text-secondary); margin-top: 0.5rem;">
        <i class="fas fa-arrow-left"></i>
        Volver
      </button>
    </div>
  `;
}

// Funciones para las opciones de facturas
function openInvoiceForm() {
  // Crear URL con autenticación Basic Auth embebida
  const username = 'Alejandro';
  const password = 'Aquino@!';
  const formUrl = `https://${username}:${password}@n8n.aquinossolution.com/form/8ea2fc52-0fb4-4b15-a405-5e23448aca2d`;
  
  // Abrir directamente el formulario con credenciales
  window.open(formUrl, '_blank');
  n8nBox.classList.remove('active');
}

function openWhatsAppInvoice() {
  const message = '🧾 Hola! Quiero usar el Asistente AI para procesar facturas. Te envío la imagen de mi factura para que la proceses automáticamente.';
  window.open(`https://wa.me/5930983363503?text=${encodeURIComponent(message)}`, '_blank');
  n8nBox.classList.remove('active');
}

function openEmailInvoice() {
  const subject = 'Asistente AI - Procesamiento de Facturas';
  const body = '🧾 Hola!\\n\\nQuiero usar el Asistente AI para procesar facturas.\\n\\nAdjunto mi factura para que sea procesada automáticamente.\\n\\nEspero extraer:\\n• Datos del proveedor\\n• Productos y servicios\\n• Totales y subtotales\\n• Fechas e información fiscal\\n\\nGracias!';
  window.open(`mailto:alexs199.ale@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`, '_blank');
  n8nBox.classList.remove('active');
}

function backToN8nOptions() {
  // Restaurar las opciones originales de n8n
  const n8nOptions = document.querySelector('.n8n-options');
  n8nOptions.innerHTML = `
    <button class="n8n-option" data-n8n="asistente">
      💬 Conversar con el asistente
    </button>
    <button class="n8n-option" data-n8n="facturas">
      🧾 Asistente AI para Facturas
    </button>
  `;
  
  // Re-agregar los event listeners
  const newN8nOptions = document.querySelectorAll('.n8n-option');
  newN8nOptions.forEach(option => {
    option.addEventListener('click', () => {
      const optionType = option.getAttribute('data-n8n');
      
      if (optionType === 'asistente') {
        showN8nAssistantChat();
        return;
      }
      
        if (optionType === 'facturas') {
          // Abrir página de facturas dedicada
          window.open('facturas.html', '_blank');
          n8nBox.classList.remove('active');
          return;
        }      let message = '';
      
      switch (optionType) {
        case 'webhook':
          message = 'Hola, me interesa integrar webhooks con n8n para automatizar procesos en tiempo real.';
          break;
        case 'email':
          message = 'Hola, necesito automatizar el envío de emails y notificaciones con n8n.';
          break;
        case 'database':
          message = 'Hola, quiero sincronizar bases de datos automáticamente usando n8n.';
          break;
        case 'custom':
          message = 'Hola, necesito un workflow personalizado con n8n para mi negocio.';
          break;
      }
      
      window.open(`https://wa.me/5930983363503?text=${encodeURIComponent(message)}`, '_blank');
      n8nBox.classList.remove('active');
    });
  });
}

// Funcionalidad del chat con asistente en n8n
let n8nConversationHistory = [];
const N8N_WEBHOOK_URL = 'https://n8n.aquinossolution.com/webhook/f05e038a-e6a1-46fe-961d-e16a06469a02/chat';

function showN8nAssistantChat() {
  const n8nOptions = document.querySelector('.n8n-options');
  const n8nAssistantChat = document.getElementById('n8n-assistant-chat');
  
  n8nOptions.style.display = 'none';
  n8nAssistantChat.style.display = 'flex';
  
  // Focus en el input del chat
  const n8nChatInput = document.getElementById('n8n-chat-input');
  setTimeout(() => n8nChatInput.focus(), 100);
}

function hideN8nAssistantChat() {
  const n8nOptions = document.querySelector('.n8n-options');
  const n8nAssistantChat = document.getElementById('n8n-assistant-chat');
  
  n8nOptions.style.display = 'flex';
  n8nAssistantChat.style.display = 'none';
}

// Event listeners para el chat del asistente n8n
document.addEventListener('DOMContentLoaded', () => {
  // Esperar a que el DOM esté completamente cargado
  setTimeout(() => {
    const n8nChatInput = document.getElementById('n8n-chat-input');
    const n8nSendButton = document.getElementById('n8n-send-message');
    const n8nBackButton = document.getElementById('n8n-back-to-options');
    const n8nMessagesContainer = document.getElementById('n8n-chat-messages');

    if (n8nChatInput && n8nSendButton && n8nBackButton && n8nMessagesContainer) {
      // Enviar mensaje al presionar Enter
      n8nChatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          sendN8nMessage();
        }
      });

      // Enviar mensaje al hacer clic en el botón
      n8nSendButton.addEventListener('click', sendN8nMessage);

      // Volver a las opciones
      n8nBackButton.addEventListener('click', hideN8nAssistantChat);

      async function sendN8nMessage() {
        const message = n8nChatInput.value.trim();
        if (!message) return;

        // Deshabilitar input mientras se procesa
        n8nChatInput.disabled = true;
        n8nSendButton.disabled = true;
        
        // Mostrar mensaje del usuario
        displayN8nMessage(message, 'user');
        n8nChatInput.value = '';
        
        // Mostrar indicador de escritura
        showN8nTypingIndicator();
        
        try {
          // El webhook de n8n espera un sessionId específico
          const sessionId = getN8nSessionId();
          
          // Credenciales para Basic Auth
          const username = 'Alejandro';
          const password = 'Aquino@!';
          const authString = btoa(`${username}:${password}`);
          
          // Formato basado en tu Chat Trigger configuración
          const payload = {
            chatInput: message,
            sessionId: sessionId,
            message: message
          };

          const response = await fetch(N8N_WEBHOOK_URL, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'text/plain, text/event-stream, application/json',
              'Authorization': `Basic ${authString}`,
              'User-Agent': 'AquinosChat/1.0',
              'Origin': window.location.origin
            },
            body: JSON.stringify(payload)
          });

          // Remover indicador de escritura
          removeN8nTypingIndicator();
          
          if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Error HTTP: ${response.status} - ${errorText}`);
          }

          // Procesar respuesta
          const contentType = response.headers.get('content-type');
          let responseData;
          
          // Obtener el texto crudo primero
          const rawText = await response.text();
          
          // Intentar limpiar y parsear la respuesta
          try {
            if (contentType && contentType.includes('application/json')) {
              // Limpiar caracteres problemáticos comunes
              let cleanedText = rawText
                .trim()
                .replace(/^\uFEFF/, '') // Remover BOM
                .replace(/[\u0000-\u001F\u007F]/g, '') // Remover caracteres de control
                .split('\n')[0] // Tomar solo la primera línea si hay múltiples
                .split('\r')[0]; // Manejar retornos de carro
              
              // Si aún no es JSON válido, intentar extraer solo la parte JSON
              if (!cleanedText.startsWith('{') && !cleanedText.startsWith('[')) {
                const jsonStart = cleanedText.indexOf('{');
                const jsonArrayStart = cleanedText.indexOf('[');
                
                if (jsonStart !== -1) {
                  cleanedText = cleanedText.substring(jsonStart);
                } else if (jsonArrayStart !== -1) {
                  cleanedText = cleanedText.substring(jsonArrayStart);
                }
              }
              
              // Intentar encontrar el final del JSON válido
              let validJsonEnd = -1;
              let braceCount = 0;
              for (let i = 0; i < cleanedText.length; i++) {
                if (cleanedText[i] === '{') braceCount++;
                if (cleanedText[i] === '}') {
                  braceCount--;
                  if (braceCount === 0) {
                    validJsonEnd = i + 1;
                    break;
                  }
                }
              }
              
              if (validJsonEnd > 0) {
                cleanedText = cleanedText.substring(0, validJsonEnd);
              }
              
              responseData = JSON.parse(cleanedText);
            } else {
              // Si no es JSON, usar como texto plano
              responseData = rawText.trim();
            }
          } catch (jsonError) {
            responseData = rawText.trim();
          }

          // Extraer la respuesta del asistente
          let assistantMessage;
          
          // Si la respuesta es un string directo
          if (typeof responseData === 'string') {
            assistantMessage = responseData;
          }
          // Si la respuesta tiene el formato de n8n Chat Trigger
          else if (responseData && typeof responseData === 'object') {
            // Posibles campos donde puede venir la respuesta del chat
            assistantMessage = responseData.output || 
                             responseData.response || 
                             responseData.message || 
                             responseData.text ||
                             responseData.result ||
                             responseData.data ||
                             responseData.content ||
                             responseData.reply;
            
            // Si no encontramos mensaje en los campos comunes, usar toda la respuesta
            if (!assistantMessage) {
              assistantMessage = JSON.stringify(responseData, null, 2);
            }
          }
          // Fallback
          else {
            assistantMessage = '✨ ¡Hola! Soy Nathan, tu asistente de automatización n8n. ¿En qué puedo ayudarte?';
          }
          
          // Limpiar la respuesta si viene con markdown o formato JSON
          if (typeof assistantMessage === 'string') {
            assistantMessage = assistantMessage
              .replace(/```json\n?/g, '')
              .replace(/\n?```/g, '')
              .replace(/```\n?/g, '')
              .replace(/^\s*[\r\n]+/gm, '') // Remover líneas vacías al inicio
              .replace(/[\r\n]+\s*$/gm, '') // Remover líneas vacías al final
              .trim();
          }
          
          // Verificar que no esté vacía
          if (!assistantMessage || assistantMessage.trim() === '' || assistantMessage === '{}' || assistantMessage === 'null') {
            assistantMessage = '👋 ¡Hola! Soy Nathan, tu asistente de automatización n8n. Recibí tu mensaje, ¿en qué puedo ayudarte específicamente?';
          }
          
          displayN8nMessage(assistantMessage, 'bot');
          
          // Guardar en historial
          n8nConversationHistory.push(
            { role: 'usuario', content: message },
            { role: 'asistente', content: assistantMessage }
          );
          
          // Mantener solo los últimos 10 intercambios
          if (n8nConversationHistory.length > 20) {
            n8nConversationHistory = n8nConversationHistory.slice(-20);
          }
          
        } catch (error) {
          removeN8nTypingIndicator();
          
          // Mensajes de error más específicos
          let errorMessage = '🔧 Disculpa, hay un problema temporal con el asistente. ';
          
          if (error.message.includes('session')) {
            errorMessage += 'Error de sesión detectado. Intentando reconectar...';
            // Limpiar sessionId y generar uno nuevo
            localStorage.removeItem('n8nChatSessionId');
            errorMessage += '\n\n🔄 Por favor, intenta enviar tu mensaje nuevamente.';
          } else if (error.message.includes('404')) {
            errorMessage += 'El webhook no está disponible. Contáctanos directamente por WhatsApp.';
          } else if (error.message.includes('500')) {
            errorMessage += 'Error del servidor. Estamos trabajando en solucionarlo.';
          } else if (error.message.includes('Network')) {
            errorMessage += 'Problema de conexión. Verifica tu internet e intenta de nuevo.';
          } else {
            errorMessage += 'Mientras tanto, puedes contactarme directamente por WhatsApp para cualquier consulta sobre automatización y n8n.';
          }
          
          errorMessage += '\n\n📱 WhatsApp: https://wa.me/5930983363503';
          
          displayN8nMessage(errorMessage, 'bot');
        } finally {
          // Re-habilitar input
          n8nChatInput.disabled = false;
          n8nSendButton.disabled = false;
          n8nChatInput.focus();
        }
      }

      function displayN8nMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}-message`;
        
        const icon = type === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
        
        messageDiv.innerHTML = `
          <div class="message-content">
            ${icon}
            <span>${message}</span>
          </div>
        `;
        
        n8nMessagesContainer.appendChild(messageDiv);
        n8nMessagesContainer.scrollTop = n8nMessagesContainer.scrollHeight;
      }

      function showN8nTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message typing-indicator';
        typingDiv.id = 'n8n-typing-indicator';
        typingDiv.innerHTML = `
          <i class="fas fa-robot"></i>
          <span>Procesando con n8n</span>
          <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
          </div>
        `;
        
        n8nMessagesContainer.appendChild(typingDiv);
        n8nMessagesContainer.scrollTop = n8nMessagesContainer.scrollHeight;
      }

      function removeN8nTypingIndicator() {
        const typingIndicator = document.getElementById('n8n-typing-indicator');
        if (typingIndicator) {
          typingIndicator.remove();
        }
      }
    }
  }, 1000); // Esperar 1 segundo para asegurar que el DOM esté listo
});

function getN8nSessionId() {
  let sessionId = localStorage.getItem('n8nChatSessionId');
  if (!sessionId) {
    // Generar un sessionId más robusto y único
    const timestamp = Date.now();
    const random = Math.random().toString(36).substr(2, 12);
    const userAgent = navigator.userAgent.slice(0, 10).replace(/\W/g, '');
    sessionId = `aquinos_${timestamp}_${random}_${userAgent}`;
    localStorage.setItem('n8nChatSessionId', sessionId);
  }
  return sessionId;
}

// ========================================
// SMOOTH SCROLLING
// ========================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// ========================================
// THEME TOGGLE
// ========================================

const themeToggle = document.getElementById('theme-toggle');

// Check for saved theme preference or default to 'light' mode
const currentTheme = localStorage.getItem('theme') || 'light';
body.setAttribute('data-theme', currentTheme);
updateThemeToggle(currentTheme);

themeToggle.addEventListener('click', () => {
  const currentTheme = body.getAttribute('data-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  
  body.setAttribute('data-theme', newTheme);
  localStorage.setItem('theme', newTheme);
  updateThemeToggle(newTheme);
});

function updateThemeToggle(theme) {
  themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
  themeToggle.setAttribute('aria-label', theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
}

// ========================================
// PORTFOLIO MODAL FUNCTIONALITY
// ========================================

const portfolioItems = document.querySelectorAll('.proyecto-item');
const modal = document.getElementById('proyecto-modal');
const modalClose = document.getElementById('modal-close');

// Open modal
portfolioItems.forEach(item => {
  item.addEventListener('click', () => {
    const title = item.querySelector('h4').textContent;
    const description = item.getAttribute('data-description');
    const image = item.querySelector('img').src;
    const technologies = item.getAttribute('data-technologies');
    const demo = item.getAttribute('data-demo');
    const github = item.getAttribute('data-github');

    // Update modal content
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-description').textContent = description;
    document.getElementById('modal-image').src = image;
    
    // Update technologies
    const techContainer = document.getElementById('modal-technologies');
    techContainer.innerHTML = technologies.split(',').map(tech => 
      `<span class="tech-tag">${tech.trim()}</span>`
    ).join('');

    // Update links
    const demoBtn = document.getElementById('modal-demo');
    const githubBtn = document.getElementById('modal-github');
    
    if (demo && demo !== '#') {
      demoBtn.href = demo;
      demoBtn.style.display = 'inline-flex';
    } else {
      demoBtn.style.display = 'none';
    }
    
    if (github && github !== '#') {
      githubBtn.href = github;
      githubBtn.style.display = 'inline-flex';
    } else {
      githubBtn.style.display = 'none';
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  });
});

// Close modal
modalClose.addEventListener('click', () => {
  modal.classList.remove('active');
  document.body.style.overflow = 'auto';
});

// Close modal when clicking outside
modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
  }
});

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && modal.classList.contains('active')) {
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
  }
});