<?php
include('include.php');

$pagename="";
doPageOpen();


////
//Interpret Filter Forms
///
include('interpretFilters.php');


////
//Build data for Class charts
///
include('buildClassData.php');


////
//Build data for Test charts
///
include('buildTestData.php');


////
//Build Country Pie
///
$countryData="['Country', 'Messages']";

$countryPieQuery=mysql_query("select country, count(distinct(emailID)) as count from v_data{$where} group by country order by count desc");
while($country=mysql_fetch_array($countryPieQuery)){
	$countryData.=", 
	['{$country[country]}', {$country[count]}]";
}




////
//Inside the <head>:
///

Print <<< END
<style type="text/css">
	div.chartHolder{
		background: #ffffff url("twirl.gif") no-repeat center center;
		padding: 0;
	}
	select{
		width:100%;
	}
	select[multiple]{
		height: 15em;
	}
</style>
END;

doLayoutHeader();


$tableSpecs="<table style='background:#FFFFFF;border:solid #CCCCCC 1px;padding:1em;margin:1em;width:95%;'>";


////
// Build Sending IP Table
///
$senderQuery=mysql_query("select sendingIP, count(distinct(emailID)) as count from v_data{$where} group by sendingIP order by count desc limit 11");
$sendingIPtable="{$tableSpecs}<tr><td><strong>Sending IP</strong></td><td><strong>Total</strong></td></tr>";
while($sender=mysql_fetch_array($senderQuery)){
	if($grayed=="") $grayed=" style='background-color:#CCCCCC;'"; else $grayed="";
	$sendingIPtable.="
	<tr{$grayed}><td>{$sender[sendingIP]}</td><td>{$sender[count]}</td></tr>";
}
$sendingIPtable.="</table>";
$grayed="";


////
// Build Receiving IP Table
///
$receiverQuery=mysql_query("select receivingIP, count(distinct(emailID)) as count from v_data{$where} group by receivingIP order by count desc limit 11");
$receivingIPtable="{$tableSpecs}<tr><td><strong>Receiving Server</strong></td><td><strong>Total</strong></td></tr>";
while($receiver=mysql_fetch_array($receiverQuery)){
	if($grayed=="") $grayed=" style='background-color:#CCCCCC;'"; else $grayed="";
	$receivingIPtable.="
	<tr{$grayed}><td>{$receiver[receivingIP]}</td><td>{$receiver[count]}</td></tr>";
}
$receivingIPtable.="</table>";



////
//Build Filter Form
///
include('buildFilterForm.php');
	

////
//Information Display Sections
///
Print <<< END
   <section id="classes" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-12">
                <h2>Class Data</h2>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#classTotals">Class Totals</button>
				<div id="classTotals" class="collapse in">
					<div class="row">
						<div class="col-lg-4">
							<div class="chartHolder" id="piechart_classes" style="width:100%;height:450px;"></div>
						</div>
						<div class="col-lg-8">
							<div class="chartHolder" id="barchart_classes" style="width:100%;height:450px;"></div>
						</div>
					</div>
				</div>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#classStack">Stacked by Interval</button>
				<div id="classStack" class="collapse in">
					<form name="clss" id="clss" style="text-align:left;">
						<button type="button" class="btn btn-default btn-lg" id="classButton" />
							<i class='fa fa-align-justify fa-rotate-90'></i> Switch to Percent
						</button>
					</form>
					<div class="chartHolder" id="columnchart_values" style="width:99%;height:450px;"></div>
				</div>
				
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#classTimeline">Class Timeline</button>
				<div id="classTimeline" class="collapse in">
					<div class="chartHolder" id="timeline_div" style="width:99%;height:450px;"></div>
				</div>
				
			</div>
		</div>
	</section>
	
	
   <section id="tests" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-12">
                <h2>Test Data</h2>
				
END;
				if($testGraphHead==""){
					Print "<h4>No tests failed.</h4>";
				}else{
Print <<< END
					<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#failTotals">Test Fail Totals</button>
					<div id="failTotals" class="collapse in">
						<div class="row">
							<div class="col-lg-4">
								<div class="chartHolder" id="piechart_fails" style="width:100%;height:450px;"></div>
							</div>
							<div class="col-lg-8">
								<div class="chartHolder" id="barchart_fails" style="width:100%;height:450px;"></div>
							</div>
						</div>
					</div>
					
					<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#testStack">Stacked by Interval</button>
					<div id="testStack" class="collapse in">
						<form name="tests" id="tests" style="text-align:left;">
							<button type="button" class="btn btn-default btn-lg" id="testButton" />
								<i class='fa fa-align-justify fa-rotate-90'></i> Switch to Percent
							</button>
						</form>
						<div class="chartHolder" id="testcolumnchart_values" style="width:99%;height:450px;"></div>
					</div>
					
					<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#testTimeline">Test Timeline</button>
					<div id="testTimeline" class="collapse in">
						<div class="chartHolder" id="testtimeline_div" style="width:99%;height:450px;"></div>
					</div>
END;
				}
Print <<< END
			</div>
		</div>
	</section>
	
	
   <section id="location" class="container content-section text-center">
        <div class="row">
			<h2>Location Data</h2>
            <div class="col-lg-3">
				<div class="chartHolder" id="piechart_country" style="clear:left;width:100%;height:450px;"></div>
			</div>
			<div class="col-lg-9">
				<div class="chartHolder" id="map_values" style="width:100%;height:450px;"></div>
			</div>
		</div>
	</section>
	
	
   <section id="servers" class="container content-section text-center">
        <div class="row">
			<h2>Server Data</h2>
            <div class="col-lg-6">
				<div style="width:100%;">{$sendingIPtable}</div>
			</div>
			<div class="col-lg-6">
				<div style="width:100%;">{$receivingIPtable}</div>
			</div>
		</div>
	</section>
END;

////
// Load Charts through a LOT of Javascript
///
include('loadChartJS.php');


dofooter();
?>
