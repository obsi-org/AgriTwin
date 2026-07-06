/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * SPDX-License-Identifier: Apache-2.0
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 *
 * Generic Arduino-compatible weather station example.
 * Replace placeholders outside source control before deployment.
 */

#include <Arduino.h>

const char *DEPLOYMENT_ID = "example_deployment";
const char *STATION_ID = "weather_station_A";
const char *MQTT_TOPIC = "agritwin/example_deployment/weather/weather_station_A/telemetry";
const char *MQTT_HOST = "replace-with-mqtt-host";
const char *NETWORK_NAME = "replace-with-network-name";
const char *NETWORK_PASSWORD = "replace-with-network-password";

const int RAIN_GAUGE_PIN = 2;
const int ANEMOMETER_PIN = 3;
const int WIND_VANE_PIN = A0;
const int SOLAR_RADIATION_PIN = A1;
const int BATTERY_PIN = A2;

const float RAIN_MM_PER_TIP = 0.2794;
const float WIND_KMH_PER_HZ = 2.4;
const float SOLAR_W_M2_PER_ADC = 1.0;
const float BATTERY_DIVIDER_RATIO = 2.0;

volatile unsigned long rainTips = 0;
volatile unsigned long windPulses = 0;

void rainInterrupt() {
  rainTips++;
}

void windInterrupt() {
  windPulses++;
}

float readAirTemperatureC() {
  return 21.8; // Replace with weather sensor reading.
}

float readRelativeHumidityPct() {
  return 61.0; // Replace with weather sensor reading.
}

float readPressureHpa() {
  return 1015.2; // Replace with pressure sensor reading.
}

float readWindDirectionDeg() {
  int adc = analogRead(WIND_VANE_PIN);
  return (adc / 1023.0) * 360.0;
}

float readSolarRadiationWM2() {
  return analogRead(SOLAR_RADIATION_PIN) * SOLAR_W_M2_PER_ADC;
}

float readBatteryVoltage() {
  float adcVoltage = (analogRead(BATTERY_PIN) / 1023.0) * 3.3;
  return adcVoltage * BATTERY_DIVIDER_RATIO;
}

String isoTimestampPlaceholder() {
  return "gateway_timestamp";
}

String buildPayload(float rainfallMm, float windSpeedKmh) {
  String payload = "{";
  payload += "\"deployment_id\":\"" + String(DEPLOYMENT_ID) + "\",";
  payload += "\"station_id\":\"" + String(STATION_ID) + "\",";
  payload += "\"observed_at\":\"" + isoTimestampPlaceholder() + "\",";
  payload += "\"data_source_classification\":\"field_weather_station\",";
  payload += "\"readings\":{";
  payload += "\"air_temperature_c\":" + String(readAirTemperatureC(), 1) + ",";
  payload += "\"relative_humidity_pct\":" + String(readRelativeHumidityPct(), 1) + ",";
  payload += "\"rainfall_mm\":" + String(rainfallMm, 2) + ",";
  payload += "\"wind_speed_kmh\":" + String(windSpeedKmh, 1) + ",";
  payload += "\"wind_direction_deg\":" + String(readWindDirectionDeg(), 0) + ",";
  payload += "\"solar_radiation_w_m2\":" + String(readSolarRadiationWM2(), 0) + ",";
  payload += "\"atmospheric_pressure_hpa\":" + String(readPressureHpa(), 1) + ",";
  payload += "\"battery_voltage_v\":" + String(readBatteryVoltage(), 2);
  payload += "},";
  payload += "\"units\":{";
  payload += "\"air_temperature_c\":\"C\",";
  payload += "\"relative_humidity_pct\":\"%\",";
  payload += "\"rainfall_mm\":\"mm\",";
  payload += "\"wind_speed_kmh\":\"km h^-1\",";
  payload += "\"wind_direction_deg\":\"degrees\",";
  payload += "\"solar_radiation_w_m2\":\"W m^-2\",";
  payload += "\"atmospheric_pressure_hpa\":\"hPa\",";
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
  pinMode(RAIN_GAUGE_PIN, INPUT_PULLUP);
  pinMode(ANEMOMETER_PIN, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(RAIN_GAUGE_PIN), rainInterrupt, FALLING);
  attachInterrupt(digitalPinToInterrupt(ANEMOMETER_PIN), windInterrupt, FALLING);
}

void loop() {
  unsigned long startPulses = windPulses;
  delay(10000UL);
  unsigned long pulseDelta = windPulses - startPulses;
  float windHz = pulseDelta / 10.0;
  float windSpeedKmh = windHz * WIND_KMH_PER_HZ;

  noInterrupts();
  unsigned long tips = rainTips;
  rainTips = 0;
  interrupts();

  float rainfallMm = tips * RAIN_MM_PER_TIP;
  publishTelemetry(buildPayload(rainfallMm, windSpeedKmh));
  delay(890000UL); // Complete approximately 15-minute interval.
}
