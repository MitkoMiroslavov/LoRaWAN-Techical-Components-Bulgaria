// This code works with Heltec WiFi LoRaWAN V3 ESP based microcontroller

#include "LoRaWan_APP.h"
#include "Arduino.h"

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

#define DISS_SEN_ECHO                               20 // Define distance sensor pins
#define DISS_SEN_TRIG                               19

char txpacket[BUFFER_SIZE];
char rxpacket[BUFFER_SIZE];

double txNumber;

// Declare distance variable globally so it can be used in the loop
long distance = 0;

bool lora_idle = true;

static RadioEvents_t RadioEvents;
void OnTxDone( void );
void OnTxTimeout( void );

void setup() {
    Serial.begin(115200);
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);
  
    txNumber = 0;

    pinMode(DISS_SEN_TRIG, OUTPUT);  // Set Trigger pin as output
    pinMode(DISS_SEN_ECHO, INPUT);   // Set Echo pin as input

    RadioEvents.TxDone = OnTxDone;
    RadioEvents.TxTimeout = OnTxTimeout;
    
    Radio.Init( &RadioEvents );
    Radio.SetChannel( RF_FREQUENCY );
    Radio.SetTxConfig( MODEM_LORA, TX_OUTPUT_POWER, 0, LORA_BANDWIDTH,
                                   LORA_SPREADING_FACTOR, LORA_CODINGRATE,
                                   LORA_PREAMBLE_LENGTH, LORA_FIX_LENGTH_PAYLOAD_ON,
                                   true, 0, 0, LORA_IQ_INVERSION_ON, 3000 ); 
}

long dissSens()
{
  long duration;

  // Send a pulse to the Trigger pin
  digitalWrite(DISS_SEN_TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(DISS_SEN_TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(DISS_SEN_TRIG, LOW);

  // Read the duration of the Echo pin
  duration = pulseIn(DISS_SEN_ECHO, HIGH);

  // Calculate distance (duration in microseconds / 2 gives the round-trip time, then convert to cm)
  distance = (duration / 2) / 29.1;  // 29.1 is the speed of sound in cm/microseconds
  
  return distance;  // Return the calculated distance
}

void loop()
{
  distance = dissSens();  // Call the distance sensor function and get the distance
  Serial.println(distance);
  // Check if the distance is less than or equal to 10 cm
  if (distance >= 20) {
    if (lora_idle == true) {
      delay(1000);  // Wait a second before sending the packet
      //txNumber += 0.01;
      sprintf(txpacket, "Water level: %0.2f",distance);  // Start a package
      
      Serial.printf("\r\nsending packet \"%s\" , length %d\r\n", txpacket, strlen(txpacket));

      Radio.Send((uint8_t *)txpacket, strlen(txpacket));  // Send the package out    
      lora_idle = false;
    }
    Radio.IrqProcess();  // Handle LoRa interrupts
  }
  delay(1000);
}

void OnTxDone(void) {
  Serial.println("TX done......");
  lora_idle = true;
}

void OnTxTimeout(void) {
  Radio.Sleep();
  Serial.println("TX Timeout......");
  lora_idle = true;
}
