
var dataArr = [];
var myCharts = [];

var options = {
    tooltip : {
        trigger: 'item',
        formatter: "<br/>{b} : {c}%"
    },
    calculable : true,
    series : [
        {
            type:'pie',
            radius : [10, 80],
            center : ['50%', '50%'],
            roseType : 'radius',
            label: {
                normal: {
                    show: false
                },
                emphasis: {
                    show: true
                }
            },
            lableLine: {
                normal: {
                    show: false
                },
                emphasis: {
                    show: true
                }
            }
        }
    ]
};

function createChart(nameUI, dataValues){
	var chart = document.getElementById(nameUI);
	var myChart = echarts.init(chart);
	myCharts.push(myChart);
	myChart.setOption(options);
	
	var seriesArr = [];
	seriesArr[0] = {
						type:'pie',
						radius : [10, 80],
						center : ['50%', '50%'],
						roseType : 'radius',
						label: {
							normal: {
								show: false
							},
							emphasis: {
								show: true
							}
						},
						lableLine: {
							normal: {
								show: false
							},
							emphasis: {
								show: true
							}
						},
						data: dataValues
					}
	
	options.series = seriesArr;
	myChart.setOption(options);
}

window.onresize = function(event) {
	myCharts.forEach(  
		function resizeChart(myChart) {
			if(myChart != null && myChart != undefined){
				myChart.resize();
			}
		}  
	);
}
