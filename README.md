# JSON IoT Server
Web server to easly store data provided by low end hardware such as: ESP8266, Arduino (using any internet shield), Java program, and more

Easy to install and configure (1 click configuration)

### Requirements

Public web server with:
 - PHP
 - MySQL

### Installation and Configuration

```
1. Clone the repository to your server
```
```
if you don't have mysql root user privileges
1.2. Create a database and user
```
```
2. Open index.php
```
```
3. Fill all the fields
```
```
if you don't have mysql root user privileges
3.2. Choose the option "Existing database and user"
```

#### Notes
1. The user name and password is mandatory for server access
2. The email is required for alarms (to warn the user)

## Start sending data

Open the server
```
1. Create a client
```
```
2. Copy and save the "Connection key", "AES key" and "AES iv"
```
```
3. Use one of the provided libraries: https://github.com/luisfog/jits/tree/master/libraries
[libraries](/libraries)
```
```
4. Initiate library
```
```
5. Send JSON data (the server will configure and create the database automatically)
```

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

#
Have fun and enjoy!

#### Luis Gomes
