# sim808_gps_tracker
in this project we use sim808 and arduino uno to build a gps tracker that user can see the tracking log on a website.
sim808 will send locations as Longitude and latitude to php scripct.

the website part was done by php, html and javascripct.

In the following i will show you how to do the each part.

## arduino
for the hardware part i use arduino uno and [sim808-module](https://www.amazon.de/TECNOIOT-SIM808-Module-Development-Antenna/dp/B083M5VBQT) , this also work on other arduino board but you need to make some changes in code and wiring.
you can power up the sim808 with 5-26V, 2A, 5.5mm adapter. also you can use 3.7 18650 li-lon battery.
i personally use 20000 powerbank with 5V, 2.4A output, for both arduino uno and sim808 module.
### wiring
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/arduino%20codes/wiring/SIM808-Module-circuit3.jpg">
  <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/arduino%20codes/wiring/SIM808-Module-circuit3.jpg">
  <img alt="Shows an illustrated sun in light mode and a moon with stars in dark mode." src="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/arduino%20codes/wiring/SIM808-Module-circuit3.jpg">
</picture>

i upload two arduino code, That both works fine and both them can send the location to php secript ervery 10S feel free to use either of them but here is one example.
### arduino code
```
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

```
you can also read more about [DFRobot_sim808 library](https://github.com/DFRobot/DFRobot_SIM808]


##webpage
