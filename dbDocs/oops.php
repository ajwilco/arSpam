<html><body><pre>
<?php
include('../include.php');
ini_set('max_execution_time', 600);
$query=mysql_query("select * from receivingip");
while($country=mysql_fetch_array($query)){
	mysql_query("update securetide set receivingip='$country[id]' where receiving='$country[ip]'");
}
print mysql_error();
?>
</pre></body></html>