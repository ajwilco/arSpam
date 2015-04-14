<?php
	
Print <<< END
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	var tl,
		tlTest,
		tlOptions,
		data=[],
		testData=[],
		chart,
		testChart,
		options,
		current=0,
		testCurrent=0,
		dataClassPie,
		dataTestPie,
		optionsClassPie,
		optionsClassColumn,
		dataCountryPie,
		optionsCountryPie,
		dataHeatMap,
		optionsHeatMap;
	
	google.load("visualization", "1.1", {packages:["corechart", "geomap", "annotationchart"]});
	google.setOnLoadCallback(getVars);
	
	var button = document.getElementById('classButton');
	button.onclick = function() {
		current = 1 - current;
		drawColumnChart();
	}
	
	var testButton = document.getElementById('testButton');
	testButton.onclick = function() {
		testCurrent = 1 - testCurrent;
		drawTestColumnChart();
	}
		
	function getVars(){
		//Timeline Values
		tl = new google.visualization.DataTable();
		{$timeline}
		
		tlOptions = {
			displayAnnotations: 'false',
			fill: 25,
			scaleType: 'maximized',
			thickness: 2,
			legendPosition: 'newRow',
			zoomButtonsOrder: [
				'3-hours','6-hours','9-hours','12-hours','max'
			],
			zoomButtons: {
				'3-hours':  { 'label': '3h',  'offset': [3, 0, 0] },
				'6-hours':  { 'label': '6h',  'offset': [6, 0, 0] },
				'9-hours':  { 'label': '9h',  'offset': [9, 0, 0] },
				'12-hours': { 'label': '12h', 'offset': [12, 0, 0] },
				'max': {
					'label': 'max',
					'range': {
						'start': null,
						'end': null
					}
				}
			}
		}
	
		
		//Stacked Columns Values
		var row1 = [{$graphHead}, {$tableData}];
		var row2 = [{$percentHead}, {$tableDataPercent}];
		data[0] = google.visualization.arrayToDataTable(row1);
		data[1] = google.visualization.arrayToDataTable(row2);
		
END;
		if($testGraphHead!=""){
Print <<< END
			tlTest=new google.visualization.DataTable();
			{$testTimeline}
			
			var testRow1 = [{$testGraphHead}, {$testTableData}];
			var testRow2 = [{$testPercentHead}, {$testTableDataPercent}];
			testData[0] = google.visualization.arrayToDataTable(testRow1);
			testData[1] = google.visualization.arrayToDataTable(testRow2);
			
			testChart = new google.visualization.ColumnChart(document.getElementById("testcolumnchart_values"));
END;
		}
Print <<< END
		options = {
			height: 450,
			chartArea: { left: 50, width: '100%' },
			legend: { position: 'top', maxLines: 10 },
			bar: { groupWidth: '75%' },
			hAxis: { slantedText: 'true' },
			isStacked: true,
			tooltip:{
				isHtml: 'true'
			},
			animation:{
				duration: 500,
				easing: 'linear',
				startup: 'true'
			}
		};
		chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));

	
		//Class Pie+Column Chart Data
		dataClassPie = google.visualization.arrayToDataTable([
			$classPieData
		]);
		optionsClassPie = {
			legend: 'none',
			height: 450,
			chartArea: { width: '96%', height: '100%' },
			sliceVisibilityThreshold: 1/100
		};
		optionsClassColumn={
			legend: 'none',
			chartArea: { width: '85%' },
			height: 450
		};
		
		//Test Pie+Column Chart Data
		dataTestPie = google.visualization.arrayToDataTable([
			$testPieData
		]);

	
		//Country Pie Data
		dataCountryPie = google.visualization.arrayToDataTable([
			$countryData
		]);
		optionsCountryPie = {
			title: 'Messages per Country',
			height: 450,
			chartArea: { top: 20, width: '100%', height: '100%' },
			legend: 'none',
			sliceVisibilityThreshold: 1/100
		};

	
		//Heatmap Values
		dataHeatMap = google.visualization.arrayToDataTable([
			$countryData
		]);
		optionsHeatMap = {
			title: 'Messages by Country',
			height: 450,
			width: '100%',
			dataMode: 'regions',
			colors: [0xD69999, 0x990000]
		};
		drawChart();
	}
	
	
	function drawChart() {
	
		//Create Class Timeline
		var timeline = new google.visualization.AnnotationChart(document.getElementById('timeline_div'));
        timeline.draw(tl, tlOptions);		
		
		// Create Class Pie and Column Chart
		var classPie = new google.visualization.PieChart(document.getElementById("piechart_classes"));
		classPie.draw(dataClassPie, optionsClassPie);	  
		
		var classColumns = new google.visualization.ColumnChart(document.getElementById("barchart_classes"));
		classColumns.draw(dataClassPie, optionsClassColumn);	
	  
		// Create Country Pie Chart
		var countryPie = new google.visualization.PieChart(document.getElementById("piechart_country"));
        countryPie.draw(dataCountryPie, optionsCountryPie);
	  
	  
		// Create Heat Map
		var heatMap = new google.visualization.GeoMap(document.getElementById("map_values"));
        heatMap.draw(dataHeatMap, optionsHeatMap);
		
		drawColumnChart();
		
END;
		if($testTimeline!=""){
Print <<< END
			//Create Test Timeline
			var testTimeline = new google.visualization.AnnotationChart(document.getElementById('testtimeline_div'));
			testTimeline.draw(tlTest, tlOptions);
			
			// Create Test Pie and Column Chart
			var testPie = new google.visualization.PieChart(document.getElementById("piechart_fails"));
			testPie.draw(dataTestPie, optionsClassPie);	  
			
			var testColumns = new google.visualization.ColumnChart(document.getElementById("barchart_fails"));
			testColumns.draw(dataTestPie, optionsClassColumn);
			
			drawTestColumnChart();
END;
		}
Print <<< END
	}
	
	function drawColumnChart() {
		// Disabling the button while the chart is drawing.
		button.disabled = true;
		google.visualization.events.addListener(chart, 'ready',
		function() {
			button.disabled = false;
			button.innerHTML = (current ? "<i class='fa fa-align-right fa-rotate-90'></i>" : "<i class='fa fa-align-justify fa-rotate-90'></i>") + ' Switch to ' + (current ? 'Total' : 'Percent');
		});
		
		chart.draw(data[current], options);
	}
	
	function drawTestColumnChart() {
		// Disabling the button while the chart is drawing.
		testButton.disabled = true;
		google.visualization.events.addListener(testChart, 'ready',
		function() {
			testButton.disabled = false;
			testButton.innerHTML = (current ? "<i class='fa fa-align-right fa-rotate-90'></i>" : "<i class='fa fa-align-justify fa-rotate-90'></i>") + ' Switch to ' + (current ? 'Total' : 'Percent');
		});
		
		testChart.draw(testData[testCurrent], options);
	}

	  
    </script>
END;

?>