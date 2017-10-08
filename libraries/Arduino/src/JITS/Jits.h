/*
	Jits.h - Library to connect with JSON IoT Server.
	Created by Luis Gomes
	
	Released into the public domain.
	https://github.com/luisfog/jits
*/

#ifndef Jits_h
#define Jits_h

#include "Arduino.h"
#include "Ethernet.h"

class Jits
{
	public:
		Jits(String server, int port, String connectionKey, String aesKey);
		String getIV_Ethernet();
		String fromArrayToJSON(String names[], double values[]);
		String encryptJSON(String json, String aesIV);
		boolean sendDataEncript_Ethernet(String encryptedJSON);
		boolean sendDataJson_Ethernet(String json);
		boolean sendDataArray_Ethernet(String names[], double values[]);
	private:
		String _getResponse(EthernetClient client);
		String _server;
		int _port;
		String _connectionKey;
		String _aesKey;
};

#endif