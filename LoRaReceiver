// This code works with Heltec WiFi LoRaWAN V3 ESP based microcontroller


#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <WiFi.h>
#include <HTTPClient.h>

#define RF_FREQUENCY                                868000000 // Hz

#define TX_OUTPUT_POWER                             14        // dBm (This LoRa radio configuration is compliant with European Union Legal requirements)

#define LORA_BANDWIDTH                              0         // [0: 125 kHz,
                                                              //  1: 250 kHz,
                                                              //  2: 500 kHz,
                                                              //  3: Reserved]
#define LORA_SPREADING_FACTOR                       8         // [SF7..SF12]
#define LORA_CODINGRATE                             1         // [1: 4/5,
                                                              //  2: 4/6,
                                                              //  3: 4/7,
                                                              //  4: 4/8]
#define LORA_PREAMBLE_LENGTH                        8         // Same for Tx and Rx
#define LORA_SYMBOL_TIMEOUT                         0         // Symbols
#define LORA_FIX_LENGTH_PAYLOAD_ON                  false
#define LORA_IQ_INVERSION_ON                        false


#define RX_TIMEOUT_VALUE                            1000
#define BUFFER_SIZE                                 30 // Define the payload size here

#define LED_PIN                                     2  // Set to your LED GPIO pin

// WiFi configuration (update with your network credentials)
const char* ssid = "TCB_Office";
const char* password = "TCB_Office!@#";

// Server configuration
const char* serverName = "(Here place the adress to your SQL Database)";  // Replace with your server's IP and API endpoint

char txpacket[BUFFER_SIZE];
char rxpacket[BUFFER_SIZE];

static RadioEvents_t RadioEvents;

int16_t txNumber;
int16_t rssi, rxSize;

bool lora_idle = true;

void setup() {
    Serial.begin(115200);
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);

    // Set LED pin as output
    pinMode(LED_PIN, OUTPUT);
    digitalWrite(LED_PIN, LOW); // Make sure LED is off initially
    
    txNumber = 0;
    rssi = 0;
  
    RadioEvents.RxDone = OnRxDone;
    Radio.Init(&RadioEvents);
    Radio.SetChannel(RF_FREQUENCY);
    Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR,
                               LORA_CODINGRATE, 0, LORA_PREAMBLE_LENGTH,
                               LORA_SYMBOL_TIMEOUT, LORA_FIX_LENGTH_PAYLOAD_ON,
                               0, true, 0, 0, LORA_IQ_INVERSION_ON, true);

    // Connect to WiFi
    connectToWiFi();
}

void connectToWiFi() {
  // Connect to WiFi
  Serial.print("Connecting to WiFi...");
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  if (lora_idle) {
    lora_idle = false;
    Serial.println("into RX mode");
    Radio.Rx(0);
  }
  Radio.IrqProcess();
}

void OnRxDone(uint8_t *payload, uint16_t size, int16_t rssi, int8_t snr) {
    rssi = rssi;
    rxSize = size;
    memcpy(rxpacket, payload, size);
    rxpacket[size] = '\0';
    Radio.Sleep();
    
    Serial.printf("\r\nreceived packet \"%s\" with rssi %d , length %d\r\n", rxpacket, rssi, rxSize);
    
    // Turn LED on
    digitalWrite(LED_PIN, HIGH);
    delay(300); // Keep LED on for 0.3 seconds
    digitalWrite(LED_PIN, LOW); // Turn LED off

    // Send the received data to the server
    sendDataToServer(rxpacket);

    lora_idle = true;
}

void sendDataToServer(const char* data) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    
    // Specify the URL of the API
    http.begin(serverName);

    // Specify content type
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Prepare the payload to send (you can add more parameters here as needed)
    String payload = "data=" + String(data);

    // Send the HTTP POST request
    int httpResponseCode = http.POST(payload);

    // Check response
    if (httpResponseCode > 0) {
      Serial.printf("Data sent successfully! HTTP Response code: %d\n", httpResponseCode);
    } else {
      Serial.printf("Error sending data. HTTP Response code: %d\n", httpResponseCode);
    }

    // End the HTTP request
    http.end();
  } else {
    Serial.println("WiFi not connected.");
  }
}
