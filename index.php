<?php
include('include.php');

$pagename="";
doPageOpen();


////
//Build data for graph and table.
///

//$hourlyClassQuery=mysql_query("select * from graph_classes_hourly order by hour asc, class asc"


$graphHead="['Class";
$i=1;
$j=0;
$k=0;

$classQuery=mysql_query("select * from classes order by name asc");
while($class=mysql_fetch_array($classQuery)){
	$graphHead .= "', '" . $class[name];
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


/*
Print "<pre>";
Print $graphHead."<br /><br />";

Print $tableData."<br /><br />";
print_r($table);
print "</pre>";
*/

////
//Inside the <head>:
///

Print <<< END
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	
	function drawChart() {
		// Create the data table.
		
		var data = google.visualization.arrayToDataTable([
			{$graphHead},
			{$tableData}
		]);
		
		var options = {
			title: 'Total Hourly Emails by Class',
			height: 500,
			chartArea: { left: 50, width: '100%' },
            legend: { position: 'top', maxLines: 10 },
			bar: { groupWidth: '75%' },
			hAxis: { slantedText: 'true' },
			isStacked: true,
		};

        var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));

        chart.draw(data, options);
		
		
		
		
		// Create Percents Graph
		
		var dataPercent = google.visualization.arrayToDataTable([
			{$graphHead},
			{$tableDataPercent}
		]);
		
		var optionsPercent = {
			title: 'Percent Hourly Emails by Class',
			height: 500,
			chartArea: { left: 50, width: '100%' },
            legend: { position: 'top', maxLines: 10 },
			bar: { groupWidth: '75%' },
			hAxis: { slantedText: 'true' },
			isStacked: true,
		};

        var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_valuesPercent"));

        chart.draw(dataPercent, optionsPercent);
      }
    </script>
END;
doLayoutHeader();





Print <<< END
<!--Div that will hold the pie chart-->
    <div id="columnchart_values" style="width:99%;text-align:center;"></div>
	<div id="columnchart_valuesPercent" style="width:99%;text-align:center;"></div>
END;


dofooter();
?>
