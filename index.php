<?php
include('include.php');

$pagename="";
doPageOpen();
doLayoutHeader();

$total=mysql_fetch_array(mysql_query("SELECT sum(countsTotal) as sum from ar_metadata"));

if(is_numeric($_GET[minHits])) $minHits=$_GET[minHits]; else $minHits=100;
	
if($_GET[everything]){
	$everythingForm="<input type='hidden' name='everything' value='true' />";
	$tableHead="<th>Name</th><th>Period</th><th>Key</th><th>Total</th>";
	$query=mysql_query("SELECT name, period, domainKey, countsTotal FROM ar_metadata WHERE countsTotal>=$minHits ORDER BY countsTotal DESC") or print mysql_error();
}else{
	$tableHead="<th>Percent</th><th>Key</th><th>Total</th>";
	$query=mysql_query("SELECT domainKey, sum(countsTotal) AS countsTotal FROM ar_metadata GROUP BY domainKey ORDER BY countsTotal DESC") or print mysql_error();
}

$totalRows=mysql_num_rows($query);

Print <<< END
<div style="text-align:center;padding:1em;">
	<a href="{$M}">By Server</a> | 
	<a href="{$M}?everything=true">All Data</a><br /><br />
	<form action="{$_PHP[self]}" method="get">
		Minimum Hits from Server: <input type="text" name="minHits" value="{$minHits}" />
		{$everythingForm}
		<input type="submit" value="Show" />
	</form>
</div>
<h3>({$totalRows} rows.)</h3>
<table border="1" bordercolor="gray" cellpadding="10" cellspacing="0" width="100%">
	<tr>
		{$tableHead}
	</tr>
END;

	while(($domain=mysql_fetch_array($query)) && $domain[countsTotal]>=$minHits){
		if($_GET[everything]){
			$tableData="<td>{$domain[name]}</td><td>{$domain[period]}</td><td>{$domain[domainKey]}</td><td>{$domain[countsTotal]}</td>";
		}else{
			$percent=round($domain[countsTotal]*100/$total[sum], 2);
			$tableData="<td>{$percent}%</td><td>{$domain[domainKey]}</td><td>{$domain[countsTotal]}</td>";
		}
		
PRINT <<< END
		<tr>
			{$tableData}
		</tr>
END;
	}
Print <<< END
</table>
END;


dofooter();
?>
