<?php
//include_once 'filelogger.php';
/**
 * Ein einfacher Logger für JSON-Requests
 * 
 * Die Daten werden per Parameter <code>json</codec> übergeben und können
 * per GET- oder POST-Request kommen. Dieses Script hängt sie momentan
 * einfach nur an eine Textdatei an. Alternativ können sie auch in einer
 * Datenbank abgelegt werden (Spalte <code>timestamp</code> und <code>json</code>).
 * Wird der Parameter <code>debug</code> übergeben,
 * gibt dieses Script als Rückgabewert den <code>var_dump</code> des geparsten
 * Objekts zurück, ansonsten <code>true</code> bei erfolgreichem Logging,
 * <code>false</code> bei ungültigem oder leerem JSON-Code und eine Fehlermeldung
 * in allen anderen Fällen.
 *
 * <p>Optional kann ein zusätzlicher Parameter <code>type</code> übergeben werden,
 * der im Text-Log zwischen Timestamp und JSON verwendet wird. Im DB-Modus wird
 * der type-Wert als Tabellenname verwendet und die einzelnen Felder im JSON-Code
 * direkt auf die Spalten gemappt, dabei das Feld <code>timestamp</code> zusätzlich
 * gesetzt.
 * 
 * <p>Übergabebeispiel aus jQuery: <pre>
 * $.get('/jsonlogger', {json: JSON.stringify($('was auch immer'))});
 * </pre>
 * 
 * @author Alexander Nofftz <nofftz@taktsoft.com>, Jonas Ulrich <ju@taktsoft.com>
 */
 
/***
Für jquery-profile.js und FunMon2.js nutzbare Tabellen:

CREATE TABLE jquery (
	id INT UNSIGNED PRIMARY KEY auto_increment,
	timestamp DATETIME NOT NULL,				-- Zeitpunkt des Loggings
	event VARCHAR(255) NULL,					-- Eventname (z.B. ready)
	duration INT NULL,							-- Laufzeit des Aufrufs in ms
	functionName VARCHAR(255) NULL,				-- Name der Funktion (falls bekannt)
	methodNumber INT UNSIGNED NULL,				-- Fortlaufende Nummerierung der jQuery-Aufrufe
	name VARCHAR(255) NULL,						-- Name der jQuery-Funktion
	args VARCHAR(255) NULL,						-- Übergebene Argumente
	inLength INT UNSIGNED NULL,					-- Eingabegröße (Anzahl Objekte)
	outLength INT UNSIGNED NULL,				-- Ausgabegröße (Anzahl Objekte)
	selector VARCHAR(255) NULL,					-- jQuery-Selektor (siehe Doku)
	context VARCHAR(255) NULL					-- jQuery-Context (siehe Doku)
);

CREATE TABLE funmon (
	id INT UNSIGNED PRIMARY KEY auto_increment,
	timestamp DATETIME NOT NULL,
	functionName VARCHAR(255) NOT NULL,
	callNumber INT UNSIGNED NOT NULL,
	time INT UNSIGNED NULL,
	process INT UNSIGNED NULL,
	trace VARCHAR(255) NULL
);

***/
require('Services/JSON.php');
/** aus dem PHP manual */
function json_code ($json) {  
	$json = preg_replace('/([{,])([\n\t\s]*)([a-zA-Z0-9\s]+)[\n\s\t]*\:/s', '$1"$3":', trim($json));
	return json_decode($json, true);
}

// Speichermethode: true=DB, false=Datei
$storeInDB = true;
// Dateiname
$logFile = '/tmp/json.log';
// Datenbank
$dbName = 'jquery_log';
$dbUser = 'root';
$dbPassword = '';

$json_service = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

$json = $_REQUEST['json'];
//$json = '{"event":"ready","duration":0,"functionName":"readyContent","methodNumber":34,"name":"jQuery","args":"\".draggable_table > tbody > tr > td.first\"","selector":"function () {\n if (!internal && curEvent) {\n internal = true;\n var m = curEvent.methods[curEvent.methods.length] = {name: method, inLength: this.length, args: arguments};\n var start = (new Date).getTime();\n var ret = old.apply(this, arguments);\n m.duration = (new Date).getTime() - start;\n if (FunctionMonitor &&\n FunctionMonitor.currentFunction &&\n FunctionMonitor.currentFunction != null) {\n m.fn = FunctionMonitor.currentFunction;\n }\n if (curEvent.event == \"inline\") {\n curEvent.duration += m.duration;\n }\n if (ret && ret.jquery) {\n  m.outLength = ret.length;\n }\n internal = false;\n return ret;\n } else {\n return old.apply(this, arguments);\n }\n} .draggable_table > tbody > tr > td.first","outLength":0,"context":"[object HTMLDocument]"}';
$jsonParsed = $json_service->decode($json);

if (strlen("$_REQUEST[debug]")) {
	// geparstes Objekt ausgeben
	var_dump($json);
	var_dump($jsonParsed);
	exit;
}

header('Content-Type: application/json');

// Leere und ungültige Anfragen ignorieren
if (!$jsonParsed || !strlen("$json")) {
	echo json_encode(false);
	exit;
}

if ($storeInDB) {
	mysql_connect(null, $dbUser, $dbPassword) or die("db connect error");
	mysql_select_db($dbName) or die("invalid db name");
	$type = $_REQUEST['type'];
	if (strlen("$type")) {
		$sql = 'INSERT INTO `' . $type . '` (';
		$columns = array_keys($jsonParsed);
		$data = array_values($jsonParsed);
		for ($i = 0; $i < sizeof($jsonParsed); $i++) {
			$columns[$i] = '`' . $columns[$i] . '`';
			$data[$i] = "'" . preg_replace("/'/", "\\'", $data[$i]) . "'";
		}
		$columns[] = '`timestamp`';
		$data[] = "'" . date('c'). "'";
		$sql .= implode(',', $columns) . ')VALUES(' . implode(',', $data) . ')';
	} else {
		$json = preg_replace("/'/", "\\'", $json); // SQL injection verhindern
		$sql = "INSERT INTO jsonlog(timestamp,json)VALUES(NOW(),'$json')";
	}
	//echo $sql;
	mysql_query($sql) or die("db query error");
	mysql_close();
} else {
	$log = fopen($logFile, 'a') or die("file open error");
	$data = preg_replace('/\n/', '\\n', $json); // Zeilenumbrüche ersetzen
	if (strlen("$_REQUEST[type]")) $data = "$_REQUEST[type] $data";
	$data = date('c') . ' ' . $data;
	fputs($log, "$data\n") or die("write error");
	fclose($log);
}

echo json_encode(true);
?>