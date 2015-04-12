<?php
include('include.php');

$pagename="";
doPageOpen();


////
//Build WHERE clauses based on form data
///
$formOptions=array("classes", "tests", "countries", "sender");
$formOption= array("class", "test", "country", "sendingIP");
foreach ($formOptions as $optionKey => $option){
	if($_POST[$option]){
		if($where=="") $where.=" WHERE "; else $where.=" AND ";
		if(!$_POST[$option.'Select']){
			foreach($_POST[$option] as $key => $item){
				if($key!=0) $where.=" AND ";
				$where.=$formOption[$optionKey]."ID!='{$item}'";
			}
		}else{
			$includeSelected[$option]=" selected";
			foreach($_POST[$option] as $key => $item){
				if($key==0) $where.="("; else $where.=" OR ";
				$where.=$formOption[$optionKey]."ID='{$item}'";
			}
			$where.=")";
		}
	}
}


////
//Build data for Class charts
///
include('buildClassData.php');


////
//Build data for Test charts
///
include('buildTestData.php');


////
//Build Country Pie
///
$countryData="['Country', 'Messages']";

$countryPieQuery=mysql_query("select country, count(distinct(emailID)) as count from v_data{$where} group by country order by count desc");
while($country=mysql_fetch_array($countryPieQuery)){
	$countryData.=", 
	['{$country[country]}', {$country[count]}]";
}




////
//Inside the <head>:
///

Print <<< END
<style type="text/css">
	div.chartHolder{
		background: #ffffff url("twirl.gif") no-repeat center center;
		padding: 0;
	}
	select{
		width:100%;
	}
	select[multiple]{
		height: 15em;
	}
</style>
END;

doLayoutHeader();


$tableSpecs="<table style='background:#FFFFFF;border:solid #CCCCCC 1px;padding:1em;margin:1em;width:95%;'>";


////
// Build Sending IP Table
///
$senderQuery=mysql_query("select sendingIP, count(distinct(emailID)) as count from v_data{$where} group by sendingIP order by count desc limit 11");
$sendingIPtable="{$tableSpecs}<tr><td><strong>Sending IP</strong></td><td><strong>Total</strong></td></tr>";
while($sender=mysql_fetch_array($senderQuery)){
	if($grayed=="") $grayed=" style='background-color:#CCCCCC;'"; else $grayed="";
	$sendingIPtable.="
	<tr{$grayed}><td>{$sender[sendingIP]}</td><td>{$sender[count]}</td></tr>";
}
$sendingIPtable.="</table>";
$grayed="";


////
// Build Receiving IP Table
///
$receiverQuery=mysql_query("select receivingIP, count(distinct(emailID)) as count from v_data{$where} group by receivingIP order by count desc limit 11");
$receivingIPtable="{$tableSpecs}<tr><td><strong>Receiving Server</strong></td><td><strong>Total</strong></td></tr>";
while($receiver=mysql_fetch_array($receiverQuery)){
	if($grayed=="") $grayed=" style='background-color:#CCCCCC;'"; else $grayed="";
	$receivingIPtable.="
	<tr{$grayed}><td>{$receiver[receivingIP]}</td><td>{$receiver[count]}</td></tr>";
}
$receivingIPtable.="</table>";



////
//Form Building
///

//All Classes
$classQuery=mysql_query("select id, name from classes order by name asc");
while($class=mysql_fetch_array($classQuery)){
	if(in_array($class[id], $_POST[classes])) $selected=" selected"; else $selected=null;
	$classOptions.="<option value='{$class[id]}'{$selected}>{$class[name]}</option>";
}

//All Tests
$testQuery=mysql_query("select id, name from tests order by name asc");
while($test=mysql_fetch_array($testQuery)){
	if(in_array($test[id], $_POST[tests])) $selected=" selected"; else $selected=null;
	$testOptions.="<option value='{$test[id]}'{$selected}>{$test[name]}</option>";
}

//All Countries
$countryQuery=mysql_query("select id, name from countries order by name asc");
while($country=mysql_fetch_array($countryQuery)){
	if(in_array($country[id], $_POST[tests])) $selected=" selected"; else $selected=null;
	$countryOptions.="<option value='{$country[id]}'>{$country[name]}</option>";
}

//All Senders
$senderQuery=mysql_query("select sendingIP, count(id) as count from securetide group by sendingIP order by count desc limit 20");
while($sender=mysql_fetch_array($senderQuery)){
	if(in_array($sender[sendingIP], $_POST[tests])) $selected=" selected"; else $selected=null;
	$senderOptions.="<option value='{$sender[sendingIP]}'>{$sender[sendingIP]}</option>";
}


Print <<< END
   <section id="filter" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Filter Results</h2>
				<form action="{$_PHP[self]}#classes" method="post">
					<div style="float:left;"><fieldset>
						<legend>Classes</legend>
						<select name="classesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[classes]}>Show Only</option></select><br />
						<select multiple name="classes[]">
							<option value="top">[Top 9]</option>
							{$classOptions}
						</select>
					</fieldset></div>
					
					<div style="float:left;"><fieldset>
						<legend>Tests</legend>
						<select name="testsSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[tests]}>Show Only</option></select><br />
						<select multiple name="tests[]">
							<option value="top">[Top 9]</option>
							{$testOptions}
						</select>
					</fieldset></div>
					
					<div style="float:left;"><fieldset>
						<legend>Countries</legend>
						<select name="countriesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[countries]}>Show Only</option></select><br />
						<select multiple name="countries[]">
							<option value="top">[Top 9]</option>
							{$countryOptions}
						</select>
					</fieldset></div>
					
					<div style="float:left;"><fieldset>
						<legend>Top Senders</legend>
						<select name="senderSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[sender]}>Show Only</option></select><br />
						<select multiple name="sender[]">
							<option value="top">[Top 9]</option>
							{$senderOptions}
						</select>
					</fieldset></div>
					<input type="submit" value="Filter" style="clear:left;display:block;margin:.5em;" /><br />
				</form>
            </div>
        </div>
    </section>
	
	
	
	

   <section id="classes" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-12">
                <h2>Class Data</h2>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#classStack">Stacked by Interval</button>
				<div id="classStack" class="collapse in">
					<form name="clss" id="clss" style="text-align:left;">
						<button type="button" class="btn btn-default btn-lg" id="classButton" />
							<i class='fa fa-align-justify fa-rotate-90'></i> Switch to Percent
						</button>
					</form>
					<div class="chartHolder" id="columnchart_values" style="width:99%;height:450px;"></div>
				</div>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#classTimeline">Class Timeline</button>
				<div id="classTimeline" class="collapse in">
					<div class="chartHolder" id="timeline_div" style="width:99%;height:450px;"></div>
				</div>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#classTotals">Class Totals</button>
				<div id="classTotals" class="collapse in">
					<div class="row">
						<div class="col-lg-4">
							<div class="chartHolder" id="piechart_classes" style="width:100%;height:450px;"></div>
						</div>
						<div class="col-lg-8">
							<div class="chartHolder" id="barchart_classes" style="width:100%;height:450px;"></div>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</section>
	
	
   <section id="tests" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-12">
                <h2>Test Data</h2>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#testStack">Stacked by Interval</button>
				<div id="testStack" class="collapse in">
					<form name="tests" id="tests" style="text-align:left;">
						<button type="button" class="btn btn-default btn-lg" id="testButton" />
							<i class='fa fa-align-justify fa-rotate-90'></i> Switch to Percent
						</button>
					</form>
					<div class="chartHolder" id="testcolumnchart_values" style="width:99%;height:450px;"></div>
				</div>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#testTimeline">Test Timeline</button>
				<div id="testTimeline" class="collapse in">
					<div class="chartHolder" id="testtimeline_div" style="width:99%;height:450px;"></div>
				</div>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#failTotals">Test Fail Totals</button>
				<div id="failTotals" class="collapse in">
					<div class="row">
						<div class="col-lg-4">
							<div class="chartHolder" id="piechart_fails" style="width:100%;height:450px;"></div>
						</div>
						<div class="col-lg-8">
							<div class="chartHolder" id="barchart_fails" style="width:100%;height:450px;"></div>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</section>
	
	
   <section id="location" class="container content-section text-center">
        <div class="row">
			<h2>Location Data</h2>
            <div class="col-lg-3">
				<div class="chartHolder" id="piechart_country" style="clear:left;width:100%;height:450px;"></div>
			</div>
			<div class="col-lg-9">
				<div class="chartHolder" id="map_values" style="width:100%;height:450px;"></div>
			</div>
		</div>
	</section>
	
	
   <section id="servers" class="container content-section text-center">
        <div class="row">
			<h2>Server Data</h2>
            <div class="col-lg-6">
				<div style="width:100%;">{$sendingIPtable}</div>
			</div>
			<div class="col-lg-6">
				<div style="width:100%;">{$receivingIPtable}</div>
			</div>
		</div>
	</section>

	
	
	
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
	
	google.load("visualization", "1", {packages:["corechart", "geomap", "annotatedtimeline"]});
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
		tlTest=new google.visualization.DataTable();
		{$testTimeline}
		
		tlOptions = {
			displayAnnotations: 'false',
			scaleType: 'maximized',
			thickness: 2
		}
	
		
		//Stacked Columns Values
		var row1 = [{$graphHead}, {$tableData}];
		var row2 = [{$percentHead}, {$tableDataPercent}];
		data[0] = google.visualization.arrayToDataTable(row1);
		data[1] = google.visualization.arrayToDataTable(row2);
		
		var testRow1 = [{$testGraphHead}, {$testTableData}];
		var testRow2 = [{$testPercentHead}, {$testTableDataPercent}];
		testData[0] = google.visualization.arrayToDataTable(testRow1);
		testData[1] = google.visualization.arrayToDataTable(testRow2);
		
		
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
		testChart = new google.visualization.ColumnChart(document.getElementById("testcolumnchart_values"));

	
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
		var timeline = new google.visualization.AnnotatedTimeLine(document.getElementById('timeline_div'));
        timeline.draw(tl, tlOptions);
		
		//Create Test Timeline
		var testTimeline = new google.visualization.AnnotatedTimeLine(document.getElementById('testtimeline_div'));
        testTimeline.draw(tlTest, tlOptions);
		
		
		// Create Class Pie and Column Chart
		var classPie = new google.visualization.PieChart(document.getElementById("piechart_classes"));
		classPie.draw(dataClassPie, optionsClassPie);	  
		
		var classColumns = new google.visualization.ColumnChart(document.getElementById("barchart_classes"));
		classColumns.draw(dataClassPie, optionsClassColumn);	

		// Create Test Pie and Column Chart
		var testPie = new google.visualization.PieChart(document.getElementById("piechart_fails"));
		testPie.draw(dataTestPie, optionsClassPie);	  
		
		var classColumns = new google.visualization.ColumnChart(document.getElementById("barchart_fails"));
		classColumns.draw(dataTestPie, optionsClassColumn);			
		
	  
		// Create Country Pie Chart
		var countryPie = new google.visualization.PieChart(document.getElementById("piechart_country"));
        countryPie.draw(dataCountryPie, optionsCountryPie);
	  
	  
		// Create Heat Map
		var heatMap = new google.visualization.GeoMap(document.getElementById("map_values"));
        heatMap.draw(dataHeatMap, optionsHeatMap);
		
		drawColumnChart();
		drawTestColumnChart();
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


dofooter();
?>
