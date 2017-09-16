
jits = require "jits"

function loop()

    local status, temp, humi, temp_dec, humi_dec = dht.read(7)

    local server = "http://yourserver/publisher.php"
    local connectionKey = "your connection key"
    local aesKey = "your aes key"
    
    
    if status == dht.OK then
        local msg = string.format("{\"temp\" : \"%f\", \"hum\" : \"%f\"}",
              temp,
              humi
        )

        local names = {'temp', 'hum'}
        local values = {temp, humi}

        jits.sendDataArray (server, connectionKey, names, values, aesKey)
            
    elseif status == dht.ERROR_CHECKSUM then
        print( "DHT Checksum error." )
    elseif status == dht.ERROR_TIMEOUT then
        print( "DHT timed out." )
    end
end

function setup()
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

            tmr.create():alarm(10000, tmr.ALARM_AUTO, loop)
        end
    end)
end

tmr.create():alarm(3000, tmr.ALARM_SINGLE, setup)
