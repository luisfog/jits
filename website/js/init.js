
var counter = 10;

function updateFields(value){
	if(value == "new"){
		document.getElementById("user").placeholder = "MySQL root username, e.g. root";
		document.getElementById("pass").placeholder = "MySQL root password";
		document.getElementById("database").disabled = true;
	} else if(value == "old"){
		document.getElementById("user").placeholder = "MySQL username, e.g. jitsUser";
		document.getElementById("pass").placeholder = "MySQL password";
		document.getElementById("database").disabled = false;
	}
}


function createWorld(){
	var webUser = document.getElementById("webUser").value;
	var email_ui = document.getElementById("email").value;
	var webPass = document.getElementById("webPass").value;
	var webPassCon = document.getElementById("webPassCon").value;
	var timezone = document.getElementById("timezone").value;
	var serverI = document.getElementById("server").value;
	var userI = document.getElementById("user").value;
	var passI = document.getElementById("pass").value;
	
	if(webUser == ""){
		alert("Username field is empty.");
		return;
	}
	if(email_ui == "" || email_ui.indexOf(".") == -1 || email_ui.indexOf("@") == -1){
		alert("Email not valid.");
		return;
	}
	if(webPass == ""){
		alert("Password field is empty.");
		return;
	}
	if(webPass != webPassCon){
		alert("Confirmation password is not the same as password.");
		return;
	}
	if(timezone == "selectTime"){
		alert("Please choose a timezone.");
		return;
	}
	if(serverI == ""){
		alert("MySQL server field is empty.");
		return;
	}
	if(userI == ""){
		alert("MySQL root username field is empty.");
		return;
	}
	
	
	var mySQLselect = document.getElementById("mySQLselect");
	if(mySQLselect.value == "new")
		createDatabase(serverI, userI, passI)
	else if(mySQLselect.value == "old")
		createDBinfo(serverI, userI, passI);
}

function createDBinfo(serverI, userI, passI){
	var databaseI = document.getElementById("database").value;
	
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, database: databaseI, order: "dbinfo" },
		statusCode: {
			200: function (response) {
				document.getElementById("createDatabase").innerHTML = "...";
				document.getElementById("createDatabase").style.visibility = "visible";
				document.getElementById("createUser").innerHTML = "...";
				document.getElementById("createUser").style.visibility = "visible";
				document.getElementById("givePermissions").innerHTML = "...";
				document.getElementById("givePermissions").style.visibility = "visible";
				createClients(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function createDatabase(serverI, userI, passI){
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "database" },
		statusCode: {
			200: function (response) {
				document.getElementById("createDatabase").style.visibility = "visible";
				createUser(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function createUser(serverI, userI, passI){	
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "user"},
		statusCode: {
			200: function (response) {
				document.getElementById("createUser").style.visibility = "visible";
				givePermissions(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function givePermissions(serverI, userI, passI){
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "permissions" },
		statusCode: {
			200: function (response) {
				document.getElementById("givePermissions").style.visibility = "visible";
				createClients(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function createClients(serverI, userI, passI){
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "clients" },
		statusCode: {
			200: function (response) {
				document.getElementById("createClients").style.visibility = "visible";
				createViews(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function createViews(serverI, userI, passI){
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "views" },
		statusCode: {
			200: function (response) {
				document.getElementById("createViews").style.visibility = "visible";
				createAlarms(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function createAlarms(serverI, userI, passI){
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "alarms" },
		statusCode: {
			200: function (response) {
				document.getElementById("createAlarms").style.visibility = "visible";
				createWebUser(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function createWebUser(serverI, userI, passI){	
	var webUserI = document.getElementById("webUser").value;
	var emailI = document.getElementById("email").value;
	var webPassI = document.getElementById("webPass").value;
	var timezoneI = document.getElementById("timezone").value;
	
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "webUuser", webUser: webUserI, webPass: webPassI, email: emailI, timezone: timezoneI},
		statusCode: {
			200: function (response) {
				document.getElementById("createWebUser").style.visibility = "visible";
				deleteInit(serverI, userI, passI);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function deleteInit(serverI, userI, passI){
	
	$.ajax({
		method: "POST",
		url: "./server/init.php",
		data: { server: serverI, user: userI, pass: passI, order: "deleteInit"},
		statusCode: {
			200: function (response) {
				document.getElementById("deleteInits").style.visibility = "visible";
				document.getElementById("refresh").style.visibility = "visible";
				setTimeout(decounter, 1000);
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function decounter(){
	document.getElementById("refresh").innerHTML = counter--;
	if(counter == 0){
		location.reload();
	}
	setTimeout(decounter, 1000);
}