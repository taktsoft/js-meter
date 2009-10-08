<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Profiling-Report</title>
	<link type="text/css" href="css/jquery-ui-1.7.2.custom.css" rel="stylesheet" />	
	<link type="text/css" href="css/profiler/report.css" rel="stylesheet" />
	<script type="text/javascript" src="javascript/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="javascript/jquery-ui-1.7.2.custom.min.js"></script>
	<script type="text/javascript" src="javascript/profiler/report.js"></script>
</head>
<body>
	<div id="wrap">
		<h1 class="ui-corner-all">Profiling Report</h1>
		
		<div id="profilings_accordion">
			{include file="single_report.tpl" report=$general_profile}
			
			{section name=profile loop=$profiles}
				{include file="single_report.tpl" report=$profiles[profile]}
			{/section}
		</div>
	</div>
</body>
</html>