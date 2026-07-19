# Services

## Services

Services Callback Values

eventType
notificationType
Note

ProductAttributeValueChangeNotification
ProductInstanceUpdated
Sent when NBN updates something to do with a service (e.g. changing UNI-D port on an NTD because the old port was faulty

ProductRemoveNotification
ProductInstanceDisconnected
Service has been disconnected, (usually because it’s been churned to another provider)


### Get Service from VT Service ID

**URI:** `/services{?vtServiceId,includeMdfPatch,legacySpeedEnums}`


#### `GET` GET /services{?vtServiceId,includeMdfPatch,legacySpeedEnums}

Required scope: read:services

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID. Must start with 'VT' | VT0000123 |
|  | boolean | no | [Optional] Include MDF patch information. Only allowed for NBN FTTB services. |  |
|  | boolean | no | Whether to display the old speed enumerations (100D20U, 250D25U, 1000D50U) for FTTP and HFC services |  |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "services": [
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
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
  "vt_short_error": "not_found",
  "vt_error_desc": "The requested resource was not found"
}
```


### Get Service from Supplier Service ID

**URI:** `/services{?supplierServiceId,includeMdfPatch,legacySpeedEnums}`


#### `GET` GET /services{?supplierServiceId,includeMdfPatch,legacySpeedEnums}

Required scope: read:services

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | PRI000000001234 |
|  | boolean | no | [Optional] Include MDF patch information. Only allowed for NBN FTTB services. |  |
|  | boolean | no | Whether to display the old speed enumerations (100D20U, 250D25U, 1000D50U) for FTTP and HFC services |  |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "services": [
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
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
  "vt_short_error": "not_found",
  "vt_error_desc": "The requested resource was not found"
}
```


### Get All Services or Search for Services Containing a Specific String

**URI:** `/services{?searchString,page,limit,status,legacySpeedEnums}`


#### `GET` GET /services{?searchString,page,limit,status,legacySpeedEnums}

Note that the results from this endpoint are paginated

Required scope: read:services

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | Omit this or leave it blank to return all services. Matches any part of VT Service ID, Supplier Service ID, Service Type, Supplier, Description, Additional Description, AVC ID, or LOC ID | Jane Citizen |
|  | number | no | Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty services array | 5 |
|  | number | no | Maximum number of services on a page. Default 100. Minimum 10. Maximum 1000. | 100 |
|  | string | no | Filter by service status (Active or Cancelled) | Active |
|  | boolean | no | Whether to display the old speed enumerations (100D20U, 250D25U, 1000D50U) for FTTP and HFC services |  |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "services": [
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    },
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    },
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/services?page=5&limit=100",
    "first": "/api/v1/services?page=1&limit=100",
    "last": "/api/v1/services?page=14&limit=100",
    "prev": "/api/v1/services?page=4&limit=100",
    "next": "/api/v1/services?page=6&limit=100"
  }
}
```
