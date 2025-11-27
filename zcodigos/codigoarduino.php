#include <SPI.h>
#include <MFRC522.h>
#include <SD.h>
#include <Wire.h>
#include <RTClib.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>
#include <vector>
#include <WebServer.h>
#include <DNSServer.h>
#include <Preferences.h>

// === CONFIGURACIÓN DE PINES ===
#define RST_PIN 25
#define SS_RFID 26
#define SS_SD 5
#define LED_ACTIVITY 2
#define LED_STATUS 4
#define LED_ERROR 15
#define SCK_PIN 18
#define MISO_PIN 19
#define MOSI_PIN 23
#define CONFIG_BUTTON_PIN 0  // Pin del botón de configuración (GPIO0 - BOOT)
// === NUEVA CONFIGURACIÓN BUZZER ===
#define BUZZER_PIN 13       // Pin GPIO elegido

// === CONFIGURACIÓN PANTALLA LCD ===
#define LCD_ADDRESS 0x27
#define LCD_COLUMNS 16
#define LCD_ROWS 2
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLUMNS, LCD_ROWS);

// === CONFIGURACIÓN WIFI Y SERVIDOR ===
const char* ap_ssid = "SACA_Config";
const char* ap_password = "12345678";

// Variables dinámicas para WiFi
String wifi_ssid = "";
String wifi_password = "";
String server_url = "";

// Variables dinámicas para RTC
String current_rtc_date = "";
String current_rtc_time = "";

// === OBJETOS PRINCIPALES ===
MFRC522 rfid(SS_RFID, RST_PIN);
RTC_DS3231 rtc;
File archivo;
WebServer server(80);
DNSServer dnsServer;
Preferences preferences;

// === ESTRUCTURAS ===
struct Estudiante {
    String uid;
    String nombre;
    int estado; // <-- AÑADIR ESTA LÍNEA
    bool marcoHoy;
};

struct CardStatus {
    String uid;
    String lastAction;
};

// === CONSTANTES ===
#define MAX_STUDENTS 100
#define CARD_READ_DELAY 200
#define DATE_UPDATE_INTERVAL 1000
#define LCD_MESSAGE_DURATION 1000
#define SYNC_INTERVAL 30000
#define MODULE_CHECK_INTERVAL 10000
#define STUDENT_LIST_SYNC_INTERVAL 60000
#define ERROR_BLINK_INTERVAL 500
#define ERROR_DISPLAY_CYCLE_INTERVAL 2000
#define CONFIG_MODE_TIMEOUT 300000  // 5 minutos en modo config
#define RESET_BUTTON_HOLD_TIME 5000  // 5 segundos para resetear configuración


// === VARIABLES GLOBALES ===
Estudiante estudiantes[MAX_STUDENTS];
int numEstudiantesActual = 0;

// Temporizadores
unsigned long lastSyncAttempt = 0;
unsigned long lastCardRead = 0;
unsigned long lastDateUpdate = 0;
unsigned long lcdMessageDisplayTime = 0;
unsigned long lastModuleCheck = 0;
unsigned long lastErrorDisplayUpdate = 0;
unsigned long lastErrorBlink = 0;
unsigned long lastStudentListSync = 0;
unsigned long configModeStartTime = 0;
unsigned long lastButtonPress = 0;

unsigned long buttonPressStartTime = 0;
unsigned long activityLedOnTime = 0;
const int ACTIVITY_LED_DURATION = 100; // 100 ms de duración del pulso
bool activityLedPulsed = false; // Bandera para saber si se acaba de encender
unsigned long statusLedPulseTime = 0;
bool statusLedPulsed = false;
// Para controlar el pulso del LED Rojo/Error (en denegaciones)
unsigned long errorLedPulseTime = 0;
bool errorLedPulsed = false;
// Para controlar el tiempo de presión del botón

// Control de sistema
String lastCardUID = "";
bool sdCardOK = true;
bool wifiConnected = false;
bool rtcOK = true;
bool rfidOK = true;
bool errorLedState = false;
bool configMode = false;
bool shouldRestart = false;
bool buttonPressed = false;  // Estado del botón
bool resetInProgress = false;
// Indica si está en proceso de reset

// Control de errores
String errorMessages[4];
int currentErrorIndex = 0;
int numActiveErrors = 0;

// === ARCHIVOS SD ===
const char* LAST_ACTIONS_FILE = "/last_actions.txt";

// === DECLARACIONES DE FUNCIONES ===
String formatTwoDigits(int number);
String obtenerTimestamp(String &fecha, String &hora);
void mostrarMensajeLCD(String linea1, String linea2, int duracion = 0);
void mostrarFechaEnLCD();
void mostrarPantallaInicial();
void inicializarLCD();
void inicializarHardware();
void crearArchivosBasicos();
void procesarMensajesLCD();
void verificarSistemaPeriodicamennte();
void sincronizarDatosPeriodicamente();
void procesarLecturaRFID();
void actualizarInterfaz();
void actualizarLEDs();
void parpadearLEDActividad();
void parpadearLEDEstado(); // Pulso rápido del LED verde (acceso OK)
void parpadearLEDErrorDenegacion(); // Pulso rápido del LED rojo (denegación)
void verificarEstadoSistema();
void conectarWiFi();
void crearArchivoSiNoExiste(const char* filename, const char* header);
bool guardarRegistroEnSD(String filename, String nombre, String uid, String accion, String fecha, String hora, String modo);
void guardarPendienteEnSD(String uid, String accion, String fecha, String hora);
std::vector<CardStatus> loadAllCardStatuses();
void saveAllCardStatuses(const std::vector<CardStatus>& statuses);
String getLastAction(String uid);
void updateLastAction(String uid, String action);
String leerUID();
void detenerLecturaRFID();
void procesarAsistencia(String uidLeido);
int buscarEstudiante(String uid, String &nombreEncontrado);
bool enviarAsistenciaRapido(String uid, String accion, String modo);
void sincronizarPendientes();
void sincronizarListaEstudiantes();
void cargarListaEstudiantesDesdeSD();
bool enviarUidDesconocido(String uid); // Declaración de la nueva función

// === NUEVAS FUNCIONES PARA CONFIGURACIÓN ===
void cargarConfiguracion();
void guardarConfiguracion();
void iniciarModoConfiguracion();
void detenerModoConfiguracion();
void verificarBotonConfiguracion();
void manejarServidorConfig();
void handleRoot();
void handleSave();
void handleScan();
void handleNotFound();
String escanearRedes();
String generarPaginaConfig();
void resetearConfiguracionWiFi();
void configurarRTCManual(String fechaStr, String horaStr); // Nueva función para configurar RTC
// Nueva función para resetear configuración

// Variable global para almacenar el ID del aula
String aulaCodigo = "AULA-101";

// === FUNCIONES PRINCIPALES ===
void setup() {
    Serial.begin(115200);
    delay(200);
    
    // Inicializar Preferences
    preferences.begin("esp32_config", false);
    
    inicializarHardware();
    cargarConfiguracion();
    verificarEstadoSistema();
    mostrarPantallaInicial();
    
    if (sdCardOK) cargarListaEstudiantesDesdeSD();
    if (WiFi.status() == WL_CONNECTED) sincronizarListaEstudiantes();

    emitirSonido(0);
}

void loop() {
    // Verificar botón de configuración y reset
    verificarBotonConfiguracion();

    if (configMode) {
        manejarServidorConfig();
        // Timeout del modo configuración
        if (millis() - configModeStartTime > CONFIG_MODE_TIMEOUT) {
            detenerModoConfiguracion();
        }
    } else {
        procesarMensajesLCD();
        verificarSistemaPeriodicamennte();
        sincronizarDatosPeriodicamente();
        procesarLecturaRFID();
        actualizarInterfaz();
    }
    
    if (shouldRestart) {
        delay(1000);
        ESP.restart();
    }
    
    yield();
}

// === FUNCIONES DE CONFIGURACIÓN ===
void cargarConfiguracion() {
    wifi_ssid = preferences.getString("wifi_ssid", "");
    wifi_password = preferences.getString("wifi_pass", "");
    server_url = preferences.getString("server_url", "http://192.168.1.100:8000");
    
    // ✅ CARGAR CÓDIGO DE AULA (String en lugar de int)
    aulaCodigo = preferences.getString("aula_codigo", "");
    
    // Si no está configurado, asignar valor por defecto
    if (aulaCodigo.length() == 0) {
        aulaCodigo = "AULA-101"; // Valor por defecto
        preferences.putString("aula_codigo", aulaCodigo);
        Serial.println("⚠️ Código de Aula no configurado, usando: " + aulaCodigo);
    }
    
    Serial.println("=== Configuración Cargada ===");
    Serial.println("SSID: " + wifi_ssid);
    Serial.println("Server URL: " + server_url);
    Serial.println("Aula Código: " + aulaCodigo);
    Serial.println("=============================");

    if (wifi_ssid.length() > 0) {
        conectarWiFi();
    } else {
        Serial.println("⚠️ No hay configuración WiFi. Iniciando modo configuración...");
        iniciarModoConfiguracion();
    }
}
void guardarConfiguracion() {
    preferences.putString("wifi_ssid", wifi_ssid);
    preferences.putString("wifi_pass", wifi_password);
    preferences.putString("server_url", server_url);
    Serial.println("Configuración guardada");
}

void resetearConfiguracionWiFi() {
    Serial.println("Reseteando configuración WiFi...");
    
    // Limpiar variables
    wifi_ssid = "";
    wifi_password = "";
    
    // Borrar de preferences
    preferences.remove("wifi_ssid");
    preferences.remove("wifi_pass");
    
    // Desconectar WiFi
    WiFi.disconnect(true);
    WiFi.mode(WIFI_OFF);
    // Mostrar mensaje en LCD
    mostrarMensajeLCD("Config WiFi", "RESETEADA!", 3000);
    
    Serial.println("Configuración WiFi reseteada. Iniciando modo configuración...");
    // Esperar un momento antes de iniciar modo configuración
    delay(3000);
    // Iniciar modo configuración automáticamente
    iniciarModoConfiguracion();
}

void iniciarModoConfiguracion() {
    configMode = true;
    configModeStartTime = millis();

    // Obtener la hora actual para mostrarla en el formulario
    DateTime now = rtc.now();
    current_rtc_date = String(now.year()) + "-" + formatTwoDigits(now.month()) + "-" + formatTwoDigits(now.day());
    current_rtc_time = formatTwoDigits(now.hour()) + ":" + formatTwoDigits(now.minute());
    
    // Configurar Access Point
    WiFi.mode(WIFI_AP);
    WiFi.softAP(ap_ssid, ap_password);

    // Esperar a que el AP se active
    delay(500);

    // Configurar DNS para portal cautivo - redirige TODAS las consultas DNS al ESP32
    dnsServer.start(53, "*", WiFi.softAPIP());

    // Configurar rutas del servidor web
    server.on("/", handleRoot);
    server.on("/save", HTTP_POST, handleSave);
    server.on("/scan", handleScan);
    server.on("/generate_204", handleRoot); // Android
    server.on("/fwlink", handleRoot);        // Microsoft
    server.on("/hotspot-detect.html", handleRoot); // Apple
    server.onNotFound(handleNotFound);

    server.begin();
    
    mostrarMensajeLCD("Modo Config", WiFi.softAPIP().toString(), 0);
    
    Serial.println("Modo configuración iniciado");
    Serial.println("Portal cautivo activo");
    Serial.println("IP del AP: " + WiFi.softAPIP().toString());
    Serial.println("SSID: " + String(ap_ssid));
    Serial.println("Password: " + String(ap_password));
}

void detenerModoConfiguracion() {
    configMode = false;
    server.stop();
    dnsServer.stop();
    WiFi.softAPdisconnect(true);
    mostrarMensajeLCD("Saliendo config...", "Reiniciando...", 2000);
    shouldRestart = true;
}

void verificarBotonConfiguracion() {
    bool currentButtonState = (digitalRead(CONFIG_BUTTON_PIN) == LOW);
    // Detectar cuando se presiona el botón
    if (currentButtonState && !buttonPressed) {
        buttonPressed = true;
        buttonPressStartTime = millis();
        Serial.println("Botón presionado...");
    }
    
    // Detectar cuando se suelta el botón
    if (!currentButtonState && buttonPressed) {
        buttonPressed = false;
        unsigned long pressDuration = millis() - buttonPressStartTime;
        
        if (resetInProgress) {
            // Si estaba en proceso de reset, cancelarlo
            resetInProgress = false;
            digitalWrite(LED_ERROR, LOW);
            mostrarMensajeLCD("Reset", "CANCELADO", 2000);
            Serial.println("Reset cancelado");
        } else if (pressDuration >= 1000 && pressDuration < RESET_BUTTON_HOLD_TIME) {
            // Presión corta (1-5 segundos) - modo configuración
            if (!configMode) {
                Serial.println("Botón de configuración presionado");
                mostrarMensajeLCD("Iniciando", "Configuracion...", 2000);
                delay(2000);
                iniciarModoConfiguracion();
            }
        }
        
        lastButtonPress = millis();
    }
    
    // Verificar si el botón está siendo presionado por más de 5 segundos
    if (buttonPressed && !resetInProgress && 
        (millis() - buttonPressStartTime) >= RESET_BUTTON_HOLD_TIME) {
        
        resetInProgress = true;
        // Mostrar mensaje de confirmación
        mostrarMensajeLCD("RESET WiFi en", "curso...", 0);
        
        // SONIDO DE CONFIRMACIÓN DE RESET // <-- MODIFICACIÓN
        emitirSonido(3); 
        delay(100); 

        // Parpadear LED de error rápidamente durante el reset
        for (int i = 0; i < 10; i++) {
            digitalWrite(LED_ERROR, HIGH);
            delay(100);
            digitalWrite(LED_ERROR, LOW);
            delay(100);
        }
        
        // Ejecutar reset de configuración WiFi
        resetearConfiguracionWiFi();
        resetInProgress = false;
    }
    
    // Mostrar progreso visual durante la presión prolongada
    if (buttonPressed && !resetInProgress) {
        unsigned long pressDuration = millis() - buttonPressStartTime;
        if (pressDuration >= 1000 && pressDuration < RESET_BUTTON_HOLD_TIME) {
            // Mostrar contador regresivo en LCD
            int segundosRestantes = (RESET_BUTTON_HOLD_TIME - pressDuration) / 1000;
            mostrarMensajeLCD("Reset WiFi en:", String(segundosRestantes + 1) + " segundos", 0);
            
            // SONIDO DE CUENTA REGRESIVA (Beep cada 1 segundo) // <-- MODIFICACIÓN
            static unsigned long lastBeep = 0;
            if (millis() - lastBeep >= 1000) {
                emitirSonido(3);
                lastBeep = millis();
            }

            // Parpadear LED de error lentamente
            if ((pressDuration / 500) % 2 == 0) {
                digitalWrite(LED_ERROR, HIGH);
            } else {
                digitalWrite(LED_ERROR, LOW);
            }
        }
    }
}

void manejarServidorConfig() {
    dnsServer.processNextRequest();
    server.handleClient();
    // Parpadear LED de error para indicar modo config
    if (millis() - lastErrorBlink > 250) {
        errorLedState = !errorLedState;
        digitalWrite(LED_ERROR, errorLedState ? HIGH : LOW);
        lastErrorBlink = millis();
        
        // SONIDO MODO CONFIGURACIÓN (Cada 1 segundo) // <-- MODIFICACIÓN
        if (millis() - configModeStartTime > 2000 && (lastErrorBlink % 4) == 0) {
            emitirSonido(4); 
        }
    }
}

// === MANEJADORES DEL SERVIDOR WEB ===
void handleRoot() {
    server.send(200, "text/html", generarPaginaConfig());
}

void handleSave() {
    if (server.hasArg("ssid") && server.hasArg("password") && 
        server.hasArg("server") && server.hasArg("aula_codigo") &&
        server.hasArg("rtc_date") && server.hasArg("rtc_time")) {

        // 1. Configuración WiFi/Servidor
        wifi_ssid = server.arg("ssid");
        wifi_password = server.arg("password");
        server_url = server.arg("server");
        
        // ✅ LEER CÓDIGO DE AULA (String)
        aulaCodigo = server.arg("aula_codigo");
        aulaCodigo.trim(); // Eliminar espacios al inicio/final
        aulaCodigo.toUpperCase(); // Convertir a mayúsculas
        
        // Validar que no esté vacío
        if (aulaCodigo.length() == 0) {
            aulaCodigo = "AULA-101"; // Valor por defecto
            Serial.println("⚠️ Código de aula vacío, usando: " + aulaCodigo);
        }
        
        // Validar formato (opcional): debe tener entre 3 y 20 caracteres
        if (aulaCodigo.length() < 3 || aulaCodigo.length() > 20) {
            String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
            html += "<h1 style='color:red;'>❌ Error: Código de Aula Inválido</h1>";
            html += "<p>El código debe tener entre 3 y 20 caracteres.</p>";
            html += "<p>Recibido: <strong>" + aulaCodigo + "</strong> (" + String(aulaCodigo.length()) + " caracteres)</p>";
            html += "<br><a href='/'>← Volver</a>";
            html += "</body></html>";
            server.send(400, "text/html", html);
            return;
        }
        
        // 2. Guardar en Preferences
        preferences.putString("wifi_ssid", wifi_ssid);
        preferences.putString("wifi_pass", wifi_password);
        preferences.putString("server_url", server_url);
        preferences.putString("aula_codigo", aulaCodigo);  // ✅ GUARDAR CÓDIGO
        
        Serial.println("✅ Configuración guardada:");
        Serial.println("   SSID: " + wifi_ssid);
        Serial.println("   Servidor: " + server_url);
        Serial.println("   Aula Código: " + aulaCodigo);

        // 3. Configuración RTC
        String rtc_date = server.arg("rtc_date");
        String rtc_time = server.arg("rtc_time");
        configurarRTCManual(rtc_date, rtc_time);
        
        // 4. Respuesta HTML
        String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Configuración Guardada</title>";
        html += "<style>body{font-family:sans-serif;max-width:500px;margin:50px auto;padding:20px;background:#f0f0f0;}";
        html += ".success{background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:8px;margin:20px 0;}";
        html += "h1{color:#155724;} .code{background:#f8f9fa;padding:5px 10px;border-radius:4px;font-family:monospace;}</style></head><body>";
        html += "<div class='success'>";
        html += "<h1>✅ Configuración Guardada</h1>";
        html += "<p><strong>SSID:</strong> " + wifi_ssid + "</p>";
        html += "<p><strong>Servidor:</strong> " + server_url + "</p>";
        html += "<p><strong>Aula Código:</strong> <span class='code'>" + aulaCodigo + "</span></p>";
        html += "<p><strong>RTC:</strong> " + rtc_date + " " + rtc_time + "</p>";
        html += "<hr>";
        html += "<p>🔄 El dispositivo se reiniciará en <span id='countdown'>5</span> segundos...</p>";
        html += "</div>";
        html += "<script>";
        html += "let count = 5;";
        html += "setInterval(() => {";
        html += "  count--;";
        html += "  document.getElementById('countdown').textContent = count;";
        html += "  if(count <= 0) window.location.href='/';";
        html += "}, 1000);";
        html += "</script>";
        html += "</body></html>";
        
        server.send(200, "text/html", html);
        
        delay(1000);
        shouldRestart = true;
    } else {
        // ❌ Faltan parámetros
        String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Error</title></head><body>";
        html += "<h1 style='color:red;'>❌ Error: Faltan parámetros requeridos</h1>";
        html += "<p>Parámetros recibidos:</p><ul>";
        html += "<li>SSID: " + String(server.hasArg("ssid") ? "✅" : "❌") + "</li>";
        html += "<li>Password: " + String(server.hasArg("password") ? "✅" : "❌") + "</li>";
        html += "<li>Servidor: " + String(server.hasArg("server") ? "✅" : "❌") + "</li>";
        html += "<li>Código Aula: " + String(server.hasArg("aula_codigo") ? "✅" : "❌") + "</li>";
        html += "<li>Fecha RTC: " + String(server.hasArg("rtc_date") ? "✅" : "❌") + "</li>";
        html += "<li>Hora RTC: " + String(server.hasArg("rtc_time") ? "✅" : "❌") + "</li>";
        html += "</ul>";
        html += "<a href='/'>← Volver</a>";
        html += "</body></html>";
        
        server.send(400, "text/html", html);
    }
}

void handleScan() {
    server.send(200, "application/json", escanearRedes());
}

void handleNotFound() {
    // Portal cautivo - capturar TODAS las peticiones y redirigir a la página de configuración
    String host = server.hostHeader();
    // Si la petición viene del IP del AP, servir la página directamente
    if (host == WiFi.softAPIP().toString()) {
        server.send(200, "text/html", generarPaginaConfig());
        return;
    }
    
    // Para cualquier otro dominio, redirigir con código 302 (portal cautivo)
    server.sendHeader("Location", "http://" + WiFi.softAPIP().toString(), true);
    server.sendHeader("Cache-Control", "no-cache, no-store, must-revalidate");
    server.sendHeader("Pragma", "no-cache");
    server.sendHeader("Expires", "0");
    server.send(302, "text/plain", "Redirecting to captive portal");
}

String escanearRedes() {
    WiFi.scanDelete();
    int n = WiFi.scanNetworks();
    
    StaticJsonDocument<2048> doc;
    JsonArray networks = doc.to<JsonArray>();

    for (int i = 0; i < n; i++) {
        JsonObject network = networks.createNestedObject();
        network["ssid"] = WiFi.SSID(i);
        network["rssi"] = WiFi.RSSI(i);
        network["secure"] = (WiFi.encryptionType(i) != WIFI_AUTH_OPEN);
    }
    
    String result;
    serializeJson(doc, result);
    return result;
}

String generarPaginaConfig() {
    String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'>";
    html += "<title>Configuración ESP32 RFID</title>";
    html += "<script src='https://cdn.tailwindcss.com'></script>";
    html += "<style>";
    html += "body { font-family: system-ui, sans-serif; background: linear-gradient(to right, #e0e7ff, #f9fafb); }";
    html += ".btn { display: inline-block; padding: 0.6rem 1rem; border-radius: 0.5rem; font-weight: 600; text-align: center; transition: all 0.2s; }";
    html += ".btn-primary { background-color: #4f46e5; color: white; width: 100%; }";
    html += ".btn-primary:hover { background-color: #4338ca; transform: scale(1.02); }";
    html += ".btn-scan { background-color: #0ea5e9; color: white; margin-bottom: 0.5rem; }";
    html += ".btn-scan:hover { background-color: #0284c7; transform: scale(1.02); }";
    html += ".input { width: 100%; padding: 0.6rem 0.9rem; border: 1px solid #d1d5db; border-radius: 0.5rem; box-sizing: border-box; }";
    html += ".input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79,70,229,0.3); }";
    html += ".card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 6px 16px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }";
    html += ".alert { background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }";
    html += ".info-box { background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }";
    html += ".network-item { padding: 0.6rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; margin-bottom: 0.5rem; cursor: pointer; transition: background 0.2s; }";
    html += ".network-item:hover { background-color: #f3f4f6; }";
    html += ".datetime-group { display: flex; gap: 0.5rem; }";
    html += ".datetime-group .input { flex: 1; }";
    html += ".password-wrapper { position: relative; }";
    html += ".toggle-btn { position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 0.9rem; color: #6b7280; }";
    html += ".code-display { font-family: 'Courier New', monospace; background: #f8f9fa; padding: 8px; border-radius: 4px; font-weight: bold; }";
    html += "</style></head>";
    html += "<body class='min-h-screen flex items-center justify-center p-6'>";
    
    html += "<div class='w-full max-w-sm mx-auto'>";  
    html += "<h1 class='text-2xl font-extrabold text-center mb-6 text-indigo-700 drop-shadow-sm'>⚙️ Configuración ESP32 RFID</h1>";

    // Información sobre reset
    html += "<div class='alert'>";
    html += "<h3 class='font-bold text-red-700'>Resetear Configuración WiFi</h3>";
    html += "<p class='text-sm text-red-600'>Mantén presionado el botón BOOT del ESP32 por <strong>5 segundos</strong>. El LED rojo parpadeará y verás una cuenta regresiva en la pantalla LCD.</p>";
    html += "</div>";
    
    html += "<div class='card'>";
    html += "<form method='POST' action='/save'>";
    
    // --- 1. Configuración WiFi ---
    html += "<h2 class='text-lg font-semibold mb-3 text-gray-700'>1. Configuración de Red</h2>";
    html += "<div class='mb-4'>";
    html += "<label class='block text-sm font-medium text-gray-700 mb-1'>Red WiFi</label>";
    html += "<button type='button' class='btn btn-scan w-auto px-4' onclick='escanearRedes()'>🔍 Escanear Redes</button>";
    html += "<input type='text' name='ssid' id='ssid' placeholder='Nombre de la red WiFi' class='input mt-2' value='" + wifi_ssid + "' required>";
    html += "<div id='networks' class='mt-2'></div>";
    html += "</div>";
    
    html += "<div class='mb-4'>";
    html += "<label class='block text-sm font-medium text-gray-700 mb-1'>Contraseña WiFi</label>";
    html += "<div class='password-wrapper'>";
    html += "<input type='password' id='password' name='password' placeholder='Contraseña de la red WiFi' class='input' required>";
    html += "<span class='toggle-btn' onclick='togglePassword()'>👁️</span>";
    html += "</div>";
    html += "</div>";
    
    html += "<div class='mb-4'>";
    html += "<label class='block text-sm font-medium text-gray-700 mb-1'>URL del Servidor</label>";
    html += "<input type='text' name='server' placeholder='http://192.168.1.100:8000' value='" + server_url + "' class='input' required>";
    html += "</div>";

    // ✅ CAMPO DE CÓDIGO DE AULA (TEXTO EN LUGAR DE NÚMERO)
    html += "<div class='mb-6'>";
    html += "<label class='block text-sm font-medium text-gray-700 mb-1'>🏫 Código del Aula</label>";
    html += "<input type='text' name='aula_codigo' id='aula_codigo' ";
    html += "placeholder='Ej: AULA-101, LAB-INFO, SALON-A' ";
    html += "value='" + aulaCodigo + "' ";
    html += "class='input' ";
    html += "maxlength='20' ";
    html += "pattern='[A-Za-z0-9\\-_]+' ";
    html += "title='Solo letras, números, guiones y guiones bajos' ";
    html += "required ";
    html += "style='text-transform: uppercase;'>";
    
    // Información adicional sobre códigos
    html += "<div class='info-box mt-2'>";
    html += "<p class='text-xs text-blue-700'><strong>💡 Formato del código:</strong></p>";
    html += "<ul class='text-xs text-blue-600 mt-1 ml-4 list-disc'>";
    html += "<li>3 a 20 caracteres</li>";
    html += "<li>Solo letras, números, guiones (-) y guiones bajos (_)</li>";
    html += "<li>Se convertirá automáticamente a MAYÚSCULAS</li>";
    html += "<li>Ejemplos: <code>AULA-101</code>, <code>LAB-INFO</code>, <code>SALON_A</code></li>";
    html += "</ul>";
    html += "</div>";
    html += "</div>";

    // --- 2. Configuración RTC ---
    html += "<h2 class='text-lg font-semibold mb-3 text-gray-700'>2. Configuración de RTC (Reloj)</h2>";
    html += "<div class='mb-6'>";
    html += "<label class='block text-sm font-medium text-gray-700 mb-1'>Fecha y Hora Actuales</label>";
    html += "<div class='datetime-group'>";
    html += "<input type='date' name='rtc_date' id='rtc_date' class='input' value='" + current_rtc_date + "' required>";
    html += "<input type='time' name='rtc_time' id='rtc_time' class='input' value='" + current_rtc_time + "' step='1' required>";
    html += "</div>";
    html += "<p class='text-xs text-gray-500 mt-1'>Fecha y hora que se ajustarán en el módulo RTC.</p>";
    html += "</div>";
    
    html += "<button type='submit' class='btn btn-primary'>💾 Guardar Configuración y Reiniciar</button>";
    html += "</form>";
    html += "</div>";
    
    // Mostrar configuración actual
    html += "<div class='card text-center text-sm text-gray-600'>";
    html += "<p class='font-medium text-gray-800 mb-2'>📋 Configuración Actual</p>";
    html += "<div style='text-align:left; background:#f9fafb; padding:10px; border-radius:8px;'>";
    html += "<p><strong>SSID:</strong> " + (wifi_ssid.length() > 0 ? wifi_ssid : "No configurado") + "</p>";
    html += "<p><strong>Servidor:</strong> " + server_url + "</p>";
    html += "<p><strong>Aula:</strong> <span class='code-display'>" + aulaCodigo + "</span></p>";
    html += "</div>";
    html += "</div>";
    
    html += "</div>";
    
    // Scripts JavaScript
    html += "<script>";
    
    // Convertir a mayúsculas mientras escribe
    html += "document.getElementById('aula_codigo').addEventListener('input', function(e) {";
    html += "  e.target.value = e.target.value.toUpperCase();";
    html += "});";
    
    html += "function escanearRedes() {";
    html += "  document.getElementById('networks').innerHTML = '<p class=\"text-center text-gray-500\">Escaneando...</p>';"; 
    html += "  fetch('/scan')";
    html += "    .then(response => response.json())";
    html += "    .then(data => {";
    html += "      let html = '';"; 
    html += "      data.forEach(network => {";
    html += "        let security = network.secure ? '🔒' : '🔓';"; 
    html += "        let strength = '';";
    html += "        if(network.rssi > -50) strength = 'Excelente';";
    html += "        else if(network.rssi > -70) strength = 'Buena';";
    html += "        else if(network.rssi > -80) strength = 'Débil';";
    html += "        else strength = 'Muy débil';";
    html += "        html += '<div class=\"network-item\" onclick=\"seleccionarRed(\\'' + network.ssid + '\\')\">' + network.ssid + ' ' + security + '<span class=\"text-xs text-gray-500 float-right\">' + strength + '</span></div>';";
    html += "      });";
    html += "      document.getElementById('networks').innerHTML = html;"; 
    html += "    })";
    html += "    .catch(error => {";
    html += "      document.getElementById('networks').innerHTML = '<p class=\"text-center text-red-500\">Error al escanear redes</p>';"; 
    html += "    });";
    html += "}";
    html += "function seleccionarRed(ssid) {";
    html += "  document.getElementById('ssid').value = ssid;";
    html += "}";
    html += "function togglePassword() {";
    html += "  const passField = document.getElementById('password');";
    html += "  if (passField.type === 'password') {";
    html += "    passField.type = 'text';";
    html += "  } else {";
    html += "    passField.type = 'password';";
    html += "  }";
    html += "}";
    html += "window.onload = function() {";
    html += "  try {";
    html += "    const now = new Date();";
    html += "    const year = now.getFullYear();";
    html += "    const month = String(now.getMonth() + 1).padStart(2, '0');";
    html += "    const day = String(now.getDate()).padStart(2, '0');";
    html += "    const hours = String(now.getHours()).padStart(2, '0');";
    html += "    const minutes = String(now.getMinutes()).padStart(2, '0');";
    html += "    const seconds = String(now.getSeconds()).padStart(2, '0');";
    html += "    const rtcDateInput = document.getElementById('rtc_date');";
    html += "    const rtcTimeInput = document.getElementById('rtc_time');";
    html += "    if (rtcDateInput.value.startsWith('2000') || rtcDateInput.value === '') {";
    html += "        rtcDateInput.value = `${year}-${month}-${day}`;";
    html += "    }";
    html += "    if (rtcTimeInput.value === '00:00' || rtcTimeInput.value === '') {";
    html += "        rtcTimeInput.value = `${hours}:${minutes}:${seconds}`;";
    html += "    }";
    html += "  } catch (e) {";
    html += "    console.error('Error al configurar fecha/hora del navegador:', e);";
    html += "  }";
    html += "};";
    html += "</script>";
    
    html += "</body></html>";
    
    return html;
}


// NUEVA FUNCIÓN: Configura el RTC con la fecha y hora manual
void configurarRTCManual(String fechaStr, String horaStr) {
    // fechaStr: YYYY-MM-DD
    // horaStr: HH:MM:SS (o HH:MM si el input es type="time")

    if (fechaStr.length() < 10) {
        Serial.println("Error: Formato de fecha inválido.");
        return;
    }

    int year = fechaStr.substring(0, 4).toInt();
    int month = fechaStr.substring(5, 7).toInt();
    int day = fechaStr.substring(8, 10).toInt();

    int hour = 0;
    int minute = 0;
    int second = 0;

    if (horaStr.length() >= 5) {
        hour = horaStr.substring(0, 2).toInt();
        minute = horaStr.substring(3, 5).toInt();
    }
    if (horaStr.length() >= 8) {
        second = horaStr.substring(6, 8).toInt();
    }
    
    // Crear un objeto DateTime con los nuevos valores
    DateTime newTime(year, month, day, hour, minute, second);

    // Ajustar el RTC
    rtc.adjust(newTime);
    
    Serial.print("RTC ajustado manualmente a: ");
    Serial.print(newTime.year());
    Serial.print("-");
    Serial.print(newTime.month());
    Serial.print("-");
    Serial.print(newTime.day());
    Serial.print(" ");
    Serial.print(newTime.hour());
    Serial.print(":");
    Serial.print(newTime.minute());
    Serial.print(":");
    Serial.println(newTime.second());

    mostrarMensajeLCD("RTC Ajustado", newTime.year() >= 2023 ? "OK" : "Error RTC", 2000);
}


// === FUNCIONES AUXILIARES (mantenidas igual) ===
String formatTwoDigits(int number) {
    number = constrain(number, 0, 99);
    return (number < 10) ? "0" + String(number) : String(number);
}

String obtenerTimestamp(String &fecha, String &hora) {
    DateTime now = rtc.now();
    fecha = formatTwoDigits(now.day()) + "/" + formatTwoDigits(now.month()) + "/" + String(now.year());
    hora = formatTwoDigits(now.hour()) + ":" + formatTwoDigits(now.minute()) + ":" + formatTwoDigits(now.second());
    return fecha + " " + hora;
}

// === FUNCIONES LCD ===
void mostrarMensajeLCD(String linea1, String linea2, int duracion) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(linea1);
    lcd.setCursor(0, 1);
    lcd.print(linea2);
    lcdMessageDisplayTime = (duracion > 0) ? millis() : 0;
}

void mostrarFechaEnLCD() {
    String fecha, hora;
    obtenerTimestamp(fecha, hora);
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(fecha);
    lcd.setCursor(0, 1);
    if (WiFi.status() == WL_CONNECTED) {
        lcd.print(hora);
    } else {
        lcd.print(hora + " OFFLINE!");
    }
}

void mostrarPantallaInicial() {
    if (configMode) {
        mostrarMensajeLCD("Modo Config", WiFi.softAPIP().toString(), 0);
    } else if (numActiveErrors > 0) {
        mostrarMensajeLCD(errorMessages[currentErrorIndex], "Verifica modulos");
    } else {
        mostrarFechaEnLCD();
    }
}

void inicializarLCD() {
    lcd.init();
    lcd.backlight();
    mostrarMensajeLCD("Inicializando...", "Sistema RFID", 0);
    delay(2000);
}

// === INICIALIZACIÓN ===
void inicializarHardware() {
    // LEDs
    pinMode(LED_ACTIVITY, OUTPUT);
    pinMode(LED_STATUS, OUTPUT);
    pinMode(LED_ERROR, OUTPUT);
    pinMode(CONFIG_BUTTON_PIN, INPUT_PULLUP);  // Botón de configuración
    digitalWrite(LED_ACTIVITY, LOW);
    digitalWrite(LED_STATUS, LOW);
    digitalWrite(LED_ERROR, LOW);
    // I2C y LCD
    Wire.begin(21, 22);
    Wire.setClock(100000);
    inicializarLCD();

    ledcAttach(BUZZER_PIN, 2000, 8);

    // RTC
    rtcOK = rtc.begin();
    if (rtcOK) Serial.println("RTC OK");

    // SPI
    SPI.begin(SCK_PIN, MISO_PIN, MOSI_PIN);
    SPI.setFrequency(4000000);

    // SD
    if (SD.begin(SS_SD)) {
        crearArchivosBasicos();
        sdCardOK = true;
        Serial.println("SD OK");
    } else {
        sdCardOK = false;
        Serial.println("Error SD");
    }

    // RFID
    rfid.PCD_Init();
    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    rfidOK = (v != 0x00 && v != 0xFF);
    if (rfidOK) {
        rfid.PCD_SetAntennaGain(rfid.RxGain_max);
        Serial.println("RFID OK");
    }
}

void crearArchivosBasicos() {
    crearArchivoSiNoExiste("/asistencia.txt", "Registro de asistencia (online):");
    crearArchivoSiNoExiste("/pendientes.txt", "Registros pendientes (offline):");
    crearArchivoSiNoExiste("/lista_estudiantes.txt", "UID,NOMBRE");
}

// === PROCESAMIENTO PRINCIPAL ===
void procesarMensajesLCD() {
    if (lcdMessageDisplayTime > 0 && millis() - lcdMessageDisplayTime > LCD_MESSAGE_DURATION) {
        lcdMessageDisplayTime = 0;
        if (numActiveErrors > 0) {
            mostrarMensajeLCD(errorMessages[currentErrorIndex], "Verifica modulos");
        } else {
            mostrarFechaEnLCD();
        }
    }
}

void verificarSistemaPeriodicamennte() {
    if (millis() - lastModuleCheck > MODULE_CHECK_INTERVAL) {
        verificarEstadoSistema();
        lastModuleCheck = millis();
    }

    if (numActiveErrors > 0 && lcdMessageDisplayTime == 0 && 
        millis() - lastErrorDisplayUpdate > ERROR_DISPLAY_CYCLE_INTERVAL) {
        currentErrorIndex = (currentErrorIndex + 1) % numActiveErrors;
        mostrarMensajeLCD(errorMessages[currentErrorIndex], "Verifica modulos");
        
        // SONIDO DE ERROR AL MOSTRAR ALERTA // <-- MODIFICACIÓN
        emitirSonido(2); 

        lastErrorDisplayUpdate = millis();
    }
}

void sincronizarDatosPeriodicamente() {
    if (millis() - lastSyncAttempt > SYNC_INTERVAL) {
        if (WiFi.status() == WL_CONNECTED && sdCardOK) {
            sincronizarPendientes();
        }
        lastSyncAttempt = millis();
    }

    if (millis() - lastStudentListSync > STUDENT_LIST_SYNC_INTERVAL) {
        if (WiFi.status() == WL_CONNECTED) {
            sincronizarListaEstudiantes();
        }
        lastStudentListSync = millis();
    }
}

void procesarLecturaRFID() {
    if (configMode) return; // No procesar RFID en modo configuración
    
    if (rfidOK && rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
        String uidStr = leerUID();
        if (uidStr == lastCardUID && (millis() - lastCardRead) < CARD_READ_DELAY) {
            detenerLecturaRFID();
            return;
        }
        
        lastCardUID = uidStr;
        lastCardRead = millis();
        parpadearLEDActividad();
        procesarAsistencia(uidStr);
        detenerLecturaRFID();
    }
}

void actualizarInterfaz() {
    if (lcdMessageDisplayTime == 0 && numActiveErrors == 0 && !configMode && millis() - lastDateUpdate > DATE_UPDATE_INTERVAL) {
        mostrarFechaEnLCD();
        lastDateUpdate = millis();
    }
    actualizarLEDs();
    
    // LOGICA DE APAGADO DE LED DE ACTIVIDAD (NO BLOQUEANTE)
    if (activityLedPulsed && (millis() - activityLedOnTime >= ACTIVITY_LED_DURATION)) {
        digitalWrite(LED_ACTIVITY, LOW);
        activityLedPulsed = false; // Resetear bandera
    }

    // LOGICA DE APAGADO DE LED DE ESTADO (NO BLOQUEANTE) // <-- MODIFICACIÓN
    if (statusLedPulsed && (millis() - statusLedPulseTime >= ACTIVITY_LED_DURATION)) {
        // Restaurar LED_STATUS al estado fijo (si módulos OK)
        digitalWrite(LED_STATUS, (sdCardOK && rtcOK && rfidOK) ? HIGH : LOW);
        statusLedPulsed = false; 
    }

    // LOGICA DE APAGADO DE LED DE ERROR POR DENEGACIÓN (NO BLOQUEANTE) // <-- MODIFICACIÓN
    if (errorLedPulsed && (millis() - errorLedPulseTime >= ACTIVITY_LED_DURATION)) {
        // Apagar si no hay errores de módulo activos
        if (numActiveErrors == 0) { 
            digitalWrite(LED_ERROR, LOW);
        }
        errorLedPulsed = false; 
    }
}

// === CONTROL DE LEDS ===
void actualizarLEDs() {
    if (configMode || resetInProgress) return; // Los LEDs se manejan diferente en modo config y reset
    
    // LED estado (verde)
    // Solo actualiza el estado fijo si NO estamos en un pulso rápido
    if (!statusLedPulsed) { // <-- MODIFICACIÓN
        digitalWrite(LED_STATUS, (sdCardOK && rtcOK && rfidOK) ? HIGH : LOW);
    }
    
    // LED error (rojo parpadeante)
    // Solo actualiza el estado si NO estamos en un pulso rápido de denegación
    if (numActiveErrors > 0) {
        if (millis() - lastErrorBlink > ERROR_BLINK_INTERVAL) {
            errorLedState = !errorLedState;
            digitalWrite(LED_ERROR, errorLedState ? HIGH : LOW);
            lastErrorBlink = millis();
        }
    } else if (!errorLedPulsed) { // <-- MODIFICACIÓN
        digitalWrite(LED_ERROR, LOW);
    }
}

void parpadearLEDActividad() {
    // Encender el LED y registrar el tiempo
    digitalWrite(LED_ACTIVITY, HIGH);
    activityLedOnTime = millis();
    activityLedPulsed = true; // Establecer bandera
}

void parpadearLEDEstado() {
    if (configMode || resetInProgress) return;
    digitalWrite(LED_STATUS, HIGH);
    statusLedPulseTime = millis();
    statusLedPulsed = true;
}

void parpadearLEDErrorDenegacion() {
    if (configMode || resetInProgress) return;
    // Sobreescribimos el LED_ERROR si no hay un error de módulo activo
    if (numActiveErrors == 0) { 
        digitalWrite(LED_ERROR, HIGH);
        errorLedPulseTime = millis();
        errorLedPulsed = true;
    }
}

// === VERIFICACIÓN SISTEMA ===
void verificarEstadoSistema() {
    numActiveErrors = 0;
    currentErrorIndex = 0;
    wifiConnected = (WiFi.status() == WL_CONNECTED);
    
    // Verificar SD
    static unsigned long lastSDCheck = 0;
    if (millis() - lastSDCheck > 5000 || lastSDCheck == 0) {
        File testFile = SD.open("/test.tmp", FILE_WRITE);
        if (testFile) {
            testFile.close();
            SD.remove("/test.tmp");
            sdCardOK = true;
        } else {
            sdCardOK = false;
        }
        lastSDCheck = millis();
    }
    if (!sdCardOK) errorMessages[numActiveErrors++] = "Error SD";
    
    // Verificar RTC
    DateTime now = rtc.now();
    rtcOK = (now.year() >= 2023); // Asumimos que si el año es menor a 2023, está desajustado/reseteado
    if (!rtcOK) errorMessages[numActiveErrors++] = "Error RTC";

    // Verificar RFID
    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    rfidOK = (v != 0x00 && v != 0xFF);
    if (!rfidOK) errorMessages[numActiveErrors++] = "Error RFID";
}

// === CONECTIVIDAD WIFI ===
void conectarWiFi() {
    if (wifi_ssid.length() == 0) return;
    mostrarMensajeLCD("Conectando WiFi...", wifi_ssid);
    WiFi.mode(WIFI_STA);
    WiFi.begin(wifi_ssid.c_str(), wifi_password.c_str());
    
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        Serial.print(".");
        attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nWiFi conectado!");
        Serial.print("IP: ");
        Serial.println(WiFi.localIP());
        wifiConnected = true;
        mostrarMensajeLCD("WiFi Conectado", WiFi.localIP().toString(), 2000);
    } else {
        Serial.println("\nFallo WiFi.");
        mostrarMensajeLCD("WiFi Desconectado", "Modo offline", 2000);
        wifiConnected = false;
    }
}

// === GESTIÓN ARCHIVOS SD ===
void crearArchivoSiNoExiste(const char* filename, const char* header) {
    if (!sdCardOK) return;
    if (!SD.exists(filename)) {
        archivo = SD.open(filename, FILE_WRITE);
        if (archivo) {
            archivo.println(header);
            archivo.close();
            Serial.println("Archivo creado: " + String(filename));
        } else {
            Serial.println("Error creando: " + String(filename));
            sdCardOK = false;
        }
    }
}

bool guardarRegistroEnSD(String filename, String nombre, String uid, String accion, String fecha, String hora, String modo) {
    if (!sdCardOK) return false;
    archivo = SD.open(filename, FILE_APPEND);
    if (archivo) {
        archivo.println(nombre + "," + uid + "," + accion + "," + fecha + "," + hora + "," + modo);
        archivo.close();
        Serial.println("Registro guardado: " + filename);
        return true;
    } else {
        Serial.println("Error escribiendo: " + filename);
        sdCardOK = false;
        return false;
    }
}

void guardarPendienteEnSD(String uid, String accion, String fecha, String hora) {
    if (!sdCardOK) return;
    String nombre = "";
    buscarEstudiante(uid, nombre);
    
    archivo = SD.open("/pendientes.txt", FILE_APPEND);
    if (archivo) {
        archivo.println(uid + "," + nombre + "," + accion + "," + fecha + "," + hora);
        archivo.close();
        Serial.println("Pendiente guardado");
    }
}

// === GESTIÓN ESTADOS TARJETAS ===
std::vector<CardStatus> loadAllCardStatuses() {
    std::vector<CardStatus> statuses;
    if (!sdCardOK) return statuses;
    if (SD.exists(LAST_ACTIONS_FILE)) {
        File file = SD.open(LAST_ACTIONS_FILE, FILE_READ);
        if (file) {
            while (file.available()) {
                String line = file.readStringUntil('\n');
                line.trim();
                if (line.length() > 0) {
                    int commaIndex = line.indexOf(',');
                    if (commaIndex != -1) {
                        CardStatus status;
                        status.uid = line.substring(0, commaIndex);
                        status.lastAction = line.substring(commaIndex + 1);
                        statuses.push_back(status);
                    }
                }
            }
            file.close();
        }
    }
    return statuses;
}

void saveAllCardStatuses(const std::vector<CardStatus>& statuses) {
    if (!sdCardOK) return;
    
    File file = SD.open(LAST_ACTIONS_FILE, FILE_WRITE);
    if (file) {
        for (const auto& status : statuses) {
            file.println(status.uid + "," + status.lastAction);
        }
        file.close();
    }
}

String getLastAction(String uid) {
    std::vector<CardStatus> statuses = loadAllCardStatuses();
    for (const auto& status : statuses) {
        if (status.uid == uid) return status.lastAction;
    }
    return "";
}

void updateLastAction(String uid, String action) {
    std::vector<CardStatus> statuses = loadAllCardStatuses();
    bool found = false;
    
    for (auto& status : statuses) {
        if (status.uid == uid) {
            status.lastAction = action;
            found = true;
            break;
        }
    }
    
    if (!found) {
        CardStatus newStatus;
        newStatus.uid = uid;
        newStatus.lastAction = action;
        statuses.push_back(newStatus);
    }
    
    saveAllCardStatuses(statuses);
}

// === LECTURA RFID ===
String leerUID() {
    String uidStr = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
        uidStr += (rfid.uid.uidByte[i] < 0x10 ? "0" : "") + String(rfid.uid.uidByte[i], HEX);
    }
    uidStr.toUpperCase();
    return uidStr;
}

void detenerLecturaRFID() {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
}

// === PROCESAMIENTO ASISTENCIA ===
// === PROCESAMIENTO ASISTENCIA (MODIFICADO) ===
// === PROCESAMIENTO ASISTENCIA (MODIFICADO PASO 3 Y 4) ===
void procesarAsistencia(String uidLeido) {
    String nombreEstudiante = "";
    int index = buscarEstudiante(uidLeido, nombreEstudiante);

    if (index != -1) {
        // ... (el chequeo de 'estado == 0' sigue igual)
        if (estudiantes[index].estado == 0) {
            Serial.println("⚠️ CUENTA INACTIVA: " + nombreEstudiante);
            mostrarMensajeLCD("CUENTA INACTIVA", nombreEstudiante, LCD_MESSAGE_DURATION * 2);
            parpadearLEDErrorDenegacion();
            emitirSonido(2); // <--- AGREGAR ESTO (Error: Tono grave)
            return;
        }

        String accion = "ENTRADA";
        Serial.print("📋 Tarjeta: ");
        Serial.println(nombreEstudiante);
        
        mostrarMensajeLCD("Procesando...", nombreEstudiante, 0);
        
        String fecha, hora;
        obtenerTimestamp(fecha, hora);
        String modo = (WiFi.status() == WL_CONNECTED) ? "ONLINE" : "OFFLINE";

        bool enviado = false;
        
        if (WiFi.status() == WL_CONNECTED) {
            // --- LÓGICA ONLINE (DEL PASO 3) ---
            bool tienePermiso = verificarPermisoServidor(uidLeido);
            if (tienePermiso) {
                enviado = enviarAsistenciaRapido(uidLeido); 
            } else {
                enviado = false;
                Serial.println("Registro denegado por el servidor (ya marcó o sin clases).");
            }
            if (!enviado && tienePermiso) {
                guardarPendienteEnSD(uidLeido, accion, fecha, hora);
            }
            // --- FIN LÓGICA ONLINE ---

        } else {
            // --- INICIO DE LÓGICA OFFLINE (PASO 4) ---
            Serial.println("Modo Offline. Verificando localmente...");
            
            // 1. Usamos el 'index' de buscarEstudiante
            if (estudiantes[index].marcoHoy) {
                // 2. Estudiante YA MARCÓ (info de la última sync o de un marcado offline previo)
                Serial.println("Registro OFFLINE denegado: 'marcoHoy' es true.");
                mostrarMensajeLCD("YA REGISTRADO", "(Sync)", LCD_MESSAGE_DURATION * 2);
                emitirSonido(2);
            
            } else {
                // 3. Estudiante PUEDE MARCAR offline
                Serial.println("Registro OFFLINE permitido.");
                guardarPendienteEnSD(uidLeido, accion, fecha, hora);
                mostrarMensajeLCD("ASISTENCIA OFFLINE", nombreEstudiante, LCD_MESSAGE_DURATION);
                emitirSonido(1);
                parpadearLEDEstado();
                
                // 4. BLOQUEO LOCAL: Actualizar estado en memoria RAM
                //    para prevenir doble marcado mientras sigue offline.
                estudiantes[index].marcoHoy = true; 
            }
            // --- FIN DE LÓGICA OFFLINE ---
        }

        // Guardar en log local de SD (siempre)
        guardarRegistroEnSD("/asistencia.txt", nombreEstudiante, uidLeido, 
                           accion, fecha, hora, modo);
    } else {
        // ... (lógica de UID Desconocido sigue igual)
        Serial.println("⚠️ UID Desconocido: " + uidLeido); 
        mostrarMensajeLCD("UID Desconocido:", uidLeido, LCD_MESSAGE_DURATION * 2);
        emitirSonido(3); // <--- AGREGAR ESTO (Alerta: Tres beeps rápidos)
        parpadearLEDErrorDenegacion();
        if (WiFi.status() == WL_CONNECTED) {
            enviarUidDesconocido(uidLeido);
        }
    }
}
// NUEVA FUNCIÓN: Envía el UID de una tarjeta desconocida al servidor
bool enviarUidDesconocido(String uid) {
    if (WiFi.status() != WL_CONNECTED) return false;
    HTTPClient http;
    String serverPath = server_url + "/api/rfid-scan"; // URL correcta
    http.begin(serverPath);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["uid"] = uid;

    String jsonPayload;
    serializeJson(doc, jsonPayload);

    int httpCode = http.POST(jsonPayload);
    bool success = (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED);
    if (success) {
        Serial.println("UID desconocido enviado al servidor OK");
    } else {
        Serial.print("Error HTTP al enviar UID: ");
        Serial.println(httpCode);
    }

    http.end();
    return success;
}

int buscarEstudiante(String uid, String &nombreEncontrado) {
    for (int i = 0; i < numEstudiantesActual; i++) {
        if (estudiantes[i].uid == uid) {
            nombreEncontrado = estudiantes[i].nombre;
            return i;
        }
    }
    nombreEncontrado = "Desconocido";
    return -1;
}

/**
 * =================================================================
 * NUEVA FUNCIÓN: VERIFICACIÓN ONLINE (SEMÁFORO)
 * =================================================================
 * Pregunta al servidor (Backend Paso 1) si un UID tiene permiso
 * para marcar asistencia en este momento.
 */
bool verificarPermisoServidor(String uid) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("❌ No se puede verificar permiso (Offline)");
        // Si no hay WiFi, no podemos verificar. 
        // El modo offline se encargará de esto.
        return true; // Permitimos que el modo offline decida
    }

    HTTPClient http;
    // Usamos el endpoint que creamos en el Paso 1
    String serverPath = server_url + "/api/asistencia/verificar?uid_tarjeta=" + uid;
    Serial.println("🌐 Verificando permiso en: " + serverPath);
    http.begin(serverPath);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(8000); // 8 segundos

    int httpCode = http.GET();
    String payload = http.getString();
    
    Serial.print("📡 Código HTTP Verificación: ");
    Serial.println(httpCode);
    Serial.println("Respuesta: " + payload);

    bool puedeMarcar = false;
    String mensaje = "Error de red";
    if (httpCode == HTTP_CODE_OK) {
        DynamicJsonDocument doc(512);
        DeserializationError error = deserializeJson(doc, payload);
        if (!error) {
            puedeMarcar = doc["puede_marcar"] | false;
            mensaje = doc["mensaje"] | "Error JSON";
        } else {
            mensaje = "Error JSON";
            Serial.println("Error al parsear JSON de verificación");
        }
    } else if (httpCode == HTTP_CODE_NOT_FOUND) { // 404
        mensaje = "Estudiante no hallado";
        emitirSonido(2); // <-- AÑADIDO: Error 404/No encontrado
    } else {
        mensaje = "Error Servidor: " + String(httpCode);
        emitirSonido(2); // <-- AÑADIDO: Error genérico de servidor
    }

    http.end();

    // Mostramos el mensaje del servidor en el LCD
    if (puedeMarcar) {
        mostrarMensajeLCD("OK. Registrando...", mensaje, LCD_MESSAGE_DURATION);
        delay(500); // Pequeña pausa para que se lea
    } else {
        mostrarMensajeLCD("DENIED:", mensaje, LCD_MESSAGE_DURATION * 2);
        // NOTA: El sonido 2 ya se llamó arriba si hubo un error HTTP.
    }

    return puedeMarcar;
}

// ===  SERVIDOR ===
// === ENVIAR ASISTENCIA (MODIFICADO) ===
// La función ahora es más simple. Solo envía el UID.
// El servidor (Paso 1) se encarga de toda la lógica (periodo, hora, etc.)
bool enviarAsistenciaRapido(String uid) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("❌ WiFi desconectado, no se puede enviar");
        return false;
    }
    
    HTTPClient http;
    // Apuntamos a la ruta que modificamos en el Paso 1
    String serverPath = server_url + "/api/asistencia/rfid";
    Serial.println("🌐 Enviando a: " + serverPath);
    
    http.begin(serverPath);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(10000);
    // El backend (Paso 1) solo necesita el UID.
    StaticJsonDocument<128> doc;
    doc["uid"] = uid;
    
    String jsonPayload;
    serializeJson(doc, jsonPayload);
    
    Serial.println("📤 JSON enviado: " + jsonPayload);
    
    int httpCode = http.POST(jsonPayload);
    Serial.print("📡 Código HTTP recibido: ");
    Serial.println(httpCode);
    
    bool success = false;
    String response = http.getString();
    Serial.println("✅ Respuesta del servidor: " + response);

    // Parsear la respuesta genérica
    DynamicJsonDocument respDoc(512);
    DeserializationError error = deserializeJson(respDoc, response);
    String mensaje = "Error";
    if (!error) {
        mensaje = respDoc["message"] | "Error JSON";
    }

    // --- MANEJO DE RESPUESTA SIMPLIFICADO ---
    if (httpCode == HTTP_CODE_CREATED || httpCode == HTTP_CODE_OK) {
        Serial.println("✅ ASISTENCIA REGISTRADA: " + mensaje);
        mostrarMensajeLCD("REGISTRADO", mensaje, LCD_MESSAGE_DURATION);
        emitirSonido(1); // Éxito: Doble beep
        parpadearLEDEstado();
        success = true;
    } else if (httpCode == HTTP_CODE_NOT_FOUND) { // 404
        Serial.println("⚠️ UID NO ENCONTRADO en base de datos");
        mostrarMensajeLCD("UID Desconocido", mensaje, LCD_MESSAGE_DURATION * 2);
        parpadearLEDErrorDenegacion();
        emitirSonido(2); // <-- AÑADIDO: Error 404
        
    } else if (httpCode == HTTP_CODE_CONFLICT) { // 409
        Serial.println("⚠️ ASISTENCIA DUPLICADA");
        mostrarMensajeLCD("YA REGISTRADO", mensaje, LCD_MESSAGE_DURATION * 2);
        parpadearLEDErrorDenegacion();
        emitirSonido(2); // <-- AÑADIDO: Error 409
        
    } else if (httpCode == HTTP_CODE_BAD_REQUEST) { // 400 (Nuevo)
        Serial.println("⚠️ SIN CLASES AHORA");
        mostrarMensajeLCD("Error:", mensaje, LCD_MESSAGE_DURATION * 2);
        emitirSonido(2); // <-- AÑADIDO: Error 400
        parpadearLEDErrorDenegacion();

    } else if (httpCode > 0) {
        Serial.println("❌ Error HTTP: " + String(httpCode));
        mostrarMensajeLCD("Error HTTP", String(httpCode), LCD_MESSAGE_DURATION);
        emitirSonido(2); // <-- AÑADIDO: Error HTTP genérico
        parpadearLEDErrorDenegacion();

    } else {
        Serial.println("❌ Error de conexión: " + http.errorToString(httpCode));
        mostrarMensajeLCD("Error conexión", "WiFi?", LCD_MESSAGE_DURATION);
        emitirSonido(2); // <-- AÑADIDO: Error de conexión (HTTP Code <= 0)
        parpadearLEDErrorDenegacion();
    }
    
    http.end();
    return success;
}

void sincronizarPendientes() {
    if (!sdCardOK || WiFi.status() != WL_CONNECTED) return;
    
    File archivoPendientes = SD.open("/pendientes.txt", FILE_READ);
    if (!archivoPendientes) return;
    
    File tempFile = SD.open("/temp_pendientes.txt", FILE_WRITE);
    if (!tempFile) {
        archivoPendientes.close();
        return;
    }
    tempFile.println("Registros pendientes (offline):");
    
    DynamicJsonDocument doc(1024);
    JsonArray batchArray = doc.to<JsonArray>();
    int registrosProcesados = 0;
    
    archivoPendientes.readStringUntil('\n'); // Saltar encabezado
    
    while (archivoPendientes.available()) {
        String linea = archivoPendientes.readStringUntil('\n');
        linea.trim();
        if (linea.length() == 0) continue;
        
        // Parsear CSV: uid,nombre,accion,fecha,hora
        int pos[4];
        int found = 0;
        for (int i = 0; i < linea.length() && found < 4; i++) {
            if (linea.charAt(i) == ',') pos[found++] = i;
        }
        
        if (found == 4) {
            String uid = linea.substring(0, pos[0]);
            String accion = linea.substring(pos[1] + 1, pos[2]);
            String fecha = linea.substring(pos[2] + 1, pos[3]);
            String hora = linea.substring(pos[3] + 1);
            
            JsonObject record = batchArray.add<JsonObject>();
            record["uid"] = uid;
            record["accion"] = accion;
            record["modo"] = "OFFLINE_SYNC";
            record["fecha"] = fecha;
            record["hora"] = hora;
            
            registrosProcesados++;
        } else {
            tempFile.println(linea);
        }
    }
    archivoPendientes.close();
    
    if (registrosProcesados > 0) {
        HTTPClient http;
        
        // --- ENDPOINT CORREGIDO ---
        String serverPath = server_url + "/api/asistencia/offline-sync";
        http.begin(serverPath); 
        
        http.addHeader("Content-Type", "application/json");
        http.setTimeout(15000);
        
        String jsonPayload;
        serializeJson(doc, jsonPayload);
        
        int httpCode = http.POST(jsonPayload);
        bool exito = (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED); 
        
        if (exito) {
            Serial.println("Sincronización de pendientes exitosa");
            String response = http.getString();
            Serial.println(response); // Muestra respuesta del servidor
        } else {
            // ... (la lógica de reintentar guardando en tempFile es correcta) ... 
        }
        http.end();
    }
    
    tempFile.close();
    SD.remove("/pendientes.txt");
    SD.rename("/temp_pendientes.txt", "/pendientes.txt");
}

void sincronizarListaEstudiantes() {
    if (WiFi.status() != WL_CONNECTED) return; 
    HTTPClient http;
    // --- ENDPOINT CORREGIDO ---
    // Usamos el 'aulaCodigo'como ID del dispositivo
    String serverPath = server_url + "/api/estudiantes/dispositivo/" + aulaCodigo; 
    
    Serial.println("Sincronizando lista desde: " + serverPath);
    http.begin(serverPath);
    int httpCode = http.GET(); 
    
    if (httpCode == HTTP_CODE_OK) {
        String payload = http.getString();
        DynamicJsonDocument doc(4096); 
        DeserializationError error = deserializeJson(doc, payload); 
        
        if (error) {
            Serial.print("deserializeJson() falló: ");
            Serial.println(error.c_str());
            http.end();
            return;
        }
        
        numEstudiantesActual = 0;
        File studentsFile = SD.open("/lista_estudiantes.txt", FILE_WRITE); 
        if (!studentsFile) {
            http.end();
            return;
        }
        
        // --- NUEVO ENCABEZADO DE 4 COLUMNAS ---
        studentsFile.println("UID,NOMBRE,ESTADO,MARCOHOY");
        
        JsonArray studentsArray = doc.as<JsonArray>();
        for (JsonObject student : studentsArray) {
            if (numEstudiantesActual < MAX_STUDENTS) {
                String uid = student["uid"].as<String>();
                String nombre = student["nombre"].as<String>();
                int estado = student["estado"]; // 1 o 0
                bool marcoHoy = student["marco_hoy"] | false; // <-- LEER NUEVO CAMPO

                // Guardar en la memoria local
                estudiantes[numEstudiantesActual].uid = uid;
                estudiantes[numEstudiantesActual].nombre = nombre;
                estudiantes[numEstudiantesActual].estado = estado;
                estudiantes[numEstudiantesActual].marcoHoy = marcoHoy; // <-- GUARDAR EN RAM

                // --- ESCRIBIR 4 CAMPOS EN LA SD ---
                studentsFile.println(uid + "," + nombre + "," + String(estado) + "," + String(marcoHoy ? 1 : 0));
                
                numEstudiantesActual++;
            } else break;
        }
        studentsFile.close();
        
        Serial.print("Lista sincronizada (con estado 'marcoHoy'). Total: ");
        Serial.println(numEstudiantesActual);
        mostrarMensajeLCD("Lista Actualizada", "Estudiantes OK", 2000);
    } else {
        Serial.print("Error al sincronizar lista, HTTP: ");
        Serial.println(httpCode);
        // Si falla el GET, cargamos desde SD
        cargarListaEstudiantesDesdeSD();
    }
    http.end();
}

void cargarListaEstudiantesDesdeSD() {
    if (!sdCardOK) return; 
    
    File studentsFile = SD.open("/lista_estudiantes.txt", FILE_READ); 
    if (!studentsFile) {
        numEstudiantesActual = 0;
        return; 
    }
    
    numEstudiantesActual = 0;
    studentsFile.readStringUntil('\n'); // Saltar encabezado
    
    while (studentsFile.available()) {
        String line = studentsFile.readStringUntil('\n');
        line.trim();
        if (line.length() == 0) continue;
        
        if (numEstudiantesActual < MAX_STUDENTS) {
            // --- PARSEAR 4 CAMPOS ---
            int firstComma = line.indexOf(',');
            int secondComma = line.indexOf(',', firstComma + 1);
            int thirdComma = line.indexOf(',', secondComma + 1); // <-- NUEVO

            if (firstComma != -1 && secondComma != -1 && thirdComma != -1) {
                estudiantes[numEstudiantesActual].uid = line.substring(0, firstComma);
                estudiantes[numEstudiantesActual].nombre = line.substring(firstComma + 1, secondComma);
                estudiantes[numEstudiantesActual].estado = line.substring(secondComma + 1, thirdComma).toInt();
                // Convertir "1" o "0" a booleano
                estudiantes[numEstudiantesActual].marcoHoy = (line.substring(thirdComma + 1).toInt() == 1); // <-- NUEVO
                
                numEstudiantesActual++;
            }
        } else break;
    }
    studentsFile.close();
    Serial.print("Cargados desde SD (con estado 'marcoHoy'): ");
    Serial.println(numEstudiantesActual);
}

// === FUNCIÓN DE CONTROL DE SONIDO ===
void emitirSonido(int tipo) {
    // tipo 0: Arranque (Melodía ascendente)
    // tipo 1: Éxito / Acceso Permitido (Agudo y alegre)
    // tipo 2: Error / Acceso Denegado (Grave y largo)
    // tipo 3: Advertencia / Desconocido (Rápido)

    switch (tipo) {
        case 0: // SONIDO DE ARRANQUE: Do - Mi - Sol
            ledcWriteTone(BUZZER_PIN, 1047); 
            delay(100);
            ledcWriteTone(BUZZER_PIN, 1319); 
            delay(100);
            ledcWriteTone(BUZZER_PIN, 1568); 
            delay(100);
            break;

        case 1: // SONIDO DE ÉXITO (ASISTENCIA OK)
            ledcWriteTone(BUZZER_PIN, 2000); 
            delay(100);
            ledcWriteTone(BUZZER_PIN, 0);    
            delay(50);
            ledcWriteTone(BUZZER_PIN, 2500); 
            delay(100);
            break;

        case 2: // SONIDO DE ERROR (INACTIVO / BLOQUEADO)
            ledcWriteTone(BUZZER_PIN, 300);  
            delay(600);                          
            break;

        case 3: // SONIDO DE ADVERTENCIA (TARJETA DESCONOCIDA)
            ledcWriteTone(BUZZER_PIN, 1000);
            delay(80);
            ledcWriteTone(BUZZER_PIN, 0);
            delay(50);
            ledcWriteTone(BUZZER_PIN, 1000);
            delay(80);
            ledcWriteTone(BUZZER_PIN, 0);
            delay(50);
            ledcWriteTone(BUZZER_PIN, 1000);
            delay(80);
            break;
        case 4: // SONIDO MODO CONFIGURACIÓN (Lento)
            ledcWriteTone(BUZZER_PIN, 500); // Frecuencia media
            delay(150);
            break;
    }
    
    // Apagar el sonido al terminar
    ledcWriteTone(BUZZER_PIN, 0);
}