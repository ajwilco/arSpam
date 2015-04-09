<?php
include('include.php');

$pagename="";
doPageOpen();


////
//Build WHERE clauses based on form data
///
/*
print "<pre>";
print_r($_POST);
print "</pre>";
*/
$formOptions=array("classes", "tests", "countries");
$formOption= array("class", "test", "country");
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
//Build data for graph and table.
///
$timeline="tl.addColumn('datetime', 'Time');
";

$graphHead="['Class";
$percentHead="['Class'";
$classPieData="['Class', 'Messages']";

$i=1;
$j=0;
$k=0;

if(!$where){
	$OtherRecords=mysql_num_rows(mysql_query("select id from securetide"));
	$OtherFails=mysql_num_rows(mysql_query("select id from test_fails"));
}

$classQuery=mysql_query("select count(distinct(emailID)) as count, className as name from v_data{$where} group by name order by count desc, name asc limit 9");
while($class=mysql_fetch_array($classQuery)){
	$timeline.="tl.addColumn('number', '{$class[name]}');
";
	$graphHead .= "', '" . $class[name];
	$percentHead.=", '{$class[name]}', { type: 'string', role: 'tooltip', p:{html:'true'}}";
	$classPieData.=",
	['{$class[name]}', {$class[count]}]";
	$classes[$j]=$class[name];
	$table[0][$class[name]]=$class[name];
	$OtherRecords-=$class[count];
	$j++;
}
//Only build "Other" field if data isn't being filtered
if(!$where){
	$timeline.="tl.addColumn('number', 'Other');
tl.addRows([";
	$classPieData.=",
	['Other', $OtherRecords]";
	$graphHead .= "', 'Other']";
	$percentHead.=", 'Other', { type: 'string', role: 'tooltip', p:{html:'true'}}]";
}else{
	$timeline.="tl.addRows([";
	$graphHead.="']";
	$percentHead.="]";
}
$hourlyClassQuery=mysql_query("SELECT COUNT(distinct(emailID)) as count, YEAR(date) as year, MONTH(date) as month,
									  DAY(date) as day, HOUR(date) as hour, className, date,
									  CASE
										WHEN minute(date) BETWEEN 0 and 14 THEN '00'
										WHEN minute(date) BETWEEN 15 and 29 THEN '15'
										WHEN minute(date) BETWEEN 30 and 44 THEN '30'
										WHEN minute(date) BETWEEN 45 and 59 THEN '45'
									  END AS intervals
							   FROM v_data{$where}
							   GROUP BY year, month, day, hour, intervals, className 
							   ORDER BY year asc, month asc, day asc, hour asc, intervals asc, className asc");

while($hourlyClass=mysql_fetch_array($hourlyClassQuery)){
	
	$interval="{$hourlyClass[year]}-{$hourlyClass[month]}-{$hourlyClass[day]} {$hourlyClass[hour]}:{$hourlyClass[intervals]}:00";
	$jsMonth="01";
	if($hourlyClass[hour]<10){
		$jsHour="0".$hourlyClass[hour];
	}else{
		$jsHour=$hourlyClass[hour];
	}
	$jshours[$k]="{$hourlyClass[year]}-{$jsMonth}-{$hourlyClass[day]}T{$jsHour}:{$hourlyClass[intervals]}:00Z";
	$hour=date('m-d g:i A',strtotime($interval));
	
	$lastStamp=$stamp;
	//$stamp=date('Y-m-d H:i', strtotime($interval));
	$stamp=$interval;
	
	if($lastStamp==""){
		$hours[0]=$stamp;
	}elseif($lastStamp!=$stamp){
		$k++;
		$hours[$k]=$stamp;
	}
	if(!in_array($hourlyClass[className], $classes)){
		$other[$k]+=$hourlyClass[count];
	}
	
	$table[$stamp][0]=$hour;
	$table[$stamp][$hourlyClass[className]]=$hourlyClass[count];
	$hourlyTotal[$k]+=$hourlyClass[count];
}
print mysql_error();


$x=0;
$tableData="";
while($x<$k){
	$timeline.="	[new Date(\"{$jshours[$x]}\")";
	$tableData.="['".$table[$hours[$x]][0]."'";
	$tableDataPercent.="['".$table[$hours[$x]][0]."'";
	$y=0;
	while($y<$j){
		if($table[$hours[$x]][$classes[$y]]=="") $table[$hours[$x]][$classes[$y]]=0;
		$timeline.=", {$table[$hours[$x]][$classes[$y]]}";
		$tableData.=", {$table[$hours[$x]][$classes[$y]]}";
		$percent=$table[$hours[$x]][$classes[$y]]*100/$hourlyTotal[$x];
		$tooltip=round($percent, 1);
		$tableDataPercent.=", {$percent}, '<div style=\"padding:1em;\"><strong>{$table[$hours[$x]][0]}</strong><br /><br />{$classes[$y]}: <strong>{$table[$hours[$x]][$classes[$y]]} ({$tooltip}%)</strong></div>'";
		$y++;
	}
	//Only build "Other" field if data isn't being filtered
	if(!$where){
		$timeline.=", ".$other[$x];
		$tableData.=", ".$other[$x];
		$otherPercent=$other[$x]*100/$hourlyTotal[$x];
		$otherTooltip=round($otherPercent, 1);
		$tableDataPercent.=", {$otherPercent}, '<div style=\"padding:1em;\"><strong>{$hours[$x]}</strong><br /><br />Other: <strong>{$otherTooltip}%</strong></div>'";
	}
	$closer="], 
";
	$timeline.=$closer;
	$tableData.=$closer;
	$tableDataPercent.=$closer;
	$x++;
}
$timeline=substr($timeline, 0, -4);
$timeline.="]);";
$tableData=substr($tableData, 0, -4);
$tableDataPercent=substr($tableDataPercent, 0, -4);


////
//Build Country Pie
///
$countryData="['Country', 'Messages']";

$countryPieQuery=mysql_query("select country, count(distinct(emailID)) as count from v_data{$where} group by country order by count desc");
while($country=mysql_fetch_array($countryPieQuery)){
	$countryData.=", 
	['{$country[country]}', {$country[count]}]";
}


/*
////
//Build Tests Pie
///
$testPieData="['Test', 'Messages']";
$testPieQuery=mysql_query("select test, count(failID) as count from v_data{$where} group by test order by count desc limit 14");
while($fail=mysql_fetch_array($testPieQuery)){
	$testPieData.=",
	['{$fail[test]}', {$fail[count]}]";
	$OtherFails-=$fail[count];
}
if(!$where){
	$testPieData.=",
	['Other', {$OtherFails}]";
}

*/
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
	$countryOptions.="<option value='{$country[id]}'>{$country[name]}</option>";
}

//All Senders
$senderQuery=mysql_query("select sendingIP, count(id) as count from securetide group by sendingIP order by count desc limit 20");
while($sender=mysql_fetch_array($senderQuery)){
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
						<select name="classesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[classes]}>Include</option></select><br />
						<select multiple name="classes[]">
							<option value="top">[Top 9]</option>
							{$classOptions}
						</select>
					</fieldset></div>
					
					<div style="float:left;"><fieldset>
						<legend>Tests</legend>
						<select name="classesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[tests]}>Include</option></select><br />
						<select multiple name="classes[]">
							<option value="top">[Top 9]</option>
							{$testOptions}
						</select>
					</fieldset></div>
					
					<div style="float:left;"><fieldset>
						<legend>Countries</legend>
						<select name="countriesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[countries]}>Include</option></select><br />
						<select multiple name="countries[]">
							<option value="top">[Top 9]</option>
							{$countryOptions}
						</select>
					</fieldset></div>
					
					<div style="float:left;"><fieldset>
						<legend>Top Senders</legend>
						<select name="senderSelect"><option value="0">Exclude</option><option value="1">Include</option></select><br />
						<select multiple name="senders[]">
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
					<form style="text-align:left;">
						<button type="button" class="btn btn-default btn-lg" id="b1" />
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
				<h2>Tests Failed Data</h2>
				
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
		tlOptions,
		data=[],
		chart,
		options,
		current=0,
		dataClassPie,
		dataTestPie,
		optionsClassPie,
		optionsClassColumn,
		dataCountryPie,
		optionsCountryPie,
		dataHeatMap,
		optionsHeatMap;
	
	google.load("visualization", "1", {packages:["corechart", "geomap", "annotatedtimeline", "controls"]});
	google.setOnLoadCallback(getVars);
	
	var button = document.getElementById('b1');
	button.onclick = function() {
		current = 1 - current;
		drawColumnChart();
	}
	
	function getVars(){
		//Timeline Values
		tl = new google.visualization.DataTable();
		{$timeline}
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
		options = {
			title: 'Emails by Class over Time',
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
	
		//Create Timeline
		var timeline = new google.visualization.AnnotatedTimeLine(document.getElementById('timeline_div'));
        timeline.draw(tl, tlOptions);
		
		
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
	}
	
	function drawColumnChart() {
		// Disabling the button while the chart is drawing.
		button.disabled = true;
		google.visualization.events.addListener(chart, 'ready',
		function() {
			button.disabled = false;
			button.innerHTML = (current ? "<i class='fa fa-align-right fa-rotate-90'></i>" : "<i class='fa fa-align-justify fa-rotate-90'></i>") + ' Switch to ' + (current ? 'Total' : 'Percent');
		});
		options['title'] = (current ? 'Percent' : 'Total') + ' for each Class in an Interval';
		
		chart.draw(data[current], options);
	}

	  
    </script>
END;


dofooter();
?>
