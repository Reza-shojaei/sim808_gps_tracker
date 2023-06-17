#include<SoftwareSerial.h> 
#include <DFRobot_SIM808.h>
// define rx and tx pin
#define rxPin 11   
#define txPin 10

 SoftwareSerial mySerial(txPin, rxPin);
 DFRobot_SIM808 sim808(&mySerial);
 // create char to store our lat and lng
 char wlat[12];
 char wlon[12];
 
void setup(){
mySerial.begin(9600);
Serial.begin(9600); 
Serial.println("Starting...");
 while(!sim808.init()) {
      delay(1000);
      Serial.print("Sim808 init error\r\n");
  }

  //************* Turn on the GPS power************

if( sim808.attachGPS())
{
  Serial.println("Open the GPS power success");
}
else 
{
    Serial.println("Open the GPS power failure");

}
}
 void loop(){
   // if we get latitude and longitude we goes to the next line 
if (sim808.getGPS()) {     
   // showing latitude and longitude softwareserial
   Serial.print("latitude :");
   Serial.println(sim808.GPSdata.lat,6);

  Serial.print("longitude :");
  Serial.println(sim808.GPSdata.lon,6);

  // store latitude and longitude on float called la and lo
    float la = sim808.GPSdata.lat;
     float lo = sim808.GPSdata.lon;
     delay(1000);

     //goes to void SubmitHttpRequest

     SubmitHttpRequest(la,lo);
       }
  }

 void SubmitHttpRequest(float la,float lo)
 {
  // convert float to string like : dtostrf(floatValue, minStringWidth, numAfterDecimal, charBuf_to_store_string)
  dtostrf( la,6, 6, wlat); 
  dtostrf( lo,6, 6, wlon); 
  //sending AT command to start connecting internet 
   Serial.println("AT");
delay(100);
 ShowSerialData();
    // set our connection type to GPRS
 mySerial.println("AT+SAPBR=3,1,\"CONTYPE\",\"GPRS\"");
 delay(500);
  ShowSerialData();
    // define our simcard APN name
 mySerial.println("AT+SAPBR=3,1,\"APN\",\"your apn service\"");
 delay(500);
  ShowSerialData();
    // START gprs connection
 mySerial.println("AT+SAPBR=1,1");
 delay(2500);
 ShowSerialData();
//  start HTTP  mode 
mySerial.println("AT+HTTPINIT"); 
delay(100); 
 ShowSerialData();
//ready for HTTP connection
mySerial.println("AT+HTTPPARA=\"CID\",1");
delay(500); 
 ShowSerialData();
// send HTTP post request to our PHP script in our web service Along with lat and lng
mySerial.print("AT+HTTPPARA=\"URL\",\"http://your web site .com/gpsdata.php?");
mySerial.print("&lat=");  // This is the value name
mySerial.print(wlat); // value of...
mySerial.print("&lng=");  // This is the value name
mySerial.print(wlon); // value of...
 // close that screipt
 mySerial.println("\"");
 delay(500);
 ShowSerialData();
//send HTTP POST request
mySerial.println("AT+HTTPACTION=1");//submit the request 
delay(2000);
 ShowSerialData();

mySerial.println("");
delay(100);
 }
  void ShowSerialData()
 {
   while(mySerial.available()!=0)
      {
        Serial.write(mySerial.read());
     }

  } 