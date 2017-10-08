/*
	JITS Client
	
	Simple Example of JITS client using a LDR connect to A0 pin

	Created by Luis Gomes
	Released into the public domain.
	https://github.com/luisfog/jits

*/

#include <Jits.h>
#include <Ethernet.h>

//Initiate jits in port 80
Jits jits("http://yourserver/publisher.php", 80,
          "your connection key", "your aes key");

String names[2];
double values[2];

//Defines the mac address of your ethernet shield
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
//If DHCP fails
IPAddress ip(192, 168, 1, 177);

unsigned long startTime;

void setup() {
  Serial.begin(9600);
  while(!Serial);
  
  if (Ethernet.begin(mac) == 0) {
    Serial.println("Failed to configure Ethernet using DHCP");
    Ethernet.begin(mac, ip);
  }
  delay(1000);
  Serial.println("Starting");

  //Defines the values names to sent
  names[0] = "Clarity";
  names[1] = "Night";
}

void loop() {
  Ethernet.maintain();

  startTime = millis();
  
  //Read the values to sent
  values[0] = analogRead(A0);
  if(values[0] < 20)
    values[1] = 1;
  else
    values[1] = 0;

  //Send the data
  if(jits.sendDataArray_Ethernet(names, values))
    Serial.println("Data Sent");
  else
    Serial.println("ERROR");

  //Waits 10 seconds
  while((millis()-startTime) < 10000);
}
