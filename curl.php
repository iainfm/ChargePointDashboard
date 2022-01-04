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

foreach ($y->chargePoints as $item) {
	# var_dump($cp) ;
	foreach ($item->chargePoint->connectorGroups as $conn) {
		foreach ($conn->connectors as $id)
			$etd = '';
			print "<TR>\n";
			print "<TD>" . $item->chargePoint->name . "</TD>";
			print "<TD>" . $id->connectorID . "</TD>";
			
			$cs = $id->connectorStatus;
			
			switch ($cs) {
				case 'OCCUPIED':
					$etd = '<font color = "orange">';
					break;
				case 'UNKNOWN':
					$etd = '<font color = "grey">';
					break;
				default:
					$etd = '<font>';
			}
			
			print "<TD>" . $etd . $cs . "</font></TD>\n";
			print "</TR>\n";
	}
}

print "</TABLE>\n";

?>

</body>
</html>