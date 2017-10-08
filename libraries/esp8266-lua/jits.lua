--[[
	Jits.lua - Library to connect with JSON IoT Server.
	Created by Luis Gomes
	
	Released into the public domain.
	https://github.com/luisfog/jits
--]]

local jits = {}

-- Creates a json String from two arrays (one for the attributes and other for the values)
function jits.fromArrayToJSON (names, values)
    if #names ~= #values then
        return nil
    end

    local res = ""
    for i = 1, #names do
      res = res .. "\"" .. names[i] .. "\" : \"" .. values[i] .. "\","
    end
    
    return "{" .. string.sub(res, 0, -2) .. "}"
end

-- Applies the AES encryption in a json message, using a given iv code
function jits.encryptJSON (json, aesKey, aesIV)
    return crypto.toBase64(crypto.encrypt("AES-CBC",
            aesKey,json,encoder.fromBase64(aesIV)))
end

-- Creates the URL for publishimg data into JITS
function jits.createPublisherURL(server, connectionKey)
    return server .. "publisher.php?con=" .. connectionKey
end

-- Creates the URL for requesting and activate the iv
function jits.createIvURL(server, connectionKey)
    return server .. "generatorIV.php?con=" .. connectionKey
end

-- Sends an encrypted json to JITS server
function jits.sendDataEncript (server, connectionKey, encryptedJSON)
    http.post(jits.createPublisherURL(server, connectionKey),
            "Content-Type: text/plain"..
            "Content-Length: " .. string.len(encryptedJSON),
            encryptedJSON,
            function(code, data)
                if (code < 0) then
                    print("jits: ERROR")
                else
                    print("jits: Data Sent")
                end
            end)
end

-- Sends a json to JITS server (starts with the request and activation of iv)
function jits.sendDataJson (server, connectionKey, json, aesKey)
    http.get(jits.createIvURL(server, connectionKey),
            nil,
            function(code, aesIV)
                if (code < 0) then
                    print("jits: ERROR")
                else
                    local encryptedJSON = jits.encryptJSON (json, aesKey, aesIV)
                    jits.sendDataEncript (server, connectionKey, encryptedJSON)
                end
            end)
end

-- Sends two arrays (one for the attributes and other for the values) to JITS server
function jits.sendDataArray (server, connectionKey, names, values, aesKey)
    local json = jits.fromArrayToJSON (names, values)
    jits.sendDataJson (server, connectionKey, json, aesKey)
end

return jits
