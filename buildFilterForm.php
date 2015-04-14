<?php

//Saved Filters List
$savedFilterList="<a href='{$M}#classes' style='color:#111;background-color:#EEE;padding:5px;width:100%;display:block;'>No Filters</a>";
$savedQuery=mysql_query("select name from saved_filters order by name asc");
while($saved=mysql_fetch_array($savedQuery)){
	if($_GET[filter]==$saved[name]) $bgColor="9098DE"; else if($flip==1) $bgColor="EEE"; else $bgColor="CCC";
	$savedFilterList.="<a href='{$M}?filter={$saved[name]}#classes' style='color:#111;background-color:#{$bgColor};padding:5px;width:100%;text-align:left;display:block;'>{$saved[name]}</a>";
	$flip=1-$flip;
}
$bgColor="";



//All Classes
$classQuery=mysql_query("select id, name from classes order by name asc");
while($class=mysql_fetch_array($classQuery)){
	if(in_array($class[id], $filterData[classes])) $selected=" selected"; else $selected=null;
	$classOptions.="<option value='{$class[id]}'{$selected}>{$class[name]}</option>";
}

//All Tests
$testQuery=mysql_query("select id, name from tests order by name asc");
while($test=mysql_fetch_array($testQuery)){
	if(in_array($test[name], $filterData[tests])) $selected=" selected"; else $selected=null;
	$testOptions.="<option value='{$test[name]}'{$selected}>{$test[name]}</option>";
}

//All Countries
$countryQuery=mysql_query("select id, name from countries order by name asc");
while($country=mysql_fetch_array($countryQuery)){
	if(in_array($country[id], $filterData[countries])) $selected=" selected"; else $selected=null;
	$countryOptions.="<option value='{$country[id]}'{$selected}>{$country[name]}</option>";
}

//All Senders
$senderQuery=mysql_query("select sendingIP, count(id) as count from securetide group by sendingIP order by count desc limit 20");
while($sender=mysql_fetch_array($senderQuery)){
	if(in_array($sender['sendingIP'], $filterData['sender'])) $selected=" selected"; else $selected=null;
	$senderOptions.="<option value=\"{$sender['sendingIP']}\"{$selected}>{$sender['sendingIP']}</option>";
}


Print <<< END
   <section id="filter" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Filter Results</h2>
			</div>
		</div>
		<form action="{$M}#classes" method="post">
			<div class="row">
				<div class="col-lg-2 col-lg-offset-1" style="padding-right:0;padding-left:0;">
					<h4>Saved Filters</h4>
					{$savedFilterList}
				</div>
				<div class="col-lg-2">
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
				<div class="col-lg-2 col-lg-offset-5"><br />
					Name this filter to save it: <br />
					<input type="text" name="saveName" /><br /><br />
					<button type="submit" class="btn btn-default" style="background:rgb(96, 108, 208);color:black;">Filter</button>
				</div>
			</div>
		</form>
    </section>
END;

?>