<?php

$timeline="tl.addColumn('datetime', 'Time');
";

$graphHead="['Class";
$percentHead="['Class'";
$classPieData="['Class', 'Messages']";

$i=1;
$j=0;
$k=0;

if(!$where){
	$otherRecords=mysql_num_rows(mysql_query("select id from securetide"));
	$classLimit=" limit 9";
}

$classQuery=mysql_query("select count(distinct(emailID)) as count, className as name from v_data{$where} group by name order by count desc, name asc {$classLimit}");
while($class=mysql_fetch_array($classQuery)){
	$timeline.="tl.addColumn('number', '{$class[name]}');
";
	$graphHead .= "', '" . $class[name];
	$percentHead.=", '{$class[name]}', { type: 'string', role: 'tooltip', p:{html:'true'}}";
	$classPieData.=",
	['{$class[name]}', {$class[count]}]";
	$classes[$j]=$class[name];
	$table[0][$class[name]]=$class[name];
	$otherRecords-=$class[count];
	$j++;
}
//Only build "Other" field if data isn't being filtered
if(!$where){
	$timeline.="tl.addColumn('number', 'Other');
tl.addRows([";
	$classPieData.=",
	['Other', $otherRecords]";
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

?>