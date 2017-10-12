# JSON IoT Server
Web server to easly store data provided by low-end hardware, such as, ESP8266, Arduino (using any internet shield) or high-end software, such as, Java

Easy to install and configure

### Requirements

Public web server with:
 - PHP
 - MySQL

### Installation and Configuration

```
1. Create a folder in your server and do one of the below:
 - clone the website folder of this repository to your server
 - OR copy the index.php file (the one in the repository root) to your server (note 1)
```
NOTE*: yes, you just need the index.php file, this file will later download the website from the repository
```
if you don't have mysql root user privileges you need to do an extra step:
  1.2. Create a database and user
```
```
2. Access index.php (e.g. www.myJits.com/index.php)
```
```
3. Fill all the fields (notes 2 and 3)
```
```
if you don't have mysql root user privileges
  3.2. Choose the option "Existing database and user"
```

#### Notes
1. yes, you just need the index.php file, this file will later download the website from the repository
2. The user name and password is mandatory for server access
3. The email is required for alarms (to warn the user)

## Start sending data

Access the server (e.g. www.myJits.com)

1. On the Clients menu choose "Add client"

2. Give a name to your client (is recommended to stay with AES-128 and Publisher type)

2. Copy and save the "Server", "Connection key" and "AES key"

3. Use one of the provided [libraries](libraries) or the [https://github.com/abmantis/homeassistant-jitshistory](Home Assistant componente)

4. Initiate the library with your "Server", "Connection key" and "AES key"

5. Send JSON data (the server will configure and create the database automatically)


## Creating your own connection (without a library)

Access the server (e.g. www.myJits.com)

1. Create a client

2. Copy and save the "Server", "Connection key" and "AES key" (base64)

3. Get the iv for AES

   3.1 Send a HTTP GET request to generatorIV.php with your client connection key
   
   e.g. www.myJits.com/generatorIV.php?con=16f90df23806278df65eaa052faba8
   
   3.2 Store the AES iv you received (base64) - the iv expires within 5 minutes

4. In your code, develop an AES encryption function

5. In your code, create a JSON message with your values (double variables) to send (without timestamp)

   e.g. {"value1" : "12.4", "value2" : "5.9"}

6. In your code, encrypt your JSON message with your "AES key" and "AES iv"

7. Encode your encrypted JSON to Base64 (usually the encryption already results in a bse64 string)

   e.g. 
   
   Input JSON: {"value1" : "12.4", "value2" : "5.9"}
   
   Input AES iv: rtvN9B/PJrQotOF0GWUZ5A==
   
   Expected result: t46nm7o0Sgn1FNcRGcAOk6g64xsntK9G3Tq8jeAfyJcTaccrn5y2Zf7Wc55qGvYn

8. Publish your new data

   8.1 Send a HTTP POST request to publisher.php with your client connection key

   8.1. Put your encripted JSON in the HTTP POST request body

   e.g. www.myJits.com/publisher.php?con=16f90df23806278df65eaa052faba8

9. Go to the server and see your data

## [HOW TO USE](howtouse.md)

## GitHub projects used

[echarts](https://github.com/ecomfe/echarts)

[homeassistant-jitshistory](https://github.com/abmantis/homeassistant-jitshistory)

[AES Encryption Library for Arduino and Raspberry Pi](https://github.com/spaniakos/AES)

[arduino-base64](https://github.com/adamvr/arduino-base64)

[multiple-select](https://github.com/wenzhixin/multiple-select)

[nodemcu-firmware](https://github.com/nodemcu/nodemcu-firmware)

## Licenses

This project is licensed under the MIT License

The license of this project and the ones of the GitHub projects used can be found in [licenses](licenses) folder

#
Have fun and enjoy!

#### Luis Gomes
