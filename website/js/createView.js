
function checkName(){
	var name_ui = document.getElementById("name").value;
	
	if(name_ui == ""){
		alert("Name field is empty.");
		return;
	}
	
	$.ajax({
		method: "POST",
		url: "./server/checkName.php",
		data: { name: name_ui, table: 'views' },
		statusCode: {
			202: function (response) {
				document.getElementById("nameAvailable").style.visibility = "visible";
				createView();
			},
			500: function (response) {
				alert("The name is already in use.");
			}
		}
	});
}

function createView(){
	var name_ui = document.getElementById("name").value;
	var tableValues = document.getElementById("values");
	var list_ui = "[";
	
	for (var i = 1, row; row = tableValues.rows[i]; i++) {
		list_ui += "{\"connectionKey\":\"" + row.cells[0].title + "\",";
		list_ui += "\"clientName\":\"" + row.cells[0].innerHTML + "\",";
		list_ui += "\"value\":\"" + row.cells[1].innerHTML + "\",";
		list_ui += "\"columnName\":\"" + row.cells[2].innerHTML + "\"},";
	}
	
	if(list_ui.length > 2)
		list_ui = list_ui.slice(0,-1) + ']';
	else{
		alert("Table of values is empty.");
		return;
	}
	
	$.ajax({
		method: "POST",
		url: "./server/registerView.php",
		data: { name: name_ui, list: list_ui },
		statusCode: {
			200: function (response) {
				document.getElementById("registerView").style.visibility = "visible";
				
				updateViewList();
			},
			500: function (response) {
				alert(response.responseText);
			}
		}
	});
}

function updateViewList(){	
	$.ajax({
		method: "POST",
		url: "./server/viewList.php",
		statusCode: {
			200: function (response) {
				var ul = document.getElementById("viewList");
				while( ul.firstChild ){
				  ul.removeChild( ul.firstChild );
				}
				
				var jsonData = JSON.parse(response);
				for (var i = 0; i < jsonData.length; i++) {
					var li = document.createElement("li");
					var a = document.createElement('a');
					a.setAttribute('href',"view.php?view=" + jsonData[i].name);
					a.innerHTML = jsonData[i].name;
					li.appendChild(a);
					ul.appendChild(li);
				}
				
				
			}
		}
	})
}

function changeValues(){
	var connectionKey_ui = document.getElementById("client").value;
	
	$.ajax({
		method: "POST",
		url: "./server/getClientValues.php",
		data: { connectionKey: connectionKey_ui },
		statusCode: {
			200: function (response) {
				var selectHTML = document.getElementById("value");
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

function addValue(){
	var client = document.getElementById("client");
	var keyClient = client.value;
	var nameClient = client.options[client.selectedIndex].innerHTML;
	var value = document.getElementById("value").value;
	var columnValue = document.getElementById("volumnName").value;
	
	if(keyClient == "-" || value == "-")
		return;
	
	var tableValues = document.getElementById("values");
	
	if(columnValue == "")
		columnValue = nameClient + "::" + value;
	
	for (var i = 1, row; row = tableValues.rows[i]; i++) {
		if(row.cells[0].title == keyClient && row.cells[1].innerHTML == value){
			alert("You already use that Value.");
			return;
		}
		if(row.cells[2].innerHTML == columnValue){
			alert("You already use that Column Name.");
			return;
		}
	}
	
	var row = tableValues.insertRow(-1);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    var cell4 = row.insertCell(3);
    cell1.title = keyClient;
    cell1.innerHTML = nameClient;
    cell2.innerHTML = value;
    cell3.innerHTML = columnValue;
    cell4.innerHTML = "<div class='x spin small slow'><b></b><b></b><b></b><b></b></div>";
	cell4.onclick = function () {
            var p=this.parentNode;
			p.parentNode.removeChild(p);
        };
}
