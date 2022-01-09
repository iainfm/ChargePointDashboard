#!/usr/bin/env python
import requests

with open('/cps.apikey', 'r') as file:
    api_key = file.read().rstrip()

url = 'https://account.chargeplacescotland.org/api/v3/poi/chargepoint/static'

headers = {'api-auth': api_key}

x = requests.get(url, headers=headers)
body = x.content

if (len(x.content) > 4096):
	outf = open("/var/www/dbc/static.txt", "w")
	outf.write(body)
	outf.close
