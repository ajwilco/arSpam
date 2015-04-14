<?php
include('include.php');

$pagename="";
doPageOpen();


////
//Build WHERE clauses based on form data
///
$formOptions=array("classes", "countries", "sender");
$formOption= array("classID", "countryID", "sendingIP");
foreach ($formOptions as $optionKey => $option){
	if($_POST[$option]){
		if($where=="") $where.=" WHERE "; else $where.=" AND ";
		if(!$_POST[$option.'Select']){
			foreach($_POST[$option] as $key => $item){
				if($key!=0) $where.=" AND ";
				$where.=$formOption[$optionKey]."!='{$item}'";
			}
		}else{
			$includeSelected[$option]=" selected";
			foreach($_POST[$option] as $key => $item){
				if($key==0) $where.="("; else $where.=" OR ";
				$where.=$formOption[$optionKey]."='{$item}'";
			}
			$where.=")";
		}
	}
}
// Tests get their own loop, because of course they do.
if($_POST[tests]){
		if($where=="") $where.=" WHERE "; else $where.=" AND ";
		$testWhere.=" AND ";
		if(!$_POST['testsSelect']){
			foreach($_POST[tests] as $key => $item){
				if($key!=0) {$where.=" AND ";$testWhere.=" AND ";}
				$where.="tests not like concat('%','{$item}','%')";
				$testWhere.="name!='{$item}'";
			}
		}else{
			$includeSelected[tests]=" selected";
			foreach($_POST[tests] as $key => $item){
				if($key==0) {
					$where.="(";
					$testWhere.="(";
				} else {
					$where.=" OR ";
					$testWhere.=" OR ";
				}
				$where.="tests like concat('%','{$item}','%')";
				$testWhere.="name='{$item}'";
			}
			$where.=")";
			$testWhere.=")";
		}
	}

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
//Form Building
///

//All Classes
$classQuery=mysql_query("select id, name from classes order by name asc");
while($class=mysql_fetch_array($classQuery)){
	if(in_array($class[id], $_POST[classes])) $selected=" selected"; else $selected=null;
	$classOptions.="<option value='{$class[id]}'{$selected}>{$class[name]}</option>";
}

//All Tests
$testQuery=mysql_query("select id, name from tests order by name asc");
while($test=mysql_fetch_array($testQuery)){
	if(in_array($test[name], $_POST[tests])) $selected=" selected"; else $selected=null;
	$testOptions.="<option value='{$test[name]}'{$selected}>{$test[name]}</option>";
}

//All Countries
$countryQuery=mysql_query("select id, name from countries order by name asc");
while($country=mysql_fetch_array($countryQuery)){
	if(in_array($country[id], $_POST[countries])) $selected=" selected"; else $selected=null;
	$countryOptions.="<option value='{$country[id]}'{$selected}>{$country[name]}</option>";
}

//All Senders
$senderQuery=mysql_query("select sendingIP, count(id) as count from securetide group by sendingIP order by count desc limit 20");
while($sender=mysql_fetch_array($senderQuery)){
	if(in_array($sender['sendingIP'], $_POST['sender'])) $selected=" selected"; else $selected=null;
	$senderOptions.="<option value=\"{$sender['sendingIP']}\"{$selected}>{$sender['sendingIP']}</option>";
}


Print <<< END
   <section id="filter" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Filter Results</h2>
			</div>
		</div>
		<form action="{$_PHP[self]}#classes" method="post">
			<div class="row">
				<div class="col-lg-2 col-lg-offset-2">
					<h4>Classes</h4>
					<select name="classesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[classes]}>Show Only</option></select><br />
					<select multiple name="classes[]">
						<option value="top">[Top 9]</option>
						{$classOptions}
					</select>
				</div>
				<div class="col-lg-2">
					<h4>Tests</h4>
					<select name="testsSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[tests]}>Show Only</option></select><br />
					<select multiple name="tests[]">
						<option value="top">[Top 9]</option>
						{$testOptions}
					</select>
				</div>
				<div class="col-lg-2">
					<h4>Countries</h4>
					<select name="countriesSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[countries]}>Show Only</option></select><br />
					<select multiple name="countries[]">
						<option value="top">[Top 9]</option>
						{$countryOptions}
					</select>
				</div>
				<div class="col-lg-2">	
					<h4>Top Senders</h4>
					<select name="senderSelect"><option value="0">Exclude</option><option value="1"{$includeSelected[sender]}>Show Only</option></select><br />
					<select multiple name="sender[]">
						<option value="top">[Top 9]</option>
						{$senderOptions}
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-2 col-lg-offset-5"><br /><br />
					<button type="submit" class="btn btn-default" style="background:rgb(96, 108, 208);color:black;">Filter</button>
				</div>
			</div>
		</form>
    </section>
	
	
	
	

   <section id="classes" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-12">
                <h2>Class Data</h2>
				
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
