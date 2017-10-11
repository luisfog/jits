--[[
	JITS Client
	
	Simple Example of JITS client using a DHT22 at pin 7

	Created by Luis Gomes
	Released into the public domain.
	https://github.com/luisfog/jits
--]]

jits = require "jits"

function loop()

	-- DHT22 variables
    local status, temp, humi, temp_dec, humi_dec = dht.read(7)

	-- JITS variables
    local server = "http://yourserver/"
    local connectionKey = "your connection key"
    local aesKey = "your aes key"
    
    -- Read DHT22
    if status == dht.OK then
        local msg = string.format("{\"temp\" : \"%f\", \"hum\" : \"%f\"}",
              temp,
              humi
        )

		-- Create the attributes and values to sent
        local names = {'temp', 'hum'}
        local values = {temp, humi}

		-- Send the data to JITS
        jits.sendDataArray (server, connectionKey, names, values, aesKey)
            
    elseif status == dht.ERROR_CHECKSUM then
        print( "DHT Checksum error." )
    elseif status == dht.ERROR_TIMEOUT then
        print( "DHT timed out." )
    end
end

function setup()
	-- Configure your Wi-Fi connection
    wifi.setmode(wifi.STATION, true)
    station_cfg={}
    station_cfg.ssid="your ssid"
    station_cfg.pwd="your wifi password"
    wifi.sta.config(station_cfg, true)
    wifi.sta.connect()
    tmr.alarm(1, 5000, 1, function()
        if wifi.sta.getip()== nil then
            print("IP unavaiable, Waiting...")
        else
            tmr.stop(1)
            print("The module MAC address is: " .. wifi.ap.getmac())
            print("Config done, IP is "..wifi.sta.getip())
			
			-- Defines the reading and sending period
            tmr.create():alarm(10000, tmr.ALARM_AUTO, loop)
        end
    end)
end

tmr.create():alarm(3000, tmr.ALARM_SINGLE, setup)
