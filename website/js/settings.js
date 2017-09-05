
function updateMail(){
	var webUser = document.getElementById("webUser").value;
	var mail = document.getElementById("email").value;
	
	$.ajax({
		method: "POST",
		url: "./server/updateMail.php",
		data: { email: mail },
		statusCode: {
			200: function (response) {
				document.getElementById('modalOk').style.display = 'block';
			},
			500: function (response) {
				alert("There is something wrong with the server. Please try again later.");
			}
		}
	});
}

function updateTimezone(){
	var timezoneI = document.getElementById("timezone").value;
	
	$.ajax({
		method: "POST",
		url: "./server/updateTimezone.php",
		data: { timezone: timezoneI },
		statusCode: {
			200: function (response) {
				document.getElementById('modalOk').style.display = 'block';
			},
			500: function (response) {
				alert("There is something wrong with the server. Please try again later.");
			}
		}
	});
}

function updatePass(){
	var webUser = document.getElementById("webUser").value;
	var pass = document.getElementById("pass").value;
	var newPass = document.getElementById("newPass").value;
	var newPassCon = document.getElementById("newPassCon").value;
	
	if(pass == ""){
		alert("You need to insert the old password.");
		return;
	}
	
	if(newPass != newPassCon){
		alert("Confirmation password is not the same as password.");
		return;
	}
	
	if(newPass == ""){
		alert("The new password is an empty field.");
		return;
	}
	
	$.ajax({
		method: "POST",
		url: "./server/updatePassword.php",
		data: { oldPassword: pass, newPassword: newPass },
		statusCode: {
			200: function (response) {
				document.getElementById('modalOk').style.display = 'block';
			},
			403: function (response) {
				alert("The old password is not correct.");
			},
			500: function (response) {
				alert("There is something wrong with the server. Please try again later.");
			}
		}
	});
}
