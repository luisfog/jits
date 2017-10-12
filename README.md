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
 - OR copy the index.php file (the one in the repository root) to your server *
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
3. Fill all the fields **
```
```
if you don't have mysql root user privileges
  3.2. Choose the option "Existing database and user"
```

#### Notes
note * - yes, you just need the index.php file, this file will later download the website from the repository

note ** - The user name and password is mandatory for server access

note ** - The email is required for alarms (to warn the user)

## Start sending data

0. Access the server (e.g. www.myJits.com)

1. Create a client

2. Copy and save the "Server", "Connection key", "AES key" and "AES iv"

3. Use one of the provided [libraries](libraries)

4. Initiate the library with your "Server", "Connection key", "AES key" and "AES iv"

5. Send JSON data (the server will configure and create the database automatically)


## Creating you own connection (without a library)

0. Access the server (e.g. www.myJits.com)

1. Create a client

2. Copy and save the "Server", "Connection key", "AES key" (base64) and "AES iv" (base64)

3. In your code, develop an AES encryption function

4. In your code, create a JSON message with your values (double variables) to send (without timestamp)

   e.g. {"value1" : "12.4", "value2" : "5.9"}

5. In your code, encript your JSON message with your "AES key" and "AES iv"

   5.1. Encode your JSON to base64

6. In your code, send a HTTP POST request for "Server"?con="Connection key"

   e.g. www.myJits.com/publisher.php?con=16f90df23806278df65eaa052faba8

   6.1. Put your encripted JSON in the HTTP POST request body

7. Go to the server and see you data

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

#
Have fun and enjoy!

#### Luis Gomes
