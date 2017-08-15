
window.onload = function() {
	updateAlarmsList();
};

function deleteAlarmConfirmation(ele){
	document.getElementById("modalYesButton").onclick = function() { deleteAlarm(ele.id); };
	document.getElementById("modalDelete").style.display = "block";
}

function deleteAlarm(alarmName){
	$.ajax({
		method: "POST",
		url: "./server/deleteAlarm.php",
		data: { name: alarmName },
		statusCode: {
			200: function (response) {
				updateAlarmsList();
			},
			500: function (response) {
				alert("The view could not be deleted, please try again later.");
			}
		}
	});
}

function checkName(){
	var name_ui = document.getElementById("name").value;
	
	if(name_ui == ""){
		alert("Name field is empty.");
		return;
	}
	
	$.ajax({
		method: "POST",
		url: "./server/checkName.php",
		data: { name: name_ui, table: 'alarms' },
		statusCode: {
			202: function (response) {
				createAlarm(name_ui);
			},
			500: function (response) {
				alert("The name is already in use.");
			}
		}
	});
}

function createAlarm(name_ui){
	var client = document.getElementById("clientsList");
	var keyClient = client.value;
	var nameClient = client.options[client.selectedIndex].innerHTML;
	var value_ui = document.getElementById("valuesList").value;
	var condition_ui = document.getElementById("conditionsList").selectedIndex;
	var target_ui = document.getElementById("target").value;
	var timeExecution_ui = document.getElementById("timeExecution").value;
	
	if(keyClient == "-"){
		alert("You should choose a client.");
		return;
	}
	if(value_ui == "-"){
		alert("You should choose a value.");
		return;
	}
	if(condition_ui == 0){
		alert("You should choose a condition.");
		return;
	}
	if(target_ui == ""){
		alert("You should choose a target.");
		return;
	}
	
	$.ajax({
		method: "POST",
		url: "./server/registerAlarm.php",
		data: { name: name_ui, connectionKey: keyClient, clientName: nameClient,
				value: value_ui, condition: condition_ui, target: target_ui,
				timeExecution: timeExecution_ui},
		statusCode: {
			201: function (response) {
				updateAlarmsList();
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function updateAlarmsList(){	
	$.ajax({
		method: "POST",
		url: "./server/alarmList.php",
		dataType: "json",
		statusCode: {
			200: function (response) {
				var alarms = document.getElementById("alarms");
				while (alarms.firstChild) {
					alarms.removeChild(alarms.firstChild);
				}
				
				for(var i=0; i<response.length; i++){
					
					var new_alarm = document.createElement('div');
					new_alarm.className = "col-sm-4 col-md-4 col-lg-3";
					
					var new_alarm_block = document.createElement('div');
					new_alarm_block.className = "col-12 thumbnail";
					new_alarm_block.style.margin = "10px 10px 10px 10px";
					new_alarm_block.style.padding = "0 15px 0 15px";
					
					var new_alarm_block_title = document.createElement('h3');
					new_alarm_block_title.innerHTML = response[i].name;
					
					var new_alarm_block_condition = document.createElement('p');
					new_alarm_block_condition.innerHTML = "In " + response[i].client_name + " client";
					
					var new_alarm_block_client = document.createElement('p');
					new_alarm_block_client.innerHTML = "If " + response[i].value;
					if(response[i].cond == "equal")
						new_alarm_block_client.innerHTML += " == " + response[i].target;
					if(response[i].cond == "not equal")
						new_alarm_block_client.innerHTML += " != " + response[i].target;
					if(response[i].cond == "less than")
						new_alarm_block_client.innerHTML += " &lt; " + response[i].target;
					if(response[i].cond == "greater than")
						new_alarm_block_client.innerHTML += " &gt; " + response[i].target;
					if(response[i].cond == "less or equal")
						new_alarm_block_client.innerHTML += " &lt;= " + response[i].target;
					if(response[i].cond == "greater or equal")
						new_alarm_block_client.innerHTML += " &gt;= " + response[i].target;
					
					var new_alarm_block_time = document.createElement('p');
					if(response[i].time_target == "0")
						new_alarm_block_time.innerHTML = "Alarm right away";
					else
						new_alarm_block_time.innerHTML = "Alarm after " + response[i].time_target + " minutes";
					
					var new_alarm_block_delete = document.createElement('p');
					var new_alarm_block_delete_input = document.createElement('button');
					new_alarm_block_delete_input.className = "buttonDelete";
					new_alarm_block_delete_input.id = response[i].name;
					new_alarm_block_delete_input.onclick = function() { deleteAlarmConfirmation(this); };
					new_alarm_block_delete_input.innerHTML = "Delete Alarm";
					
					new_alarm_block.appendChild(new_alarm_block_title);
					new_alarm_block.appendChild(new_alarm_block_condition);
					new_alarm_block.appendChild(new_alarm_block_client);
					new_alarm_block.appendChild(new_alarm_block_time);
					new_alarm_block_delete.appendChild(new_alarm_block_delete_input);
					new_alarm_block.appendChild(new_alarm_block_delete);
					new_alarm.appendChild(new_alarm_block);
					alarms.appendChild(new_alarm);
				}
			}
		}
	});
}

function changeValues(){
	var connectionKey_ui = document.getElementById("clientsList").value;
	
	$.ajax({
		method: "POST",
		url: "./server/getClientValues.php",
		data: { connectionKey: connectionKey_ui },
		statusCode: {
			200: function (response) {
				var selectHTML = document.getElementById("valuesList");
				var data = JSON.parse(response);
				
				while( selectHTML.firstChild ){
				  selectHTML.removeChild( selectHTML.firstChild );
				}
				
				var opt = document.createElement('option');
				opt.value = "-";
				opt.innerHTML = "Select a value";
				selectHTML.appendChild(opt);
					
				for(var i=0; i<data.length; i++){
					var opt = document.createElement('option');
					opt.value = data[i];
					opt.innerHTML = data[i];
					selectHTML.appendChild(opt);
				}
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	})
}

function changeTimeExecutionList(){
	if(document.getElementById("timeExecutionList").value == "after"){
		document.getElementById("timeExecution").disabled = false;
		return;
	}
	document.getElementById("timeExecution").disabled = true;
	document.getElementById("timeExecution").value = "";
}
