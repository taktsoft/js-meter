<?php
	$smarty_dir = "D:/xampp/smarty";
	$jsprofiler_dir	= $smarty_dir . "/jsprofiler";
	$jsprofiler_docroot = "D:/xampp/htdocs/jsprofiler";
	
	include_once($jsprofiler_docroot . "/db.php");
	include_once($jsprofiler_docroot . "/util.php");

	require($smarty_dir . "/libs/Smarty.class.php");
	$smarty = new Smarty();
	
	$smarty->config_dir = $jsprofiler_dir . "/configs";	
	$smarty->template_dir = $jsprofiler_dir . "/templates";
	$smarty->compile_dir = $jsprofiler_dir . "/templates_c";
	$smarty->cache_dir = $jsprofiler_dir . "/cache";
	
	generateSingleReports($smarty);
	generateOverallReport($smarty);
	
	$smarty->display('reports.tpl');

/**
 * 
 */
function generateOverallReport(&$smarty) {
	$labels = array();
	$data = array();	
	$whereIdRange = "1";
	$aggregatedRuntime = aggregatedRuntime($whereIdRange);
	
	$generalProfile = array(
		'id' => 0,
		'title' => "Overall",
		'aggregated_runtime' => $aggregatedRuntime,
		'functions' => getFunctions($whereIdRange, $aggregatedRuntime, $labels, $data),
		'queries' => getQueries($whereIdRange, $aggregatedRuntime),
		'chart' => array(
			'labels' => implode("|", $labels),
			'data' => implode(",", $data)
		)
	);
				
	$smarty->assign('general_profile', $generalProfile);
}

function generateSingleReports(&$smarty) {
	global $db;
	$lastTimestamp = 0;
	$profile_ids = array();
	$profileDates = array();
	$profiles = array();
	
	$qry = $db->query('SELECT UNIX_TIMESTAMP(timestamp) timestamp, MIN(id) id FROM jquery GROUP BY (timestamp) ORDER BY timestamp');

	foreach ($qry as $profile) {
		// länger als eine Sekunde zum letzten Timestamp?
		$timestamp = intval($profile['timestamp']);
		if (($timestamp - $lastTimestamp) > 1) {
			// neuer Profile
			$profile_ids[] = $profile['id'];
			$profileDates[$profile['id']] = date('Y-m-d H:i:s', $timestamp);
		}
		$lastTimestamp = $timestamp;
	}
	
	foreach ($profile_ids as $pindex => $id) {
		$nextId = ($pindex == count($profile_ids) - 1) ? sprintf("%u", -1) : $profile_ids[$pindex + 1];
		$whereIdRange = "($id <= id) AND (id < $nextId)";
		$aggregatedRuntime = aggregatedRuntime($whereIdRange);
		$labels = array(); $data = array();
		
		array_push($profiles, array(
			'id' => $id,
			'title' => "Profiling " . $profileDates[$id],
			'aggregated_runtime' => $aggregatedRuntime,
			'functions' => getFunctions($whereIdRange, $aggregatedRuntime, $labels, $data),
			'queries' => getQueries($whereIdRange, $aggregatedRuntime),
			'chart' => array(
				'labels' => implode("|", $labels),
				'data' => implode(",", $data)
			)
		));
		
		$smarty->assign('profiles', $profiles);
	}
}	
	
/**
 * Gesamtlaufzeit ermitteln
 */
function aggregatedRuntime($whereIdRange) {
	global $db;
	$duration = (int) $db->query("SELECT SUM(duration) FROM jquery WHERE $whereIdRange")->fetchColumn();
	return $duration;
}

/**
 * 
 */
function getFunctions($whereIdRange, $aggregatedRuntime, &$labels, &$data) {
	global $db;
	$functionsArray = array();		

	$functions = $db->query("SELECT id,event,functionName,SUM(duration) duration FROM jquery WHERE $whereIdRange GROUP BY functionName");

	foreach ($functions as $f) {
		$functionDuration = intval($f['duration']);
		$functionDetails = getFunctionDetails($whereIdRange, $f['functionName'], $functionDuration);
		
		array_push($functionsArray, array(
			'event' => $f['event'],
			'functionName' => $f['functionName'],
			'runtime' => "$f[duration]",
			'percent' => percent($functionDuration, $aggregatedRuntime),
			'id' => $f['id'],
			'details' => $functionDetails
		));
		
		$labels[]=$f['functionName'];
		$data[]=$functionDuration;
	}
	
	return $functionsArray;
}

/**
 * 
 */
function getFunctionDetails($whereIdRange, $functionName, $functionDuration) {	
	global $db;
	$functionDetailsArray = array();
	
	$functionDetails = $db->query("SELECT SUM(duration) duration,name,args,selector,context FROM jquery WHERE $whereIdRange AND functionName='$functionName' GROUP BY methodNumber");

	foreach($functionDetails as $d) {
		array_push($functionDetailsArray, array(
			'query' => $d['name'],
			'args' => shorten($d['args']),
			'selector' => shorten($d['selector']),
			'context' => shorten($d['context']),
			'runtime' => "$d[duration]",
			'percent' => percent($d['duration'], $functionDuration)
		));
	}
	
	return $functionDetailsArray;
}

/**
 * 
 */
function getQueries($whereIdRange, $aggregatedRuntime) {
	global $db;
	$queriesArray = array();

	$queries = $db->query("SELECT id,event,methodNumber,functionName,name,args,SUM(duration) duration,selector,context,inLength,outLength FROM jquery WHERE $whereIdRange GROUP BY event, functionName, name, args, context");

	foreach($queries as $q) {
		$methodDuration = intval($q['duration']);
		if($functionName) {
			$functionName = "(functionName='" . $q['functionName']."')";
		} else {
			$functionName = "1";
		}
		
		$queryDetails = getQueryDetails($whereIdRange, $q['methodNumber'], $functionName, $methodDuration);

		array_push($queriesArray, array(
			'event' => $q['event'],
			'functionName' => shorten($q['functionName']),
			'query' => $q['name'],
			'args' => shorten($q['args']),
			'methodDuration' => $methodDuration,
			'percent' => percent($methodDuration, $aggregatedRuntime),
			'selector' => shorten($q['selector']),
			'context' => $q['context'],
			'inLength' => $q['inLength'],
			'outLength' => $q['outLength'],
			'id' => $q['id'],
			'details' => $queryDetails
		));
	}
	
	return $queriesArray;	
}

function getQueryDetails($whereIdRange, $methodNumber, $functionName, $methodDuration) {
	global $db;
	$queryDetailsArray = array();

	$queryDetails = $db->query("SELECT id,name,args,SUM(duration) duration FROM jquery WHERE $whereIdRange AND (methodNumber=$methodNumber) AND $functionName GROUP BY name, args");
	
	foreach($queryDetails as $d) {
		array_push($queryDetailsArray, array(
				'query' => shorten($d['name']),
				'args' => shorten($d['args'],100),
				'methodDuration' => intval($d['duration']),
				'percent' => percent($d['duration'], $methodDuration)
		));
	}
	
	return $queryDetailsArray;
}


?>