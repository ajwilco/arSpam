<?php // Connection and Request stuff

error_reporting(E_ERROR | E_PARSE);
$con=mysql_connect("localhost","arSpam","Lio14lsx!") or die("Failed to connect with database!!!!");
mysql_select_db("arspam", $con);


if($_GET[c]!=""){
	$limit=" and s.id<{$_GET[c]}";
	$limitWhere=" WHERE id<{$_GET[c]}";
}

$classes = array();
$cols = array();
$k=0;                //INTERVAL COUNT
$rows = array();
$table = array();


//Pack up the used classes as columns
$cols[]=array('label' => 'Class', 'type' => 'string');
$classQuery=mysql_query("select c.*, count(s.id) as count from classes c, securetide s where s.class=c.id{$limit} group by s.class order by count desc, name asc limit 9");
while($class = mysql_fetch_assoc($classQuery)) {
	$classes[]=$class[name];
	$cols[]=array('label' => $class[name], 'type' => 'number');
}
$cols[] = array('label' => 'Other', 'type' => 'number');
$table['cols'] = $cols;


//Build arrays of data, to be compiled into a table.
$hourlyClassQuery=mysql_query("SELECT COUNT(id) as count, YEAR(date) as year, MONTH(date) as month,
									  DAY(date) as day, HOUR(date) as hour, className, date,
									  CASE
										WHEN minute(date) BETWEEN 0 and 14 THEN '00'
										WHEN minute(date) BETWEEN 15 and 29 THEN '15'
										WHEN minute(date) BETWEEN 30 and 44 THEN '30'
										WHEN minute(date) BETWEEN 45 and 59 THEN '45'
									  END AS intervals
							   FROM v_securetide{$limitWhere}
							   GROUP BY year, month, day, hour, intervals, className 
							   ORDER BY year asc, month asc, day asc, hour asc, intervals asc, className asc");
while($hourlyClass = mysql_fetch_assoc($hourlyClassQuery)) {
	$interval="{$hourlyClass[year]}-{$hourlyClass[month]}-{$hourlyClass[day]} {$hourlyClass[hour]}:{$hourlyClass[intervals]}:00";
	$hour=date('m-d g:i A',strtotime($interval));  //Visual
	
	$lastStamp=$stamp;
	$stamp=date('Y-m-d H:i', strtotime($interval)); //Code-able
	
	if($lastStamp==""){
		$hours[0]=$stamp;
	}elseif($lastStamp!=$stamp){
		$k++;
		$hours[$k]=$stamp;
	}
	if(!in_array($hourlyClass[className], $classes)){
		$other[$k]+=$hourlyClass[count];
	}
	
	$rawData[$k][0]=$hour;
	$rawData[$k][$hourlyClass[className]]=$hourlyClass[count];
	$hourlyTotal[$k]+=$hourlyClass[count];
}


//Compile some data.
$intervalCount=$k;
$k=0;
while($k<=$intervalCount){
	$temp=array();
	$temp[]=array('v' => (string) $rawData[$k][0]);
	foreach($classes as $class){
		$temp[] = array('v' => (int) $rawData[$k][$class]);
	}
	$temp[] = array('v' => (int) $other[$k]); 
	$rows[] = array('c' => $temp);
	$k++;
}

$table['rows'] = $rows;

/*
Print "<pre>";
print_r($table);
Print "<br /><br />";
*/

$jsonTable = json_encode($table);
  echo $jsonTable;

//Print "</pre>";
  
?>