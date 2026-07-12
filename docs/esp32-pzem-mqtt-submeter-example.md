# ESP32 + PZEM-004T + MQTT Example

This example publishes submeter telemetry to MQTT in a format that matches the Laravel listener command.

## Topic

Use a topic like:

```text
lgu/submeters/12/telemetry
```

You can replace `12` with the actual `submeter_id`.

## Payload

Send JSON like this:

```json
{
  "submeter_id": 12,
  "device_id": "ESP32-SUB-001",
  "period_type": "monthly",
  "reading_month": "2026-07",
  "reading_start_kwh": 120.5,
  "reading_end_kwh": 131.2,
  "operating_days": 30
}
```

## Arduino Sketch

```cpp
#include <WiFi.h>
#include <PubSubClient.h>
#include <PZEM004Tv30.h>

// WiFi
const char* WIFI_SSID = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

// MQTT
const char* MQTT_HOST = "192.168.1.50";
const int MQTT_PORT = 1883;
const char* MQTT_USER = "";
const char* MQTT_PASSWORD = "";
const char* MQTT_TOPIC = "lgu/submeters/12/telemetry";

// Device identity
const char* DEVICE_ID = "ESP32-SUB-001";
const int SUBMETER_ID = 12;

WiFiClient espClient;
PubSubClient mqttClient(espClient);

// PZEM-004T serial pins
// Adjust these pins to match your wiring.
HardwareSerial PZEMSerial(2);
PZEM004Tv30 pzem(PZEMSerial, 16, 17); // RX, TX

float lastEnergy = 0.0;

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
}

void connectMqtt() {
  mqttClient.setServer(MQTT_HOST, MQTT_PORT);

  while (!mqttClient.connected()) {
    if (strlen(MQTT_USER) > 0) {
      mqttClient.connect(DEVICE_ID, MQTT_USER, MQTT_PASSWORD);
    } else {
      mqttClient.connect(DEVICE_ID);
    }
    if (!mqttClient.connected()) {
      delay(2000);
    }
  }
}

String currentMonth() {
  return String(2026) + "-" + String(7);
}

void setup() {
  Serial.begin(115200);
  PZEMSerial.begin(9600, SERIAL_8N1, 16, 17);
  connectWiFi();
  connectMqtt();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }
  if (!mqttClient.connected()) {
    connectMqtt();
  }
  mqttClient.loop();

  float voltage = pzem.voltage();
  float current = pzem.current();
  float power = pzem.power();
  float energy = pzem.energy();

  if (isnan(energy)) {
    delay(5000);
    return;
  }

  if (energy < lastEnergy) {
    lastEnergy = energy;
  }

  float startKwh = lastEnergy;
  float endKwh = energy;

  String payload = "{";
  payload += "\"submeter_id\":" + String(SUBMETER_ID) + ",";
  payload += "\"device_id\":\"" + String(DEVICE_ID) + "\",";
  payload += "\"period_type\":\"monthly\",";
  payload += "\"reading_month\":\"" + currentMonth() + "\",";
  payload += "\"reading_start_kwh\":" + String(startKwh, 2) + ",";
  payload += "\"reading_end_kwh\":" + String(endKwh, 2) + ",";
  payload += "\"operating_days\":30";
  payload += "}";

  mqttClient.publish(MQTT_TOPIC, payload.c_str(), false);
  lastEnergy = endKwh;

  delay(15000);
}
```

## Laravel run command

```bash
php artisan mqtt:submeter-listen
```

## Notes

- Update the MQTT broker host, username, and password in both ESP32 and Laravel `.env`.
- Make sure the `submeter_id` exists and is active in the database.
- If you want live browser updates later, we can broadcast the saved reading with Reverb.
