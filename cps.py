import json
import requests

with open('cps.apikey', 'r') as file:
    api_key = file.read().rstrip()

url = 'https://account.chargeplacescotland.org/api/v2/poi/chargepoint/dynamic'

headers = {'api-auth': api_key, 'chargePointIDs': '50690,50691,60238'}

x = requests.get(url, headers=headers)
y = json.loads(x.text)

for item in y['chargePoints']:
    for conn in item['chargePoint']['connectorGroups']:
        for id in conn['connectors']:
            print(item['chargePoint']['name'], id['connectorID'], id['connectorStatus'])

# print (json.dumps(y['chargePoints']))


# z=y['chargePoints'][0]['chargePoint']
# z['name']

# i = item
# i['chargePoint']['connectorGroups'][0]['connectors'][0]['connectorStatus']

# y['chargePoints'][0]['chargePoint']['connectorGroups'][1]['connectors'][0]['connectorStatus']