<html>

<!-- ChargePoint Dashboard main php file
     Gets requested charging station IDs, joins them to static
     data and presents them to the user in a convenient format
-->

<head>
<title>ChargePoint Dashboard</title>

<?php

# Randomize the css URL for testing / cache bypass
$cssdate = date('Ymd');
$cssdate = rand();
print "<link rel=\"stylesheet\" type=\"text/css\" href=\"/cpdash.css?ver=" . $cssdate . "\">";

# Read the API key from the secure file - see ReadMe.MD for how to obtain it.
$apifile = './dbc/cps.apikey';
$f = fopen($apifile, "r") or die("Unable to open file!");
$apikey = fread($f, filesize($apifile)-1);
fclose($f);

# Get static chargepoint information - this is downloaded via a cron job as it doesn't change often
$f = file_get_contents('./dbc/static.txt');
$static = json_decode($f, false);
?>

</head>

<body>
<?php

# Get the charge station IDs requested and put them into the format we need
$cpIDs = str_replace('%20', '', $_GET["ids"]);
$cpIDs = str_replace(' ', '', $cpIDs);

# Grab the charge status information using CPS's API
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

# Decode the JSON object returned
$y = json_decode($x);

# Commence outputting the data
# There are probably a million better ways to do this
# Suggestions on a postcard (PR) please...

print "<TABLE>\n";

# Create some empty arrays for what we'll need later
$results = array();	# The charger info
$lines = array();	# Charger name, id, etc
$conplug = array();	# Connector plug names
$conspeed = array();	# Charging speed capability
$contype = array();	# Connector type
$conptn = array();	# Connector Plug Type Name (not currently used)

# Initialise index
$i = 0;

# Iterate over the charge points returned
foreach ($y->chargePoints as $item) {

	# Iterate over the connector groups returned
	foreach ($item->chargePoint->connectorGroups as $conn) {

		# Iterate over the connectors returned
		foreach ($conn->connectors as $id)
			$lines[0] = $item->chargePoint->name;
			$lines[1] = $item->chargePoint->id;
			$lines[2] = $id->connectorID;
			$lines[3] = $id->connectorStatus;
			$results[$i] = $lines;
			$i++;
		}
	}

# Sort the results the way we like 'em
sort($results); # , 'sortByConn');
# sort($results, 'sortByID');

# A bit of a fudge to tell when we're at a new charger ID and
# the last connector of a particular charger
$current = '';
$last = '1';

# Iterate over the results array we've built
foreach ($results as $result) {

	$etd = '';

	# Output the charge point static info the first time each is encountered
	if ($result[2] < $last) {
		print "<tr><td colspan=100%></td></tr>";
	}
	$last = $result[2];

	if ($result[0] != $current) {

		# ChargePoint ID with hyperlink
		$current = $result[0];
		print "<TR>\n";
		print "<TD colspan='100%' class='address'>\n";
		print '<A HREF="https://chargeplacescotland.org/cpmap/chargepoint/';
		print $result[1];
		print '">';
		print $result[0];
		print '</A><br>';

		# Build the connector types, speeds and other info
		foreach ($static->features as $cp) {
			if ($cp->properties->name == $result[0]) {
				$concoords[$conn->connectorID] = $cp->geometry->coordinates;
				# print $concoords[''][0] . ', ' . $concoords[''][1] . '<br>';
				foreach ($cp->properties->connectorGroups as $cg) {
					foreach ($cg->connectors as $conn) {
					$conplug[$conn->connectorID] = $conn->connectorPlugType;
					$conspeed[$conn->connectorID] = $conn->connectorMaxChargeRate;
					$contype[$conn->connectorID] = $conn->connectorType;
					$conptn[$conn->connectorID] = $conn->connectorPlugTypeName;
					}
				}

				# Print the useful parts of the address
				foreach ($cp->properties->address as $ad) {
					if (($ad != '') && ($ad != 'GB')) {
						print $ad;
						print '<br>';
					}	
				}

				# Add the price/kWh if non-zero
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

	# Add a bit of white space
	if ($result[2] == '1') {
		print "</TR>\n";
	}

	# Output the connector numbers, type (png) and speeds
	print "<TD style='width:0.1%'>" . $result[2] . "</TD>";
	print "<TD style='width:0.1%'>";
	print "<img src ='" . $conplug[$result[2]] . ".png'";
	print " style='width:70px;height:70px;'</img>";
	print "</TD>";
	print "<TD style='width:0.1%'>";
	print $conspeed[$result[2]] . "kW&nbsp;";
	print $contype[$result[2]];
	print "</TD>";

	# Get the connector status
	$cs = $id->connectorStatus;
	
	# Choose the css style based on the connector status
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
	
	# Output the connector status, appropriately styled
	print "<TD class='" . $etd . "'>" . $result[3] . "</TD><TR>\n";
}

# All done!
print "</TABLE>\n";
?>

<!-- Add some sharing links and disclaimer -->

<h2>
<center>
<p>
Share this:
</p><p>
<?php

# Convert the URL for sharing link purposes
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
$rawurl = rawurlencode( $url );
$fburl = 'https://www.facebook.com/sharer.php?u=' . $rawurl;
$twurl = 'https://twitter.com/intent/tweet?url=' . $rawurl . '&text=Here are my chargers';
print '<a href="' . $twurl . '">';
print '<img src="twitter.png" style="width:80px;height:80px;"></u>';
print '</a>';
print '&nbsp;';
print '<a href="' . $fburl . '">';
print '<img src="facebook.png" style="width:80px;height:80px;"></u>';
print '<a href="' . $escaped_url . '">';
print '&nbsp;';
print '<img src="link.png" style="width:80px;height:80px;"></u>';
print '</a>';
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
