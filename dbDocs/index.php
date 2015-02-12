<?php
include('include.php');

$pagename="";
doPageOpen();


////
//Build data for graph and table.
///
$graphHead="['Class";
$graphData="";
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

	$hour=date('g A <b\r /> m-d',strtotime($hourlyClass[date]));
	$stamp=date('Y-m-d H', strtotime($hourlyClass[date]));
	$hours[$k]=$stamp;
	$table[$stamp][0]=$hour;
	$table[$stamp][$hourlyClass[className]]=$hourlyClass[count];
	$k++;
}
$x=0;
$y=0;
$tableData="";
while($x<$k){
	$tableData.="['{$hours[$x]}', ";
	while($y<$j){
		$tableData.="'{$table[$hours[$x]][$classes[$y]]}', ";
		$y++;
	}
	$tableData.="], ";
	$x++;
}



Print "<pre>";
Print $tabledata."<br /><br />";
print_r($table);
print "</pre>";
////
//Inside the <head>:
///
/*
Print <<< END
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("visualization", "1.1", {packages:["bar"]});
	google.setOnLoadCallback(drawHourlyHits);
	
	function drawHourlyHits() {
		// Create the data table.
		
		var data = google.visualization.arrayToDataTable([
          ['Hour', 'Hits'],
		  {$graphData}
        ]);
		
		var options = {
          chart: {
            title: 'Hits by the Hour',
            subtitle: '',
          },
          bars: 'horizontal' // Required for Material Bar Charts.
        };

        var chart = new google.charts.Bar(document.getElementById('barchart_material'));

        chart.draw(data, options);
      }
    </script>
END;
doLayoutHeader();





Print <<< END
<!--Div that will hold the pie chart-->
    <div id="barchart_material" style="float:left;"></div>
	
<div style="text-align:center;padding:1em;">
	<form action="{$_PHP[self]}" method="get">
		Date to View: <select name="date">
END;
			$dayQuery=mysql_query("SELECT period FROM ar_metadata GROUP BY period ORDER BY period DESC");
			while($day=mysql_fetch_array($dayQuery)){
				$trimmedDate=substr($day[period], 0, 10);
				if($trimmedDate==$selectedDay) $selected=" selected"; else $selected=null;
				Print "<option value='{$trimmedDate}'{$selected}>{$trimmedDate}</option>";
			}
Print <<< END
		</select>
		<input type="submit" value="Show" />
	</form>
</div>
<h3>({$hourlyClass[countsTotal]} hits.)</h3>
<table border="1" bordercolor="gray" cellpadding="10" cellspacing="0" width="100%">
	<tr>
		<th>Hour</th>
		<th>Percent</th>
		<th>Total</th>
	</tr>
	{$tableView}
</table>
END;

*/
dofooter();
?>
