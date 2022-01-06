import json
import requests
from pprint import pprint

with open('cps.apikey', 'r') as file:
    api_key = file.read().rstrip()

url = 'https://account.chargeplacescotland.org/api/v3/poi/chargepoint/static'

headers = {'api-auth': api_key}

x = requests.get(url, headers=headers)
y = json.loads(x.text)
print(y)

static = []
i = 0

for feature in y['features']:
    line = [
        (feature['properties']['name']).strip().encode('utf-8'),
        (feature['properties']['id']).encode('utf-8'),
        (feature['properties']['address']['streetnumber']).encode('utf-8'),
        (feature['properties']['address']['street']).encode('utf-8'),
        (feature['properties']['address']['area']).encode('utf-8'),
        (feature['properties']['address']['city']).encode('utf-8'),
        (feature['properties']['address']['postcode']).encode('utf-8')
    ]
    static.append(line)
# print(json.dumps(static))

