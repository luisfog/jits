
var stop = false;
var firstReal = true;
var labels = [];
var dataArr = [];
var dataArrKeys = [];

var chart;
var myChart;

function initChart(){
	chart = document.getElementById('chart');
	myChart = echarts.init(chart);
	
	var valuesArr = columnsNamesList.split(",");
	for(var i=0; i<valuesArr.length; i++){
		dataArr[i] = [];
		dataArrKeys[i] = valuesArr[i];
	}
}
				
var options = 	{
					tooltip: {
						trigger: 'axis'
					},
					legend: {
						data: ""
					},
					toolbox: {
						show: true,
						right: '0%',
						orient: 'vertical',
						feature: {
							dataZoom: {
								yAxisIndex: 'none',
								title: {
									zoom: 'Zoom In',
									back: 'Zoom Out',
								}
							},
							magicType: {
								type: ['line', 'bar', 'stack', 'tiled'],
								title: {
									line: 'Line',
									bar: 'Bar',
									stack: 'Stack',
									tiled: 'Tiled'
								}
							},
							saveAsImage: {title: "Save PNG"}
						}
					},
					dataZoom: [{
							start: 90,
							end: 100,
							handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
							handleSize: '80%',
							handleStyle: {
								color: '#fff',
								shadowBlur: 3,
								shadowColor: 'rgba(0, 0, 0, 0.6)',
								shadowOffsetX: 2,
								shadowOffsetY: 2
							},
							bottom: 0
						}],
					xAxis:  {
						type: 'category',
						boundaryGap: false
					},
					yAxis: {
						type: 'value',
					}
				};


function deleteView(){
	$.ajax({
		method: "POST",
		url: "./server/deleteView.php",
		data: { name: viewName },
		statusCode: {
			200: function (response) {
				window.location = "./index.php";
			},
			500: function (response) {
				alert("The view could not be deleted, please try again later.");
			}
		}
	});
}

function getData(){
	var dataLong_ui = document.getElementById("dataLong");
	
	if(dataLong_ui.selectedIndex == 0){
		if(stop){
			stop = false;
			firstReal = true;
			while(labels.length > 0) labels.pop();
			while(dataArr.length > 0) dataArr.pop();
			while(options.legend.data.length > 0) options.legend.data.pop();
			while(options.xAxis.data.length > 0) options.xAxis.data.pop();
			while(options.series.length > 0) options.series.pop();
			
			var valuesArr = columnsNamesList.split(",");
			for(var i=0; i<valuesArr.length; i++){
				dataArr[i] = [];
				dataArrKeys[i] = valuesArr[i];
			}
		}
	}else{
		stop = true;
	}
	
	if(dataLong_ui.selectedIndex == 0 && labels.length > 0)
		dataLong_ui = labels[labels.length - 1];
	else
		dataLong_ui = dataLong_ui.options[dataLong_ui.selectedIndex].value;
	
	var load = false;
	if(stop){
		document.getElementById("loading").style.visibility = 'visible';
		load = true;
	}
	
	$.ajax({
		method: "POST",
		url: "./server/getViewData.php",
		data: { name: viewName, connectionKeys: connectionKeysList, columnsNames: columnsNamesList,
				values: valuesList, dataLong: dataLong_ui },
		statusCode: {
			200: function (response) {
				
				var arr =  JSON.parse(response);
				
				if(stop){
					myChart = echarts.init(chart);
				}
				
				for(var i = 0; i < arr.length; i++) {
					var maxSize = 0;
					for(key in arr[i]) {
						if(key == "creation"){
							if(!stop && !firstReal){
								labels.push(arr[i][key]);
							}else{
								if(i == 0){
									labels = [arr[i][key]];
								}else{
									labels.push(arr[i][key]);
								}
							}
						}else{
							if(!stop && !firstReal){
								for(var k=0; k<dataArrKeys.length; k++)
									if(dataArrKeys[k] == key){
										dataArr[k].push(arr[i][key]);
										maxSize = dataArr[k].length;
										break;
									}
							}else{
								for(k=0; k<dataArrKeys.length; k++)
									if(dataArrKeys[k] == key){
										dataArr[k].push(arr[i][key]);
										maxSize = dataArr[k].length;
										break;
									}
							}
						}
					}
					
					for(var j=0; j<dataArr.length; j++){
						while(dataArr[j].length < maxSize){
							dataArr[j].push(dataArr[j][dataArr[j].length-1]);
						}
					}
				}
				
				var valuesArr = columnsNamesList.split(",");
				
				var seriesArr = [];
				
				if(stop || firstReal){
					for(var i=0; i<dataArr.length; i++){
						seriesArr[i] = {
									name: valuesArr[i],
									type: 'line',
									sampling: 'average',
									data: dataArr[i],
									markPoint: {
										data: [
											{type: 'max', name: 'Max'},
											{type: 'min', name: 'Min'}
										]
									},
									markLine: {
										data: [
											{type: 'average', name: 'AVG'}
										]
									}
								}			
					}
					
					options.legend.data = valuesArr;
					options.xAxis.data = labels;
					options.series = seriesArr;
					myChart.setOption(options);
				}else{
					for(var i=0; i<dataArr.length; i++){
						delete options.series[i]["type"];			
					}
					myChart.setOption({
						legend:{ data: valuesArr},
						series: options.series,
						xAxis:{ data: labels}
					});
				}
				
				if(load)
					document.getElementById("loading").style.visibility = 'hidden';
				firstReal = false;
			},
			500: function (response) {
				alert("Cannot read the requested data.");
				
				if(load)
					document.getElementById("loading").style.visibility = 'hidden';
			}
		}
	});
	
	if(!stop)
		setTimeout(getDataRT, 1000);
}

function getDataRT(){
	if(stop)
		return;
	getData();
}

window.onresize = function(event) {
	if(myChart != null && myChart != undefined){
		myChart.resize();
	}
}

function exportData(){
	var exportType = document.querySelector('input[name="typeExport"]:checked').value;
	
	var content = "";
	
	if(exportType == "tsv")
		content += "date\t" + values.replace(", ", "\t");
	else
		content += "date," + values.replace(" ", "");
		
	for(var i=0; i<labels.length; i++){
		content += "\n" + labels[i];
		
		dataArr.forEach(function(indexValue, index){
			if(exportType == "tsv")
				content += "\t" + indexValue[i];
			else
				content += "," + indexValue[i];
		});
	}
		
	var encodedContent;
	var extension;
	
	if(exportType == "csv"){
		encodedContent = encodeURI("data:text/csv;charset=utf-8," + content);
		extension = "csv";
		
	}else if(exportType == "tsv"){
		encodedContent = encodeURI("data:text/tsv;charset=utf-8," + content);
		extension = "tsv";
		
	}else if(exportType == "json"){
		
		var lines = content.split("\n");
		var result = [];
		var headers=lines[0].split(",");

		for(var i=1;i<lines.length;i++){
			var obj = {};
			var currentline=lines[i].split(",");

			for(var j=0;j<headers.length;j++){
				obj[headers[j]] = currentline[j];
			}
			result.push(obj);
		}
		
		encodedContent = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(result));
		extension = "json";
	}
	
	var link = document.createElement("a");
	link.setAttribute("href", encodedContent);
	link.setAttribute("download", "jits_" + clientName + "." + extension);
	document.body.appendChild(link);
	link.click();
	
	document.getElementById('modalExport').style.display = 'none';
}

