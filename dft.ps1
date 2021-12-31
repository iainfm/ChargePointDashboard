$x = [xml](Invoke-WebRequest 'https://chargepoints.dft.gov.uk/api/retrieve/registry/format/xml/post-town/Dumbarton/')

# foreach ($cd in $x.ChargeDevices.ChargeDevice) {

    $x.ChargeDevices.ChargeDevice | select ChargeDeviceRef, ChargeDeviceName, ChargeDeviceStatus, PublishStatusID | format-table
    $x.ChargeDevices.ChargeDevice.Connector | select connectorid, ChargeMode, ChargePointStatus | format-table

    # $cd.Connector # | select ConnectorID, ChargeMode, ChargePointSTatus

    # foreach ($cn in $cd.Connector) { $status = ($cn.ConnectorId, $cn.ChargeMode, $cn.ChargePointStatus) }
    # foreach ($cn in $cd.Connector) { $status = $cn | select ConnectorId, ChargeMode, ChargePointStatus }
#}
