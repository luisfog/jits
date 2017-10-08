/*
	Jits.cpp - Library to connect with JSON IoT Server.
	Created by Luis Gomes
	
	Released into the public domain.
	https://github.com/luisfog/jits
*/

#include "Arduino.h"
#include "Jits.h"
#include "AES.h"
#include "Base64.h"
#include "Ethernet.h"

Jits::Jits(String server, int port, String connectionKey, String aesKey)
{
	_server = server;
	_port = port;
	_connectionKey = connectionKey;
	_aesKey = aesKey;
}

/*
	Gets the iv code for AES encryption using an Ethernet connection
	
	Before sending information, the iv code must be asked to be activated in the server-side
*/
String Jits::getIV_Ethernet()
{
	String serverURL = _server;
	String pageString = "";

	EthernetClient client;

	if(serverURL.indexOf("//") != -1){
		serverURL = serverURL.substring(serverURL.indexOf("//") + 2);
	}
	if(serverURL.indexOf("/") != -1){
		pageString = serverURL.substring(serverURL.indexOf("/") + 1);
		serverURL = serverURL.substring(0, serverURL.indexOf("/"));
	}
	if(pageString.lastIndexOf("/") != pageString.length() - 1){
		pageString += "/";
	}

	pageString = "GET /" + pageString + "generatorIV.php?con=" + _connectionKey + " HTTP/1.1";

	char *host = malloc(sizeof(char)*serverURL.length());
	serverURL.toCharArray(host, serverURL.length() + 1);

	if (client.connect(host, _port)) {
		serverURL = "Host: " + serverURL;
		client.println(pageString);
		client.println(serverURL);
		client.println("Connection: close");
		client.println();

		String iv = _getResponse(client);
		return iv;
	}
	return "";
}

/*
	Creates a json String from two arrays (one for the attributes and other for the values)
*/
String Jits::fromArrayToJSON(String names[], double values[])
{
	if(sizeof(names) != sizeof(values))
		return "";

	String res = "{";
	for(int i=0; i<sizeof(names); i++)
		res += "\"" + names[i] + "\" : \"" + values[i] + "\",";

	return res.substring(0, res.length()-1) + "}";
}

/*
	Applies the AES encryption in a json message, using a given iv code
*/
String Jits::encryptJSON(String json, String aesIV)
{
	AES aes;
	int sizeCipher = json.length();
	while(sizeCipher%16 != 0)
		sizeCipher++;
	int blocks = sizeCipher/16;

	byte *plain = malloc(sizeof(byte)*sizeCipher);
	json.getBytes(plain, json.length() + 1);

	for(int i=json.length(); i<sizeCipher; i++)
		plain[i] = 0x00;

	byte *keyBytes = malloc(sizeof(byte)*_aesKey.length());
	_aesKey.getBytes(keyBytes, _aesKey.length() + 1);

	byte *ivBytes = malloc(sizeof(byte)*aesIV.length());
	aesIV.toCharArray(ivBytes, aesIV.length() + 1);
	byte *iv = malloc(sizeof(byte)*base64_dec_len(ivBytes, aesIV.length()));
	base64_decode(iv, ivBytes, aesIV.length());

	byte cipher[blocks*N_BLOCK];

	aes.set_key(keyBytes, 128);
	aes.cbc_encrypt(plain, cipher, blocks, iv);

	char encoded[base64_enc_len(sizeof(cipher))];
	base64_encode(encoded, cipher, sizeof(cipher));
	return encoded;
}

/*
	Sends an encrypted json to JITS server, using an Ethernet connection
*/
boolean Jits::sendDataEncript_Ethernet(String encryptedJSON)
{
	String serverURL = _server;
	String pageString = "";
	char outBuf[64];

	EthernetClient client;

	if(serverURL.indexOf("//") != -1){
		serverURL = serverURL.substring(serverURL.indexOf("//") + 2);
	}
	if(serverURL.indexOf("/") != -1){
		pageString = serverURL.substring(serverURL.indexOf("/") + 1);
		serverURL = serverURL.substring(0, serverURL.indexOf("/"));
	}
	if(pageString.lastIndexOf("/") != pageString.length() - 1){
		pageString += "/";
	}

	pageString = "POST /" + pageString + "publisher.php?con=" + _connectionKey + " HTTP/1.1";

	char *server = malloc(sizeof(char)*serverURL.length());
	serverURL.toCharArray(server, serverURL.length() + 1);

	if (client.connect(server, _port)) {
		serverURL = "Host: " + serverURL;
		client.println(pageString);
		client.println(serverURL);
		client.println("Connection: close");
		sprintf(outBuf,"Content-Length: %u\r\n",encryptedJSON.length());
		client.println(outBuf);
		client.println(encryptedJSON);
		client.println();

		String res = _getResponse(client);
		if(res.indexOf("successfully") != -1)
			return true;
	}
	return false;
}

/*
	Sends a json to JITS server, using an Ethernet connection
*/
boolean Jits::sendDataJson_Ethernet(String json)
{
	String aesIV = getIV_Ethernet();
	return sendDataEncript_Ethernet(encryptJSON(json, aesIV));
}

/*
	Sends two arrays (one for the attributes and other for the values) to JITS server, using an Ethernet connection
*/
boolean Jits::sendDataArray_Ethernet(String names[], double values[])
{
	return sendDataJson_Ethernet(fromArrayToJSON(names, values));
}

/*
	Receive the response of the HTTP request, using an Ethernet connection
*/
String Jits::_getResponse(EthernetClient client)
{
	byte countBreaks = 0;
	boolean flagLastLine = false;
	boolean flagRead = false;

	while(!client.available()){
		delay(2);
		if (!client.connected()) {
			client.stop();
			return "";
		}
	}
	String res = "";

	while(client.available()) {
		char c = client.read();

		if(flagRead)
			res += (char)c;

		if(c == '\r'){
			if(client.available()){
				if(flagRead){
					flagRead = false;
					flagLastLine = false;
				}else if(flagLastLine)
					flagRead = true;
				if(client.read() == '\n'){
					countBreaks++;
					if(countBreaks == 2)
					flagLastLine = true;
				}
				else
					countBreaks = 0;
			}
		}else
			countBreaks = 0;
	}
	client.stop();

	return res;
}