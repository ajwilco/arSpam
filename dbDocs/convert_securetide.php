<html><body><pre>
<?php
include('../include.php');
date_default_timezone_set('UTC'); 

$fp = fopen('logs.txt', 'r');

while ( (!feof($fp)) && ($i<1000) ){
$x++;
	$line = fgets($fp, 2048);
	$data = str_getcsv($line,"\t");
	$timestamp=date("Y-m-d H:i:s", strtotime($data[0]));
	$j=0;
	
	do{
		$thisRow = mysql_fetch_array(mysql_query("select * from ar_securetide where date='$timestamp'      and messageClass='$data[1]' 
																				and message='$data[2]'     and sendingIP='$data[3]' 
																				and receivingIP='$data[4]' and country='$data[5]'"));
																				
		if($thisRow[id]=="") mysql_query("insert into ar_securetide set date='$timestamp',       messageClass='$data[1]',
																		message='$data[2]',      sendingIP='$data[3]',
																		receivingIP='$data[4]',  country='$data[5]'");
		$j++;
	}while(($thisRow[id]=="") && ($j<2));
	
	//This should kill repeating data..  There were no IDs in the log, so hopefully there's no legitimate duplicates..
	if($j==1) CONTINUE;
	//$i++;
	
	// I honestly just prefer referential tables of foreign keys over ENUM columns..  Fear of huge databases I guess?
	$tests=str_getcsv($data[6],",");
	foreach ($tests as $test) {
		if($test!=""){
			do{
				print $test;
				$findTest=mysql_fetch_array(mysql_query("select id from ar_tests where name='$test'")) or print mysql_error();
				if($findTest[id]=="") mysql_query("insert into ar_tests set name='{$test}'") or print mysql_error();
			}while($findTest[id]=="");
			mysql_query("insert into ar_test_fails set test='$findTest[id]', record='$thisRow[id]'");
		}
	}
/*	
    print_r($thisRow);
	$failQuery=mysql_query("SELECT t.name FROM ar_tests AS t, 
											   ar_test_fails AS f WHERE f.record='$thisRow[id]' 
																	AND f.test=t.id ORDER BY t.name ASC");
	Print "<ul>";
	while($fails=mysql_fetch_array($failQuery)){
		print "<li>";
		print_r($fails);
		print "</li>";
	}
	Print "</ul>";
*/
}                              

Print "<br /><br />Up to ".$x;
fclose($fp);
?>
</pre></body></html>