/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * SPDX-License-Identifier: Apache-2.0
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 *
 * Generic Arduino-compatible sensor node example.
 * Replace placeholders outside source control before deployment.
 */

#include <Arduino.h>

const char *DEPLOYMENT_ID = "example_deployment";
const char *NODE_ID = "sensor_station_A";
const char *MQTT_TOPIC = "agritwin/example_deployment/sensor/sensor_station_A/telemetry";
const char *MQTT_HOST = "replace-with-mqtt-host";
const char *NETWORK_NAME = "replace-with-network-name";
const char *NETWORK_PASSWORD = "replace-with-network-password";

const int SOIL_MOISTURE_PIN_A = A0;
const int SOIL_MOISTURE_PIN_B = A1;
const int LEAF_WETNESS_PIN = A2;
const int BATTERY_PIN = A3;
const int STATUS_LED_PIN = 13;

const float SOIL_A_DRY_ADC = 820.0;
const float SOIL_A_WET_ADC = 360.0;
const float SOIL_B_DRY_ADC = 810.0;
const float SOIL_B_WET_ADC = 350.0;
const float BATTERY_DIVIDER_RATIO = 2.0;

float clampFloat(float value, float lower, float upper) {
  if (value < lower) {
    return lower;
  }
  if (value > upper) {
    return upper;
  }
  return value;
}

float adcToVwcPercent(int adcValue, float dryAdc, float wetAdc) {
  float fraction = (dryAdc - adcValue) / (dryAdc - wetAdc);
  return clampFloat(fraction * 38.0, 0.0, 60.0);
}

String isoTimestampPlaceholder() {
  return "gateway_timestamp";
}

float readSoilTemperatureC() {
  return 18.4; // Replace with DS18B20 reading.
}

float readAirTemperatureC() {
  return 22.1; // Replace with SHT31/SHT35 reading.
}

float readRelativeHumidityPct() {
  return 58.0; // Replace with SHT31/SHT35 reading.
}

float readBatteryVoltage() {
  int adc = analogRead(BATTERY_PIN);
  float adcVoltage = (adc / 1023.0) * 3.3;
  return adcVoltage * BATTERY_DIVIDER_RATIO;
}

String buildPayload() {
  int soilAAdc = analogRead(SOIL_MOISTURE_PIN_A);
  int soilBAdc = analogRead(SOIL_MOISTURE_PIN_B);
  int leafAdc = analogRead(LEAF_WETNESS_PIN);

  float soilA = adcToVwcPercent(soilAAdc, SOIL_A_DRY_ADC, SOIL_A_WET_ADC);
  float soilB = adcToVwcPercent(soilBAdc, SOIL_B_DRY_ADC, SOIL_B_WET_ADC);
  float leafWetness = clampFloat((leafAdc / 1023.0) * 100.0, 0.0, 100.0);

  String payload = "{";
  payload += "\"deployment_id\":\"" + String(DEPLOYMENT_ID) + "\",";
  payload += "\"node_id\":\"" + String(NODE_ID) + "\",";
  payload += "\"observed_at\":\"" + isoTimestampPlaceholder() + "\",";
  payload += "\"data_source_classification\":\"field_sensor_node\",";
  payload += "\"readings\":{";
  payload += "\"soil_moisture_vwc_pct_a\":" + String(soilA, 1) + ",";
  payload += "\"soil_moisture_vwc_pct_b\":" + String(soilB, 1) + ",";
  payload += "\"soil_temperature_c\":" + String(readSoilTemperatureC(), 1) + ",";
  payload += "\"air_temperature_c\":" + String(readAirTemperatureC(), 1) + ",";
  payload += "\"relative_humidity_pct\":" + String(readRelativeHumidityPct(), 1) + ",";
  payload += "\"leaf_wetness_pct\":" + String(leafWetness, 1) + ",";
  payload += "\"battery_voltage_v\":" + String(readBatteryVoltage(), 2);
  payload += "},";
  payload += "\"units\":{";
  payload += "\"soil_moisture_vwc_pct_a\":\"% VWC\",";
  payload += "\"soil_moisture_vwc_pct_b\":\"% VWC\",";
  payload += "\"soil_temperature_c\":\"C\",";
  payload += "\"air_temperature_c\":\"C\",";
  payload += "\"relative_humidity_pct\":\"%\",";
  payload += "\"leaf_wetness_pct\":\"%\",";
  payload += "\"battery_voltage_v\":\"V\"";
  payload += "},";
  payload += "\"status\":{\"battery\":\"normal\",\"clock\":\"gateway_assigned\"}";
  payload += "}";
  return payload;
}

void publishTelemetry(const String &payload) {
  // Connect to MQTT_HOST or send to an edge gateway here.
  // Keep real credentials in local device configuration, not in source code.
  Serial.println(MQTT_TOPIC);
  Serial.println(payload);
}

void setup() {
  Serial.begin(115200);
  pinMode(STATUS_LED_PIN, OUTPUT);
  digitalWrite(STATUS_LED_PIN, LOW);
}

void loop() {
  String payload = buildPayload();
  publishTelemetry(payload);
  delay(900000UL); // 15 minutes for example sampling.
}
