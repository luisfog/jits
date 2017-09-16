local jits = {}


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

function jits.encryptJSON (json, aesKey, aesIV)
    return crypto.toBase64(crypto.encrypt("AES-CBC",
            aesKey,json,encoder.fromBase64(aesIV)))
end

function jits.createPublisherURL(server, connectionKey)
    return server .. "publisher.php?con=" .. connectionKey
end

function jits.createIvURL(server, connectionKey)
    return server .. "generatorIV.php?con=" .. connectionKey
end

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

function jits.sendDataArray (server, connectionKey, names, values, aesKey)
    local json = jits.fromArrayToJSON (names, values)
    jits.sendDataJson (server, connectionKey, json, aesKey)
end

return jits
