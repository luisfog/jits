var stop = false;

var randomScalingFactor = function() {
	return Math.round(Math.random() * 100);
};

var configLine = {
            type: 'line',
            options: {
                responsive: true,
                title:{
                    display:false
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        };
		
var configBar = {
			type: 'bar',
			options: {
				// Elements options apply to all of the options unless overridden in a dataset
				// In this case, we are setting the border of each horizontal bar to be 2px wide
				elements: {
					rectangle: {
						borderWidth: 2,
					}
				},
				responsive: true,
				legend: {
					position: 'right',
				},
				title: {
					display: false
				}
			}
        };
		
var configArea = {
            type: 'line',
            options: {
                responsive: true,
                title:{
                    display:false
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                    yAxes: [{
                        display: true,
						stacked: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        };

var colorNames = Object.keys(window.chartColors);
var chart = [null,null,null];
var chartElement = ["chartAVG","chartMin","chartMax"];
var config = [];
var datasetsBar = [[], [], []];
var datasetsLine = [[], [], []];
var labels = [];

function changeCharType(){
	var chartType = document.getElementById("chartType");
	chartType = chartType.options[chartType.selectedIndex].value;
	
	for(var i=0; i<3; i++){
		if(chartType == "line"){
			config[i] = Object.assign({}, configLine);
		}else if(chartType == "bar"){
			config[i] = Object.assign({}, configBar);
		}else if(chartType == "area"){
			config[i] = Object.assign({}, configArea);
		}
	
		if(chart[i] != null){
			chart[i].clear();
			chart[i].destroy();
		}
		var ctx = document.getElementById(chartElement[i]);
		ctx.innerHTML = "";
		ctx.height = 100;
		chart[i] = new Chart(ctx, config[i]);
	
		config[i].data.datasets = [];
		for(var j = 0; j < datasetsBar[i].length; j++){
			if(chartType == "bar"){
				config[i].data.datasets.push(datasetsBar[i][j]);
			}else if(chartType == "line"){
				var dataset = datasetsLine[i][j];
				dataset.fill = false;
				config[i].data.datasets.push(dataset);
			}else{
				var dataset = datasetsLine[i][j];
				dataset.fill = true;
				config[i].data.datasets.push(dataset);
			}
		}
		config[i].data.labels = labels;
		
		chart[i].update();
	}
}


function fillDataWhen(){
	var dataSize = document.getElementById("dataSize");
	dataSize = dataSize.options[dataSize.selectedIndex].value;
	var dataWhen = document.getElementById("dataWhen");
	
	while (dataWhen.options.length) {
        dataWhen.remove(0);
    }
	
	if(dataSize == "day"){
		var d = new Date();
		
		var option = document.createElement("option");
		option.value = 0;
		option.text = "Today";
		dataWhen.add(option);
		var option = document.createElement("option");
		option.value = 1;
		option.text = "Last day";
		dataWhen.add(option);
		
		for(var i=2; i<30; i++){
			var option = document.createElement("option");
			option.value = i;
			option.text = i + " days ago";
			dataWhen.add(option);
		}
		document.getElementById("dataWhen").style.visibility = "visible";
		document.getElementById("rowMax").style.visibility = "visible";
		document.getElementById("rowMin").style.visibility = "visible";
		stop = true;
	}else if(dataSize == "month"){
		var d = new Date();
		var lastMonth = d.getMonth()-1;
		var monthNames = ["January", "February", "March", "April", "May", "June",
						  "July", "August", "September", "October", "November", "December"];
						  
		for(var i=0; i<12; i++){
			var option = document.createElement("option");
			if(lastMonth-i >= 0){
				option.value = i;
				option.text = monthNames[lastMonth-i];
			}else{
				option.value = i;
				option.text = monthNames[12+lastMonth-i];
			}
			dataWhen.add(option);
		}
		document.getElementById("dataWhen").style.visibility = "visible";
		document.getElementById("rowMax").style.visibility = "visible";
		document.getElementById("rowMin").style.visibility = "visible";
		stop = true;
	}else if(dataSize == "year"){
		var d = new Date();
		var currentYear = d.getFullYear();
		
		for(var i=0; i<5; i++){
			var option = document.createElement("option");
			option.value = i;
			option.text = currentYear-i;
			dataWhen.add(option);
		}
		document.getElementById("dataWhen").style.visibility = "visible";
		document.getElementById("rowMax").style.visibility = "visible";
		document.getElementById("rowMin").style.visibility = "visible";
		stop = true;
	}else{
		document.getElementById("dataWhen").style.visibility = "hidden";
		document.getElementById("rowMax").style.visibility = "hidden";
		document.getElementById("rowMin").style.visibility = "hidden";
		stop = false;
		setTimeout(updateDataset, 1000);
	}
	changeWhen();
}

function changeWhen(){
	var chartType = document.getElementById("chartType");
	if(chartType.selectedIndex == -1)
		return;
	chartType = chartType.options[chartType.selectedIndex].value;
	
	var dataSize = document.getElementById("dataSize");
	dataSize = dataSize.options[dataSize.selectedIndex].value;
	
	var dataWhen = document.getElementById("dataWhen");
	if(dataWhen.selectedIndex == -1)
		dataWhen = -1;
	else
		dataWhen = dataWhen.options[dataWhen.selectedIndex].value;
	

	$.ajax({
		method: "POST",
		url: "./server/getData.php",
		data: { connectionKey: conKey, columns: values, size: dataSize, when: dataWhen },
		statusCode: {
			200: function (response) {
				var arr =  JSON.parse(response);
				
				
				datasetsBar = [[], [], []];
				datasetsLine = [[], [], []];
				labels = [];
				var indexDataSets = [0,0,0];
				var date;

				for(var i = 0; i < arr.length; i++) {
					for(key in arr[i]) {
						if(key == "creation"){
							date = arr[i][key];
							labels[i] = date;
						}else{
							var datasetIndex = 0;
							if(key.includes("AVG")){
								datasetIndex = 0;
							}else if(key.includes("MIN")){
								datasetIndex = 1;
							}else if(key.includes("MAX")){
								datasetIndex = 2;
							}
								
							if(i == 0){
								var colorName = colorNames[indexDataSets[datasetIndex] % colorNames.length];
								var newColor = window.chartColors[colorName];
								
								var labelName = key;
								if(dataSize != "realTime"){
									labelName = key.substring(4,key.length-1);
								}
								
								datasetsBar[datasetIndex][indexDataSets[datasetIndex]] = {
													label: labelName,
													backgroundColor: Chart.helpers.color(newColor).alpha(0.5).rgbString(),
													borderColor: newColor,
													data: []
												}
								datasetsLine[datasetIndex][indexDataSets[datasetIndex]] = {
													label: labelName,
													backgroundColor: Chart.helpers.color(newColor).alpha(0.5).rgbString(),
													borderColor: newColor,
													data: []
												}
							}
							datasetsBar[datasetIndex][indexDataSets[datasetIndex]].data.push(arr[i][key]);
							datasetsLine[datasetIndex][indexDataSets[datasetIndex]].data.push({x: date, y: arr[i][key]});
							indexDataSets[datasetIndex]++;
						}
				  }
				  indexDataSets[0] = 0;
				  indexDataSets[1] = 0;
				  indexDataSets[2] = 0;
				}
				
				for(var i=0; i<3; i++){
					config[i].data.datasets = [];
					for(var j = 0; j < datasetsBar[i].length; j++){
						if(chartType == "bar"){
							config[i].data.datasets.push(datasetsBar[i][j]);
						}else if(chartType == "line"){
							var dataset = datasetsLine[i][j];
							dataset.fill = false;
							config[i].data.datasets.push(dataset);
						}else{
							var dataset = datasetsLine[i][j];
							dataset.fill = true;
							config[i].data.datasets.push(dataset);
						}
					}
					config[i].data.labels = labels;
				
					chart[i].update();
				}
			},
			500: function (response) {
				alert("Cannot read the requested data.");
			}
		}
	});
}

function updateDataset(){
	if(stop)
		return;
	
	var reDraw = false;
	
	$.ajax({
		method: "POST",
		url: "./server/getData.php",
		data: { connectionKey: conKey, columns: values, size: "realTime", when: 0 },
		statusCode: {
			200: function (response) {
				var arr =  JSON.parse(response);
				
				var indexDataSets = [0,0,0];
				var date;
				
				for(var i = 0; i < arr.length; i++) {
					for(key in arr[i]) {
						if(key == "creation"){
							date = arr[i][key];
							config[0].data.labels.push(date);
						}else{
							try {
								datasetsBar[0][indexDataSets[0]].data.push(arr[i][key]);
								datasetsLine[0][indexDataSets[0]].data.push({x: date, y: arr[i][key]});
								indexDataSets[0]++;
							}
							catch(err) {
								reDraw = true;
								
								var colorName = colorNames[indexDataSets[0] % colorNames.length];
								var newColor = window.chartColors[colorName];
								
								datasetsBar[0][indexDataSets[0]] = {
													label: key,
													backgroundColor: Chart.helpers.color(newColor).alpha(0.5).rgbString(),
													borderColor: newColor,
													data: []
												}
								datasetsLine[0][indexDataSets[0]] = {
													label: key,
													backgroundColor: Chart.helpers.color(newColor).alpha(0.5).rgbString(),
													borderColor: newColor,
													data: []
												}
								datasetsBar[0][indexDataSets[0]].data.push(arr[i][key]);
								datasetsLine[0][indexDataSets[0]].data.push({x: date, y: arr[i][key]});
								indexDataSets[0]++;
								
								
							}
						}
					}
					indexDataSets[0] = 0;
				}
				if(reDraw){
					config[0].data.datasets = [];
					for(var j = 0; j < datasetsBar[0].length; j++){
						if(chartType == "bar"){
							config[0].data.datasets.push(datasetsBar[0][j]);
						}else if(chartType == "line"){
							var dataset = datasetsLine[0][j];
							dataset.fill = false;
							config[0].data.datasets.push(dataset);
						}else{
							var dataset = datasetsLine[0][j];
							dataset.fill = true;
							config[0].data.datasets.push(dataset);
						}
					}
					config[0].data.labels = labels;
				}
				chart[0].update();
			},
			500: function (response) {
				//alert("Cannot read the requested data.");
			}
		}
	});
	
	if(!stop)
		setTimeout(updateDataset, 1000);
}
