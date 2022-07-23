#!/usr/bin/env bash
apikey=$(</var/www/dbc/cps.apikey)
curl -H "api-auth: $apikey" --url https://account.chargeplacescotland.org/api/v3/poi/chargepoint/static > static.txt
