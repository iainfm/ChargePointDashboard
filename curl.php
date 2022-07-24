<html>
<head>
<title>ChargePoint Dashboard</title>

<?php
$cssdate = date('Ymd');
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"/cpdash.css?ver=" . $cssdate . "\">";

# Read the API key from secure file
$apifile = './dbc/cps.apikey';
$f = fopen($apifile, "r") or die("Unable to open file!");
$apikey = fread($f, filesize($apifile)-1);
fclose($f);

# Get static chargepoint information
$f = file_get_contents('./dbc/static.txt');
$static = json_decode($f, false);
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

$results = array();
$lines = array();
$conplug = array();
$conspeed = array();
$contype = array();
$conptn = array();

$i = 0;
foreach ($y->chargePoints as $item) {
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

$current = '';
$last = '1';
foreach ($results as $result) {

	$etd = '';

	if ($result[2] < $last) {
		print "<tr><td colspan=100%></td></tr>";
	}
	$last = $result[2];

	if ($result[0] != $current) {

		$current = $result[0];
		print "<TR>\n";
		print "<TD colspan='100%' class='address'>\n";
		print '<A HREF="https://chargeplacescotland.org/cpmap/chargepoint/';
		print $result[1];
		print '">';
		print $result[0];
		print '</A><br>';

		foreach ($static->features as $cp) {
			if ($cp->properties->name == $result[0]) {
				foreach ($cp->properties->connectorGroups as $cg) {
					foreach ($cg->connectors as $conn) {
					$conplug[$conn->connectorID] = $conn->connectorPlugType;
					$conspeed[$conn->connectorID] = $conn->connectorMaxChargeRate;
					$contype[$conn->connectorID] = $conn->connectorType;
					$conptn[$conn->connectorID] = $conn->connectorPlugTypeName;
					}
				}
				foreach ($cp->properties->address as $ad) {
					if (($ad != '') && ($ad != 'GB')) {
						print $ad;
						print '<br>';
					}	
				}
				$fee = $cp->properties->tariff->amount;
				if ($fee != '') {
					print '£';
					print $fee;
					print " / kWh";
				}
			}
		}
		print "</TD>\n";
		print "</TR>\n";
	}
	if ($result[2] == '1') {
		print "</TR>\n";
	}
	print "<TD style='width:0.1%'>" . $result[2] . "</TD>";
	print "<TD style='width:0.1%'>";
	print "<img src ='" . $conplug[$result[2]] . ".png'";
	print " style='width:70px;height:70px;'</img>";
	print "</TD>";
	print "<TD style='width:0.1%'>";
	print $conspeed[$result[2]] . "kW&nbsp;";
	print $contype[$result[2]];
	print "</TD>";
	$cs = $id->connectorStatus;
	
	switch ($result[3]) {
		case 'OCCUPIED':
			$etd = 'occupied';
			break;
		case 'UNKNOWN':
			$etd = 'unknown';
			break;
		case 'AVAILABLE':
			$etd = 'available';
			break;
		default:
			$etd = '';
	}
	
	print "<TD class='" . $etd . "'>" . $result[3] . "</TD><TR>\n";
	if ($result[2]  === (count($result) - 1)) {
		print "<tr><td colspan=100%>***</td></tr>";
	}
}
print "</TABLE>\n";
?>

<h2>
<center>
<p>
Share this:
</p><p>
<?php
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
$rawurl = rawurlencode( $url );
$fburl = 'https://www.facebook.com/sharer.php?u=' . $rawurl;
$twurl = 'https://twitter.com/intent/tweet?url=' . $rawurl . '&text=Here are my chargers';
echo '<a href="' . $twurl . '">';
echo '<img src="twitter.png" style="width:80px;height:80px;"></u>';
echo '</a>';
echo '&nbsp;';
echo '<a href="' . $fburl . '">';
echo '<img src="facebook.png" style="width:80px;height:80px;"></u>';
echo '<a href="' . $escaped_url . '">';
echo '&nbsp;';
echo '<img src="link.png" style="width:80px;height:80px;"></u>';
echo '</a>';
?>
</p>
<hr>
<p>
<h3>
Disclaimer: This is an unofficial service that is not affiliated with ChargePlace Scotland. Data is supplied without any warranty and may be incorrect or out of date. Additional connection, parking, charging or other fees may apply.</p>
For official information please visit
<br><a href="https://www.chargeplacescotland.org"</a><u>ChargePlace Scotland</u>.</p></center>
</h3>
</body>
</html>
