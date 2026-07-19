# Address Searches

## Address Searches


### Search for NBN Location IDs

**URI:** `/locations{?apiaryAddressSearch}`

Address matching is not case sensitive.

If multiple results are found, they will be ordered from closest to furthest match, with the closest being in position 0 of the array.

If no results are found, you will still receive an HTTP200 code but the responseData array will be empty.
The only exception is the reverse location ID lookup, where you'll receive an HTTP404 error instead.

Please note that in the sandbox, you can only search for locations provided in the test data. Locations in the real world will not resolve to sandbox LOC IDs.

Required scope: read:locations


#### `POST` POST - Unstructured address search

**URI:** `/locations{?apiaryUnstructuredAddressSearch}`

Please note that unstructured searches are unreliable in NBN's staging environment, but work as expected in production.

Unstructured address search patterns:

``<unitTypeCode> <unitNumber> <roadNumber1>-<roadNumber2> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>

<unitTypeCode> <unitNumber> <roadNumber1> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>

<unitTypeCode> <unitNumber> <roadNumber1> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode>

<unitTypeCode> <unitNumber> <roadNumber1>-<roadNumber2> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode>

<roadNumber1>-<roadNumber2> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>

<roadNumber1> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>
``


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "unstructured": {
    "address": "546 FLINDERS ST MELBOURNE 3000",
    "fuzzy": false
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "2",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 2 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 2 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "3",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 3 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 3 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


#### `POST` POST - Structured address search

**URI:** `/locations{?apiaryStructuredAddressSearch}`

Most of the time you'll only need to provide roadNumber1, roadName, roadTypeCode, localityName, postcode, and stateTerritoryCode, but you can supply any fields that apply to the address.

Note that while `stateTerritoryCode` and `postcode` are marked 'required', you only need to provide one of them, (though it's usually a good idea to provide both).


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "structured": {
    "roadNumber1": "546",
    "roadName": "FLINDERS",
    "roadTypeCode": "ST",
    "localityName": "MELBOURNE",
    "stateTerritoryCode": "VIC",
    "postcode": "3000"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "2",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 2 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 2 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "3",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 3 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 3 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


#### `POST` POST - Search by G-NAF ID

**URI:** `/locations{?apiaryGNAFAddressSearch}`

See https://geoscape.com.au/ for more details on G-NAF IDs

Please note that the `fullAddress` value will always be a blank string because NBN does not return address details
for G-NAF searches. If you need any address information, please perform a 'Reverse Location ID Lookup' (details below).


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "GNAF": "GANSW098742316"
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "responseData": [
    {
      "id": "LOC123456789001",
      "fullAddress": ""
    }
  ]
}
```


#### `POST` POST - Search by coordinates

**URI:** `/locations{?apiaryCoOrdAddressSearch}`

Search by latitude and longitude


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "coordinates": {
    "latitude": "-37.82052",
    "longitude": "144.95614684"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "2",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 2 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 2 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "3",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 3 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 3 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


#### `POST` POST - Reverse Location ID Lookup

**URI:** `/locations{?apiaryReverseAddressSearch}`

Useful if you need to convert an NBN Location ID to an address.

Note that this will either return a single result, or HTTP404 if the location can't be found.


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC123456789001"
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


*Response — 404:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_error_desc": "Your request generated an NBN error status code",
  "vt_short_error": "nbn_error",
  "code": "012000",
  "type": "InvalidAddressException",
  "reason": "No records were found to match the NBN Location ID LOC123456789001 specified in the request."
}
```
