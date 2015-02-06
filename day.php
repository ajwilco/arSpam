<?php
include('include.php');

$pagename="Hourly Details";
doPageOpen();



////
//Find a useable date.
///
if(isset($_GET[date]))
	$checkDate=mysql_num_rows(mysql_query("SELECT id FROM ar_metadata WHERE period like '{$_GET[date]}%'"));
	
if(($checkDate==0) or (!isset($checkDate))){
		$selectedDayPull=mysql_fetch_array(mysql_query("SELECT period FROM ar_metadata ORDER BY period DESC LIMIT 1"));
		$selectedDay=$selectedDayPull[period];
}else{
		$selectedDay=$_GET[date];
}

$hourlyQuery=mysql_query("SELECT sum(counts0) as counts0,
								 sum(counts1) as counts1,
								 sum(counts2) as counts2,
								 sum(counts3) as counts3,
								 sum(counts4) as counts4,
								 sum(counts5) as counts5,
								 sum(counts6) as counts6,
								 sum(counts7) as counts7,
								 sum(counts8) as counts8,
								 sum(counts9) as counts9,
								 sum(counts10) as counts10, 
								 sum(counts11) as counts11, 
								 sum(counts12) as counts12, 
								 sum(counts13) as counts13, 
								 sum(counts14) as counts14, 
								 sum(counts15) as counts15, 
								 sum(counts16) as counts16, 
								 sum(counts17) as counts17, 
								 sum(counts18) as counts18, 
								 sum(counts19) as counts19, 
								 sum(counts20) as counts20, 
								 sum(counts21) as counts21, 
								 sum(counts22) as counts22, 
								 sum(counts23) as counts23,
								 sum(countsTotal) as countsTotal FROM ar_metadata WHERE period like '{$selectedDay}%' GROUP BY period") or print mysql_error();
$dayRecord=mysql_fetch_array($hourlyQuery);

////
//Build data for graph and table.
///
	$i=0;
	$hour=12;
	$xm=am;
	$tableView="";
	$graphData="";
	while ($i<24){
		$percent=round($dayRecord['counts'.$i]*100/$dayRecord[countsTotal], 2);
		$tableView.="
		<tr>
			<td>{$hour}{$xm}</td>
			<td>{$percent}%</td>
			<td>{$dayRecord['counts'.$i]}</td>
		</tr>";
		
		$graphData.="['{$hour}{$xm}', {$dayRecord['counts'.$i]}]";
		if($i<23)$graphData.=", ";
		
		$i++;
		if($hour==12) $hour=1;
		elseif($hour==11){
			if($xm=="am") $xm="pm"; else $xm="am";
			$hour++;
		}else $hour++;
	}


////
//Inside the <head>:
///
Print <<< END
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load('visualization', '1.0', {'packages':['corechart']});
	google.setOnLoadCallback(drawChart);
	
	function drawChart() {
		// Create the data table.
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Hour');
		data.addColumn('number', 'Hits');
		data.addRows([{$graphData}]);

        // Set chart options
        var options = {'title':'Hourly Hits', 'width':400, 'height':300};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
END;
doLayoutHeader();





Print <<< END
<!--Div that will hold the pie chart-->
    <div id="chart_div" style="float:left;"></div>
	
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
<h3>({$dayRecord[countsTotal]} hits.)</h3>
<table border="1" bordercolor="gray" cellpadding="10" cellspacing="0" width="100%">
	<tr>
		<th>Hour</th>
		<th>Percent</th>
		<th>Total</th>
	</tr>
	{$tableView}
</table>
END;


dofooter();
?>
