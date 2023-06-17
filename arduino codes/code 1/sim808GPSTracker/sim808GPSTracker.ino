#include <SoftwareSerial.h>

SoftwareSerial ss(10,11);

#define GSM_PORT ss

#include "sim808.h"

void setup() {
  // put your setup code here, to run once:
  ss.begin(9600);
  Serial.begin(9600);
  Serial.println("Starting...");
  
  sim808_setup();

//  sendGSM("AT+GSN");
//  int key = GSM_PORT.read();
//  Serial.print("KEY IS:");
//  Serial.println(key);
}

void sendPositionReport(unsigned long now) {
  GSM_PORT.print("AT+HTTPPARA=\"URL\",\"http://yourwebsite.com/gpsdata.php?");
  GSM_PORT.print("&lat=");
  GSM_PORT.print(lat);
  GSM_PORT.print("&lng=");
  GSM_PORT.print(lon);

  GSM_PORT.println("\"");
  
  flushGSM(now);
  
  delay(500);

  sendGSM("AT+HTTPACTION=0");
}

void loop() {

  unsigned long now = millis();

  boolean gotGPS = false;

  if ( actionState == AS_IDLE ) {
    if ( fixStatus > 0 && now > lastActionTime + 10000 ) {

      sendPositionReport(now);

      lastActionTime = now;
      httpResult = 0;
      actionState = AS_WAITING_FOR_RESPONSE;
    }
  }
  else {
    // waiting on response - abort if taking too long
    if ( now > lastActionTime + 15000 ) {
      actionState = AS_IDLE;
      parseState = PS_DETECT_MSG_TYPE;
      resetBuffer();
    }
  }
  
  sim808_loop();
}
