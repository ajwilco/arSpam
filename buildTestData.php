<?php

$testTimeline="tlTest.addColumn('datetime', 'Time');
";

$testGraphHead="['Test";
$testPercentHead="['Test'";
$testPieData="['Test', 'Messages']";

$j=0; //Test Counter
$k=0; //Interval Counter

if(!$where){
	$testOtherFails=mysql_num_rows(mysql_query("select test_fails.id from test_fails, tests where tests.id=test_fails.test{$testWhere}"));
	$testLimit=" limit 14";
}

$testQuery=mysql_query("select count(emailID) as count, name from v_data join tests on tests like concat('%',name,'%') {$where}{$testWhere} group by name order by count desc, name asc{$testLimit}");
while($test=mysql_fetch_array($testQuery)){
	$testTimeline.="tlTest.addColumn('number', '{$test[name]}');
";
	$testGraphHead .= "', '" . $test[name];
	$testPercentHead.=", '{$test[name]}', { type: 'string', role: 'tooltip', p:{html:'true'}}";
	$testPieData.=",
	['{$test[name]}', {$test[count]}]";
	$tests[$j]=$test[name];
	//$testTable[0][$test[name]]=$test[name];
	$testOtherFails-=$test[count];
	$j++;
}
//Only build "Other" field if data isn't being filtered
if(!$where){
	$testTimeline.="tlTest.addColumn('number', 'Other');
tlTest.addRows([";
	$testPieData.=",
	['Other', $testOtherFails]";
	$testGraphHead .= "', 'Other']";
	$testPercentHead.=", 'Other', { type: 'string', role: 'tooltip', p:{html:'true'}}]";
}else{
	$testTimeline.="tlTest.addRows([";
	$testGraphHead.="']";
	$testPercentHead.="]";
}
$hourlyTestQuery=mysql_query("SELECT COUNT(emailID) as count, YEAR(date) as year, MONTH(date) as month,
									  DAY(date) as day, HOUR(date) as hour, name, date,
									  CASE
										WHEN minute(date) BETWEEN 0 and 14 THEN '00'
										WHEN minute(date) BETWEEN 15 and 29 THEN '15'
										WHEN minute(date) BETWEEN 30 and 44 THEN '30'
										WHEN minute(date) BETWEEN 45 and 59 THEN '45'
									  END AS intervals
							   FROM v_data join tests on tests like concat('%',name,'%') {$where}{$testWhere}
							   GROUP BY year, month, day, hour, intervals, name
							   ORDER BY year asc, month asc, day asc, hour asc, intervals asc, name asc");

while($hourlyTest=mysql_fetch_array($hourlyTestQuery)){
	
	$interval="{$hourlyTest[year]}-{$hourlyTest[month]}-{$hourlyTest[day]} {$hourlyTest[hour]}:{$hourlyTest[intervals]}:00";
	$jsMonth="01";
	if($hourlyTest[hour]<10){
		$jsHour="0".$hourlyTest[hour];
	}else{
		$jsHour=$hourlyTest[hour];
	}
	$jshours[$k]="{$hourlyTest[year]}-{$jsMonth}-{$hourlyTest[day]}T{$jsHour}:{$hourlyTest[intervals]}:00Z";
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
	if(!in_array($hourlyTest[name], $tests)){
		$testOther[$k]+=$hourlyTest[count];
	}
	
	$testTable[$k][0]=$hour;
	$testTable[$k][$hourlyTest[name]]=$hourlyTest[count];
	$testHourlyTotal[$k]+=$hourlyTest[count];
}
print mysql_error();


$intervalCount=$k;
$k=1;
$testTableData="";
while($k<=$intervalCount){
	$testTimeline.="	[new Date(\"{$jshours[$k]}\")";
	$testTableData.="['".$testTable[$k][0]."'";
	$testTableDataPercent.="['".$testTable[$k][0]."'";
	$testCount=$j;
	$j=0;
	while($j<$testCount){
		if($testTable[$k][$tests[$j]]=="") $testTable[$k][$tests[$j]]=0;
		$testTimeline.=", {$testTable[$k][$tests[$j]]}";
		$testTableData.=", {$testTable[$k][$tests[$j]]}";
		$percent=$testTable[$k][$tests[$j]]*100/$testHourlyTotal[$k];
		$tooltip=round($percent, 1);
		$testTableDataPercent.=", {$percent}, '<div style=\"padding:1em;\"><strong>{$testTable[$k][0]}</strong><br /><br />{$tests[$j]}: <strong>{$testTable[$k][$tests[$j]]} ({$tooltip}%)</strong></div>'";
		$j++;
	}
	//Only build "Other" field if data isn't being filtered
	if(!$where){
		$testTimeline.=", ".$testOther[$k];
		$testTableData.=", ".$testOther[$k];
		$testOtherPercent=$testOther[$k]*100/$testHourlyTotal[$k];
		$testOtherTooltip=round($testOtherPercent, 1);
		$testTableDataPercent.=", {$testOtherPercent}, '<div style=\"padding:1em;\"><strong>{$hours[$k]}</strong><br /><br />Other: <strong>{$testOtherTooltip}%</strong></div>'";
	}
	$closer="], 
";
	$testTimeline.=$closer;
	$testTableData.=$closer;
	$testTableDataPercent.=$closer;
	$k++;
}
if($k!=1) {
	$testTimeline=substr($testTimeline, 0, -4);
	$testTimeline.="]);";
	$testTableData=substr($testTableData, 0, -4);
	$testTableDataPercent=substr($testTableDataPercent, 0, -4);
}else{
	$testTimeline=null;
	$testTableData=null;
	$testTableDataPercent=null;
	$testGraphHead=null;
	$testPercentHead=null;
	$testPieData=null;
}

?>