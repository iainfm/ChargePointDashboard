$api_key = Get-Content $PSScriptRoot\cps.apikey

$url = 'https://account.chargeplacescotland.org/api/v2/poi/chargepoint/dynamic'

$headers = @{'api-auth' = $api_key}

$collated = ('','','')
$count = 0

$x = Invoke-RestMethod -Uri $url -Headers $headers -Method Get

# $x.chargePoints[0].chargePoint.connectorGroups.connectors.connectorStatus

foreach ($cp in $x.chargePoints.chargePoint) {

    # write-output "CPS Charge Point: $($cp.chargePoint.name)"
    $cpn = $cp.name

    foreach ($cn in $cp.connectorGroups) {

        foreach ($con in $cn.connectors) {
            # $con | format-table
            if (($cpn -eq 50690) -or ($cpn -eq 50691)) {
                write-output "$($cpn) $($con.connectorID) $($con.connectorStatus)"
                }
            # $con.connectorID
            # $con.connectorStatus
        }
        # $cn.connectors | format-table
        #foreach ($cnn in $cp.chargePoint.connectorGroups) {
        #    $cid = $cnn.connectors.connectorID
        #    $cst = $cnn.connectors.connectorstatus
            
        #    if (($cpn -eq 50690) -or ($cpn -eq 50691)) {
        #        write-output "$($cpn, $cid, $cst)"
        #    }

        #}

    }

}