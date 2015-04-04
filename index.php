<?php
include('include.php');

$pagename="";
doPageOpen();

<<<<<<< HEAD

////
//Build WHERE clauses based on form data
///
/*
print "<pre>";
print_r($_POST);
print "</pre>";
*/
$formOptions=array("classes", "countries");
$formOption= array("class",   "country");
foreach ($formOptions as $optionKey => $option){
	if($_POST[$option]){
		if($where=="") $where.=" WHERE "; else $where.=" AND ";
		if(!$_POST[$option.'Select']){
			foreach($_POST[$option] as $key => $item){
				if($key!=0) $where.=" AND ";
				$where.=$formOption[$optionKey]."ID!='{$item}'";
			}
		}else{
			$classSelected=" selected";
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

$classQuery=mysql_query("select count(id) as count, className as name from v_securetide{$where} group by className order by count desc, className asc limit 9");
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
	$OtherRecords=mysql_num_rows(mysql_query("select id from v_securetide"));
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
=======
//Country Heatmap Query::   select c.*, count(s.id) as count from classes c, securetide s where c.id=s.class group by s.class order by c.name asc


////
//Build data for Message totals/percentages, and Class piechart.
///

$graphHead="['Class";
$classPieData="['Class', 'Messages']";
$i=1;
$j=0;
$k=0;

$classQuery=mysql_query("select c.*, count(s.id) as count from classes c, securetide s where c.id=s.class group by s.class order by count desc, c.name asc");
while($class=mysql_fetch_array($classQuery)){
	$graphHead .= "', '" . $class[name];
	$classPieData.=",
	['{$class[name]}', {$class[count]}]";
	$classes[$j]=$class[name];
	$table[0][$class[name]]=$class[name];
	$j++;
}
$graphHead .= "', { role: 'annotation' }]";

$hourlyClassQuery=mysql_query("SELECT COUNT(id) as count, YEAR(date) as year, MONTH(date) as month,
									  DAY(date) as day, HOUR(date) as hour, className, date FROM v_securetide 
									  GROUP BY year, month, day, hour, className 
									  ORDER BY year asc, month asc, day asc, hour asc, className asc");
while($hourlyClass=mysql_fetch_array($hourlyClassQuery)){

	$hour=date('m-d gA',strtotime($hourlyClass[date]));
	$lastStamp=$stamp;
	$stamp=date('Y-m-d H', strtotime($hourlyClass[date]));
	if($lastStamp!=$stamp){
		$hours[$k]=$stamp;
		$hourlyTotal[$k]=mysql_num_rows(mysql_query("select id from v_securetide where date like '$stamp%'"));
		$k++;
	}
	$table[$stamp][0]=$hour;
	$table[$stamp][$hourlyClass[className]]=$hourlyClass[count];
}

$x=0;
$tableData="";
while($x<$k){
	$tableData.="['".$table[$hours[$x]][0]."', ";
	$tableDataPercent.="['".$table[$hours[$x]][0]."', ";
	$y=0;
	while($y<$j){
	
		$tableData.="{$table[$hours[$x]][$classes[$y]]}, ";
		$percent=$table[$hours[$x]][$classes[$y]]*100/$hourlyTotal[$x];
		$tableDataPercent.="{$percent}, ";
		$y++;
	}
	$tableData.="''], 
";
	$tableDataPercent.="''], 
";
	$x++;
}
$tableData=substr($tableData, 0, -4);
$tableDataPercent=substr($tableDataPercent, 0, -4);




////
//Build Country Pie
///
$countryPieData="['Country', 'Messages']";

$countryPieQuery=mysql_query("select country, count(id) as count from v_securetide group by country order by count desc");
while($country=mysql_fetch_array($countryPieQuery)){
	$countryPieData.=", 
	['{$country[country]}', {$country[count]}]";
}



////
//Build Heat Map
///
$heatMapData="['Country', 'Messages']";

$heatQuery=mysql_query("select c.name, count(m.id) as count 
						  from countries c, securetide m 
						 where c.ISO!='' and m.country=c.id 
					  group by m.country order by count desc");
while($country=mysql_fetch_array($heatQuery)){
	$heatMapData.=", 
	['{$country[name]}', {$country[count]}]";
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
}
$hourlyClassQuery=mysql_query("SELECT COUNT(id) as count, YEAR(date) as year, MONTH(date) as month,
									  DAY(date) as day, HOUR(date) as hour, className, date,
									  CASE
										WHEN minute(date) BETWEEN 0 and 14 THEN '00'
										WHEN minute(date) BETWEEN 15 and 29 THEN '15'
										WHEN minute(date) BETWEEN 30 and 44 THEN '30'
										WHEN minute(date) BETWEEN 45 and 59 THEN '45'
									  END AS intervals
							   FROM v_securetide{$where}
							   GROUP BY year, month, day, hour, intervals, className 
							   ORDER BY year asc, month asc, day asc, hour asc, intervals asc, className asc");

<<<<<<< HEAD
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

$countryPieQuery=mysql_query("select id, country, count(id) as count from v_securetide{$where} group by country order by count desc");
while($country=mysql_fetch_array($countryPieQuery)){
	$countryData.=", 
	['{$country[country]}', {$country[count]}]";
}

=======
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d


////
//Inside the <head>:
///

Print <<< END
<style type="text/css">
<<<<<<< HEAD
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
=======
div{
	background: #ffffff url("twirl.gif") no-repeat center center;
}
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
</style>
END;

doLayoutHeader();


$tableSpecs="<table style='background:#FFFFFF;border:solid #CCCCCC 1px;padding:1em;margin:1em;width:95%;'>";


////
// Build Sending IP Table
///
<<<<<<< HEAD
$senderQuery=mysql_query("select sendingIP, count(id) as count from v_securetide{$where} group by sendingIP order by count desc limit 11");
=======
$senderQuery=mysql_query("select sendingIP, count(id) as count from securetide group by sendingIP order by count desc limit 11");
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
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
<<<<<<< HEAD
$receiverQuery=mysql_query("select receivingIP, count(id) as count from v_securetide{$where} group by receivingIP order by count desc limit 11");
=======
$receiverQuery=mysql_query("select receivingIP, count(id) as count from v_securetide group by receivingIP order by count desc limit 11");
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
$receivingIPtable="{$tableSpecs}<tr><td><strong>Receiving Server</strong></td><td><strong>Total</strong></td></tr>";
while($receiver=mysql_fetch_array($receiverQuery)){
	if($grayed=="") $grayed=" style='background-color:#CCCCCC;'"; else $grayed="";
	$receivingIPtable.="
	<tr{$grayed}><td>{$receiver[receivingIP]}</td><td>{$receiver[count]}</td></tr>";
}
$receivingIPtable.="</table>";



<<<<<<< HEAD
////
//Form Building
///

//All Classes
$classQuery=mysql_query("select id, name from classes order by name asc");
while($class=mysql_fetch_array($classQuery)){
	if(in_array($class[id], $_POST[classes])) $selected=" selected"; else $selected=null;
	$classOptions.="<option value='{$class[id]}'{$selected}>{$class[name]}</option>";
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
	<div style="width:99%;">
		<form action="{$_PHP[self]}" method="post">
			<div style="float:left;"><fieldset>
				<legend>Classes</legend>
				<select name="classSelect"><option value="0">Exclude</option><option value="1"{$classSelected}>Include</option></select><br />
				<select multiple name="classes[]">
					<option value="top">[Top 9]</option>
					{$classOptions}
				</select>
			</fieldset></div>
			
			<div style="float:left;"><fieldset>
				<legend>Countries</legend>
				<select name="countrySelect"><option value="0">Exclude</option><option value="1">Include</option></select><br />
				<select multiple name="countries[]">
					<option value="top">[Top 9]</option>
					{$countryOptions}
				</select>
			</fieldset></div>
			
			<div style="float:left;"><fieldset>
				<legend>Sender</legend>
				<select name="senderSelect"><option value="0">Exclude</option><option value="1">Include</option></select><br />
				<select multiple name="senders[]">
					<option value="top">[Top 9]</option>
					{$senderOptions}
				</select>
			</fieldset></div>
			<input type="submit" value="Filter" style="clear:left;display:block;margin:.5em;" /><br />
		</form>
	</div>

	<div class="chartHolder" id="timeline_div" style="width:99%;height:450px;"></div><br /><br />
	<form><input type="button" id="b1" value="Show Percentages" /></form>
	<div class="chartHolder" id="columnchart_values" style="width:99%;height:450px;"></div>
	<div style="width:33%;float:left;min-width:400px;">{$sendingIPtable}</div>
	<div style="width:33%;float:left;min-width:400px;">{$receivingIPtable}</div>
	<div class="chartHolder" id="piechart_classes" style="width:33%;height:325px;float:left;"></div>
	<div class="chartHolder" id="piechart_country" style="clear:left;width:33%;height:450px;float:left;"></div>
	<div class="chartHolder" id="map_values" style="width:66%;height:450px;float:left;"></div>
=======
Print <<< END
    <div id="columnchart_values" style="width:99%;height:450px;"></div>
	<div id="columnchart_valuesPercent" style="width:99%;height:450px;"></div>
	<div style="width:33%;float:left;min-width:400px;">{$sendingIPtable}</div>
	<div style="width:33%;float:left;min-width:400px;">{$receivingIPtable}</div>
	<div id="piechart_classes" style="width:33%;height:325px;float:left;"></div>
	<div id="piechart_country" style="clear:left;width:33%;height:450px;float:left;"></div>
	<div id="map_values" style="width:66%;height:450px;float:left;"></div>
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d

	
	
	
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
<<<<<<< HEAD
	google.load("visualization", "1", {packages:["corechart", "geomap", "annotatedtimeline", "controls"]});
	google.setOnLoadCallback(getVars);
	
	var tl;
	var tlOptions;
	var data=[];
	var chart;
	var options;
	var current=0;
	var dataClassPie;
	var optionsClassPie;
	var dataCountryPie;
	var optionsCountryPie;
	var dataHeatMap;
	var optionsHeatMap;
	
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

	
		//Class Pie Chart Data
		dataClassPie = google.visualization.arrayToDataTable([
			$classPieData
		]);
		optionsClassPie = {
=======
	google.load("visualization", "1", {packages:["corechart", "geomap"]});
	google.setOnLoadCallback(drawChart);
	
	function drawChart() {
		// Create Hourly Graph
		
		var data = google.visualization.arrayToDataTable([
			{$graphHead},
			$tableData
		]);
		
		var options = {
			title: 'Total Hourly Emails by Class',
			height: 450,
			chartArea: { left: 50, width: '100%' },
            legend: { position: 'top', maxLines: 10 },
			bar: { groupWidth: '75%' },
			hAxis: { slantedText: 'true' },
			isStacked: true,
			sliceVisibilityThreshold: 1
		};

        var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));

        chart.draw(data, options);
		
		
		
		
		// Create Percents Graph
		
		var dataPercent = google.visualization.arrayToDataTable([
			{$graphHead},
			$tableDataPercent
		]);
		
		var optionsPercent = {
			title: 'Percent Hourly Emails by Class',
			height: 450,
			chartArea: { left: 50, width: '100%' },
            legend: { position: 'top', maxLines: 10 },
			bar: { groupWidth: '75%' },
			hAxis: { slantedText: 'true' },
			isStacked: true,
			sliceVisibilityThreshold: 1
		};

        var percentChart = new google.visualization.ColumnChart(document.getElementById("columnchart_valuesPercent"));

        percentChart.draw(dataPercent, optionsPercent);
	  
	  
	  
		// Create Class Pie Chart
		
		var dataClassPie = google.visualization.arrayToDataTable([
			$classPieData
		]);
		
		var optionsClassPie = {
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
			title: 'Messages per Class',
			height: 325,
			chartArea: { top: 20, width: '100%', height: '100%' },
			legend: 'none',
			sliceVisibilityThreshold: 1/100
		};

<<<<<<< HEAD
	
		//Country Pie Data
		dataCountryPie = google.visualization.arrayToDataTable([
			$countryData
		]);
		optionsCountryPie = {
=======
        var classPie = new google.visualization.PieChart(document.getElementById("piechart_classes"));

        classPie.draw(dataClassPie, optionsClassPie);	  
	  
	  
	  
	  
		// Create Country Pie Chart
		
		var dataCountryPie = google.visualization.arrayToDataTable([
			$countryPieData
		]);
		
		var optionsCountryPie = {
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
			title: 'Messages per Country',
			height: 450,
			chartArea: { top: 20, width: '100%', height: '100%' },
			legend: 'none',
			sliceVisibilityThreshold: 1/100
		};

<<<<<<< HEAD
	
		//Heatmap Values
		dataHeatMap = google.visualization.arrayToDataTable([
			$countryData
		]);
		optionsHeatMap = {
=======
        var countryPie = new google.visualization.PieChart(document.getElementById("piechart_country"));

        countryPie.draw(dataCountryPie, optionsCountryPie);	  
	  
	  
	  
	  
		// Create Heat Map
		
		var dataHeatMap = google.visualization.arrayToDataTable([
			$heatMapData
		]);
		
		var optionsHeatMap = {
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
			title: 'Messages by Country',
			height: 450,
			width: '100%',
			dataMode: 'regions',
			colors: [0xD69999, 0x990000]
		};
<<<<<<< HEAD
		drawChart();
	}
	
	
	function drawChart() {
	
		//Create Timeline
		var timeline = new google.visualization.AnnotatedTimeLine(document.getElementById('timeline_div'));
        timeline.draw(tl, tlOptions);
		
		
		// Create Class Pie Chart
		var classPie = new google.visualization.PieChart(document.getElementById("piechart_classes"));
        classPie.draw(dataClassPie, optionsClassPie);	  
	  
	  
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
			button.value = 'Switch to ' + (current ? 'Total' : 'Percent');
		});
		options['title'] = (current ? 'Percent' : 'Total') + ' for each Class in an Interval';
		
		chart.draw(data[current], options);
	}

=======

        var heatMap = new google.visualization.GeoMap(document.getElementById("map_values"));

        heatMap.draw(dataHeatMap, optionsHeatMap);
		
		
	}
>>>>>>> 8bdeb4b1fde63b0ba78992614dd61f91cd6adf2d
	  
    </script>
END;


dofooter();
?>
