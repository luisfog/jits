local jits = {}

function jits.fromArrayToJSON (names, values)
    if #names ~= #values then
        return nil
    end

    local res = ""
    for i = 1, #names do
      res = res .. "\"" .. names[i] .. "\": \"" .. values[i] .. "\","
    end
    
    return "{" .. string.sub(res, 0, -2) .. "}"
end

function jits.encryptJSON (json, aesKey, aesIV)
    return crypto.toBase64(crypto.encrypt("AES-CBC",
            aesKey,json,encoder.fromBase64(aesIV)))
end

function jits.createURL(server, connectionKey)
    return server .. "?con=" .. connectionKey
end

function jits.sendData (server, connectionKey, encryptedJSON)
    http.post(jits.createURL(server, connectionKey),
            "Content-Type: text/plain"..
            "Content-Length: " .. string.len(encryptedJSON),
            encryptedJSON,
            function(code, data)
                if (code < 0) then
                    print("HTTP request failed")
                else
                    print(code, data)
                end
            end)
end

function jits.sendData (server, connectionKey, json, aesKey, aesIV)
    local encryptedJSON = jits.encryptJSON (json, aesKey, aesIV)
    http.post(jits.createURL(server, connectionKey),
            "Content-Type: text/plain"..
            "Content-Length: " .. string.len(encryptedJSON),
            encryptedJSON,
            function(code, data)
                if (code < 0) then
                    print("HTTP request failed")
                else
                    print(code, data)
                end
            end)
end

function jits.sendData (server, connectionKey, names, values, aesKey, aesIV)
    local json = jits.fromArrayToJSON (names, values)
    if json == nil then
        print ("Error creating json")
        return
    end
    local encryptedJSON = jits.encryptJSON (json, aesKey, aesIV)
    http.post(jits.createURL(server, connectionKey),
            "Content-Type: text/plain"..
            "Content-Length: " .. string.len(encryptedJSON),
            encryptedJSON,
            function(code, data)
                if (code < 0) then
                    print("HTTP request failed")
                else
                    print(code, data)
                end
            end)
end

return jits