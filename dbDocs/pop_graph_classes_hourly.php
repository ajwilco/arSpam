<html><body><pre>
<?php
include('../include.php');
ini_set('max_execution_time', 2400);
date_default_timezone_set('UTC'); 
/*
SELECT COUNT(id) as count, YEAR(date) as year, MONTH(date) as month,
									  DAY(date) as day, HOUR(date) as hour, className, date FROM v_securetide 
									  GROUP BY year, month, day, hour, className 
									  ORDER BY year asc, month asc, day asc, hour asc, className asc
*/
$firstRecord=mysql_fetch_array(mysql_query("select date from securetide order by date asc"));
$lastRecord=mysql_fetch_array(mysql_query("select date from securetide order by date desc"));

$hour=strtotime($firstRecord[date]);
$last=strtotime($lastRecord[date]);

$i=0; $j=0; $addQuery="";
$classQuery=mysql_query("select id, name from classes order by id asc");
while($class=mysql_fetch_array($classQuery)){
	$classes[$i]=$class[id];
	$classNames[$i]=$class[name];
	$i++;
}

while($hour<=$last){
	$hourF=date("Y-m-d H:", $hour);
	$classData="";
	while($j<$i){
		$count=mysql_num_rows(mysql_query("select id from securetide where date like '$hourF%' and class='$classes[$j]'"));
		$addQuery.=", $classNames[$j]='$count'";
		$j++;
	}
	print $hourF." -- ".$addQuery."<br />";
	mysql_query("insert into graph_classes_hourly set hour='{$hourF}00:00'$addQuery");
	$j=0; $addQuery="";
	$hour+=3600;
}
?>