<?php
include('include.php');

$pagename="";
doPageOpen();

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
}



////
//Inside the <head>:
///

Print <<< END
<style type="text/css">
div{
	background: #ffffff url("twirl.gif") no-repeat center center;
}
</style>
END;

doLayoutHeader();


$tableSpecs="<table style='background:#FFFFFF;border:solid #CCCCCC 1px;padding:1em;margin:1em;width:95%;'>";


////
// Build Sending IP Table
///
$senderQuery=mysql_query("select sendingIP, count(id) as count from securetide group by sendingIP order by count desc limit 11");
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
$receiverQuery=mysql_query("select receivingIP, count(id) as count from v_securetide group by receivingIP order by count desc limit 11");
$receivingIPtable="{$tableSpecs}<tr><td><strong>Receiving Server</strong></td><td><strong>Total</strong></td></tr>";
while($receiver=mysql_fetch_array($receiverQuery)){
	if($grayed=="") $grayed=" style='background-color:#CCCCCC;'"; else $grayed="";
	$receivingIPtable.="
	<tr{$grayed}><td>{$receiver[receivingIP]}</td><td>{$receiver[count]}</td></tr>";
}
$receivingIPtable.="</table>";



Print <<< END
    <div id="columnchart_values" style="width:99%;height:450px;"></div>
	<div id="columnchart_valuesPercent" style="width:99%;height:450px;"></div>
	<div style="width:33%;float:left;min-width:400px;">{$sendingIPtable}</div>
	<div style="width:33%;float:left;min-width:400px;">{$receivingIPtable}</div>
	<div id="piechart_classes" style="width:33%;height:325px;float:left;"></div>
	<div id="piechart_country" style="clear:left;width:33%;height:450px;float:left;"></div>
	<div id="map_values" style="width:66%;height:450px;float:left;"></div>

	
	
	
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
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
			title: 'Messages per Class',
			height: 325,
			chartArea: { top: 20, width: '100%', height: '100%' },
			legend: 'none',
			sliceVisibilityThreshold: 1/100
		};

        var classPie = new google.visualization.PieChart(document.getElementById("piechart_classes"));

        classPie.draw(dataClassPie, optionsClassPie);	  
	  
	  
	  
	  
		// Create Country Pie Chart
		
		var dataCountryPie = google.visualization.arrayToDataTable([
			$countryPieData
		]);
		
		var optionsCountryPie = {
			title: 'Messages per Country',
			height: 450,
			chartArea: { top: 20, width: '100%', height: '100%' },
			legend: 'none',
			sliceVisibilityThreshold: 1/100
		};

        var countryPie = new google.visualization.PieChart(document.getElementById("piechart_country"));

        countryPie.draw(dataCountryPie, optionsCountryPie);	  
	  
	  
	  
	  
		// Create Heat Map
		
		var dataHeatMap = google.visualization.arrayToDataTable([
			$heatMapData
		]);
		
		var optionsHeatMap = {
			title: 'Messages by Country',
			height: 450,
			width: '100%',
			dataMode: 'regions',
			colors: [0xD69999, 0x990000]
		};

        var heatMap = new google.visualization.GeoMap(document.getElementById("map_values"));

        heatMap.draw(dataHeatMap, optionsHeatMap);
		
		
	}
	  
    </script>
END;


dofooter();
?>
