<html>
<head>
<title>ChargePoint Dashboard</title>

<?php
$cssdate = date('Ymd');
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"/cpdash.css?ver=" . $cssdate . "\">";

$apifile = '/var/www/dbc/cps.apikey';
$f = fopen($apifile, "r") or die("Unable to open file!");
$apikey = fread($f, filesize($apifile)-1);
fclose($f);

?>

</head>

<body>
<?php

function sortByID($a, $b) {
	return $a[0] - $b[0];
}

function sortByConn($a, $b) {
	return $a[2] - $b[2];
}

$cpIDs = str_replace('%20', '', $_GET["ids"]);
$cpIDs = str_replace(' ', '', $cpIDs);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://account.chargeplacescotland.org/api/v2/poi/chargepoint/dynamic/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = [
	'api-auth: ' . $apikey,
	'chargePointIDs: ' . $cpIDs
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$x = curl_exec ($ch);
curl_close ($ch);

$y = json_decode($x);

print "<TABLE>\n";
print "<TH>CP id</TH><TH>Socket</TH><TH>Status</TH>\n";

$results = array();
$lines = array();

$i = 0;
foreach ($y->chargePoints as $item) {
	# var_dump($cp) ;
	foreach ($item->chargePoint->connectorGroups as $conn) {
		foreach ($conn->connectors as $id)
			$lines[0] = $item->chargePoint->name;
			$lines[1] = $item->chargePoint->id;
			$lines[2] = $id->connectorID;
			$lines[3] = $id->connectorStatus;
			$results[$i] = $lines;
			$i++;
		}
	}

usort($results, 'sortByConn');
usort($results, 'sortByID');

foreach ($results as $result) {
	$etd = '';
	print "<TR>\n";
	print "<TD>";
	print '<A HREF="https://chargeplacescotland.org/cpmap/chargepoint/';
	print $result[1];
	print '">';
	print $result[0];
	print '</A>';
	print "</TD>";
	print "<TD>" . $result[2] . "</TD>";
	$cs = $id->connectorStatus;
	
	switch ($result[3]) {
		case 'OCCUPIED':
			$etd = '<font color = "orange">';
			break;
		case 'UNKNOWN':
			$etd = '<font color = "grey">';
			break;
		case 'AVAILABLE':
			$etd = '<font color = "lime">';
			break;
		default:
			$etd = '<font>';
	}
	
	print "<TD>" . $etd . $result[3] . "</font></TD>\n";
	print "</TR>\n";
}
print "</TABLE>\n";
?>

</body>
</html>
