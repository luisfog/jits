var connectionServer;
var connectionKey;
var aesKey;
var aesIV;

function checkName(){
	var nameI = document.getElementById("name").value;
	
	$.ajax({
		method: "POST",
		url: "./server/checkName.php",
		data: { name: nameI, table: "clients" },
		statusCode: {
			202: function (response) {
				document.getElementById("nameAvailable").style.visibility = "visible";
				getServer();
			},
			500: function (response) {
				alert("The name is already in use.");
			}
		}
	})
}

function getServer(){
	var nameI = document.getElementById("name").value;
	
	$.ajax({
		method: "POST",
		url: "./server/getServer.php",
		statusCode: {
			201: function (response) {
				connectionServer = response;
				createConnection();
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	})
}

function createConnection(){
	var nameI = document.getElementById("name").value;
	
	$.ajax({
		method: "POST",
		url: "./server/generator.php",
		data: { name: nameI, length: 30 },
		statusCode: {
			201: function (response) {
				connectionKey = response;
				document.getElementById("generateConn").style.visibility = "visible";
				createAes(nameI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	})
}

function createAes(nameI){
	var lengthI = document.getElementById("AES").value;
	lengthNumeber = 16;
	if(lengthI == "192")
		lengthNumeber = 24;
	if(lengthI == "256")
		lengthNumeber = 32;
	
	$.ajax({
		method: "POST",
		url: "./server/generator.php",
		data: { name: nameI, length: lengthNumeber },
		statusCode: {
			201: function (response) {
				aesKey = response;
				document.getElementById("generateAES").style.visibility = "visible";
				createIV(nameI, lengthI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	})
}

function createIV(nameI, lengthI){	
	$.ajax({
		method: "POST",
		url: "./server/generatorIV.php",
		statusCode: {
			201: function (response) {
				aesIV = response;
				document.getElementById("generateAESiv").style.visibility = "visible";
				createClient(nameI, lengthI, aesIV);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	})
}

function createClient(nameI, lengthI, aesIV){	
	var typeI = document.getElementById("type").value;
	
	$.ajax({
		method: "POST",
		url: "./server/registerClient.php",
		data: { name: nameI, aes: lengthI, type: typeI, connection: connectionKey, aes_key: aesKey, aes_iv: aesIV },
		statusCode: {
			201: function (response) {
				document.getElementById("registerClient").style.visibility = "visible";
				
				document.getElementById("connServer").innerHTML = connectionServer;
				document.getElementById("connKey").innerHTML = connectionKey;
				document.getElementById("aesKey").innerHTML = aesKey;
				document.getElementById("aesIV").innerHTML = aesIV;
				
				document.getElementById("connServer").style.visibility = "visible";
				document.getElementById("connKey").style.visibility = "visible";
				document.getElementById("aesKey").style.visibility = "visible";
				document.getElementById("aesIV").style.visibility = "visible";
				
				updateClientList();
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	})
}

function updateClientList(){
	$.ajax({
		method: "POST",
		url: "./server/clientList.php",
		statusCode: {
			200: function (response) {
				var ul = document.getElementById("clientList");
				while( ul.firstChild ){
				  ul.removeChild( ul.firstChild );
				}
				
				var jsonData = JSON.parse(response);
				for (var i = 0; i < jsonData.length; i++) {
					var li = document.createElement("li");
					var a = document.createElement('a');
					a.setAttribute('href',"client.php?client=" + jsonData[i].connection_key);
					a.innerHTML = jsonData[i].name;
					li.appendChild(a);
					ul.appendChild(li);
				}
				
				
			}
		}
	})
}
