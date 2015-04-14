<?php

$allSelects=array("classes", "tests", "countries", "sender");

//Find and use saved filter
if($_GET[filter]){
	$filter=mysql_fetch_array(mysql_query("select * from saved_filters where name='{$_GET[filter]}' order by id asc limit 1"));
	foreach($allSelects as $select){
		if($filter[$select]){
			$filterData[$select.'Select']=$filter[$select.'Select'];
			unset($collect);
			$collect=explode(",",$filter[$select]);
			$filterData[$select]=$collect;
		}
	}
	
//Or use new filter data.
}else if($_POST) $filterData=$_POST;


if($filterData){
	//Save the filter, if a name was provided.
	if($filterData[saveName]!=""){
		$_GET[filter]=$filterData[saveName];
		foreach($allSelects as $select){
			if($filterData[$select]){
				if($filterData[$select.'Select']) $saveQuery.=", {$select}Select='1'";
				$collect=implode(",",$filterData[$select]);
				$saveQuery.=", {$select}='{$collect}'";
			}
		}
		if($saveQuery!="") mysql_query("insert into saved_filters set name='{$filterData[saveName]}'{$saveQuery}");
	}


	////
	//Build WHERE clauses based on form data
	///
	$formOptions=array("classes", "countries", "sender");
	$formOption= array("classID", "countryID", "sendingIP");
	foreach ($formOptions as $optionKey => $option){
		if($filterData[$option]){
			if($where=="") $where.=" WHERE "; else $where.=" AND ";
			if(!$filterData[$option.'Select']){
				foreach($filterData[$option] as $key => $item){
					if($key!=0) $where.=" AND ";
					$where.=$formOption[$optionKey]."!='{$item}'";
				}
			}else{
				$includeSelected[$option]=" selected";
				foreach($filterData[$option] as $key => $item){
					if($key==0) $where.="("; else $where.=" OR ";
					$where.=$formOption[$optionKey]."='{$item}'";
				}
				$where.=")";
			}
		}
	}
	
	// Tests get their own loop, because of course they do.
	if($filterData[tests]){
		if($where=="") $where.=" WHERE "; else $where.=" AND ";
		$testWhere.=" AND ";
		if(!$filterData['testsSelect']){
			foreach($filterData[tests] as $key => $item){
				if($key!=0) {$where.=" AND ";$testWhere.=" AND ";}
				$where.="tests not like concat('%','{$item}','%')";
				$testWhere.="name!='{$item}'";
			}
		}else{
			$includeSelected[tests]=" selected";
			foreach($filterData[tests] as $key => $item){
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
}
?>