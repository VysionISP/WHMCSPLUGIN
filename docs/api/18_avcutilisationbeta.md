# AVC Utilisation - Beta

## AVC Utilisation - Beta

This is currently in beta.
There may be breaking changes before it's released.

The sandbox data is currently randomly generated. The structures and formats match what you'd see in
production, but the data itself is nonsensical and will change with each request.


### List Available Reports

**URI:** `/avc-utilisation/list-reports{?fromCreatedUtc,toCreatedUtc,page,limit,apiaryAvcUtilisationListReports}`

Retrieves a list of available reports.

NBN will occasionally issue an updated report for a given day. When this happens, the old report will
still be available, however there will be a new entry for the same reportDate with a more recent `createdUtc` timestamp.

Results are sorted by createdDate in ascending order. This means that if NBN issues an updated report for a previous
day, the `reportDate`s will not be in chronological order.

These responses are paginated.

In the sandbox environment, this data will be dynamically generated and may change over time.


#### `GET` GET /avc-utilisation/list-reports{?fromCreatedUtc,toCreatedUtc,page,limit,apiaryAvcUtilisationListReports}

Required scope: read:avc-utilisation

Rate limits: steady - 20 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | [Optional] Limit results to reports created at or after this time. YYYY-MM-DDTHH:MM:SS format. |  |
|  | string | no | [Optional] Limit results to reports created at or before this time. YYYY-MM-DDTHH:MM:SS format. |  |
|  | number | no | [Optional] Fetch a specific page of results. Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty invoices array | 5 |
|  | number | no | [Optional] Maximum number of entries on a page. Default 100. Minimum 10. Maximum 1000. | 100 |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "records": [
    {
      "uuid": "c7a19ada-0a6e-419d-9fc4-fd563c18674a",
      "reportDate": "2023-11-01",
      "createdUtc": "2023-11-02T04:02:03",
      "name": "AVC Utilisation Report - 01 November 2023"
    },
    {
      "uuid": "6d9b20ca-b901-48ad-b245-6c73e7fdf400",
      "reportDate": "2023-11-01",
      "createdUtc": "2023-11-02T08:02:03",
      "name": "UPDATED AVC Utilisation Report - 01 November 2023"
    },
    {
      "uuid": "47c28cb8-127f-4994-bdfc-cde36da5faf6",
      "reportDate": "2023-11-02",
      "createdUtc": "2023-11-04T01:02:03",
      "name": "AVC Utilisation Report - 02 November 2023"
    },
    {
      "uuid": "86417817-8297-40fd-a749-4943283a67ec",
      "reportDate": "2023-11-03",
      "createdUtc": "2023-11-05T01:02:03",
      "name": "AVC Utilisation Report - 03 November 2023"
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/avc-utilisation/list-reports?page=5&limit=100",
    "first": "/api/v1/avc-utilisation/list-reports?page=1&limit=100",
    "last": "/api/v1/avc-utilisation/list-reports?page=14&limit=100",
    "prev": "/api/v1/avc-utilisation/list-reports?page=4&limit=100",
    "next": "/api/v1/avc-utilisation/list-reports?page=6&limit=100"
  }
}
```


### Fetch Records by Report ID as JSON

**URI:** `/avc-utilisation/by-uuid/{uuid}{?page,limit,apiaryAvcUtilisationRecordsByReportIDJSON}`

Returns records for the given report ID.

In the sandbox environment, this data will be dynamically generated and may change over time.


#### `GET` GET /avc-utilisation/by-uuid/{uuid}{?page,limit,apiaryAvcUtilisationRecordsByReportIDJSON}

Required scope: read:avc-utilisation

Rate limits: steady - 10 requests/minute, burst - 2 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Report ID | c7a19ada-0a6e-419d-9fc4-fd563c18674a |
|  | number | no | [Optional] Fetch a specific page of results. Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty invoices array | 5 |
|  | number | no | [Optional] Maximum number of entries on a page. Default 10000. Minimum 100. Maximum 10000. | 100 |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "uuid": "c7a19ada-0a6e-419d-9fc4-fd563c18674a",
  "reportDate": "2023-11-01",
  "createdUtc": "2023-11-02T04:02:03",
  "name": "AVC Utilisation Report - 01 November 2023",
  "reports": [
    {
      "csaId": "CSA000000000001",
      "poiId": "0ABC",
      "avcId": "AVC000000000002",
      "priId": "PRI000000000003",
      "speedTier": "12/1",
      "csaPeakHour": "19:30",
      "avcUtilCsaPeakHr": "0.001212",
      "avcPeakHour": "15:00",
      "avcUtilAvcPeakHr": "0.006458",
      "overage": "0.001212",
      "capped": null,
      "breach": false
    },
    {
      "csaId": "CSA000000000004",
      "poiId": "0DEF",
      "avcId": "AVC000000000005",
      "priId": "PRI000000000006",
      "speedTier": "25/5",
      "csaPeakHour": "20:00",
      "avcUtilCsaPeakHr": "0.015772",
      "avcPeakHour": "10:00",
      "avcUtilAvcPeakHr": "1.331757",
      "overage": "0.000000",
      "capped": null,
      "breach": false
    },
    {
      "csaId": "CSA000000000007",
      "poiId": "0GHI",
      "avcId": "AVC000000000008",
      "priId": "PRI000000000009",
      "speedTier": "50/20",
      "csaPeakHour": "23:00",
      "avcUtilCsaPeakHr": "3.860719",
      "avcPeakHour": "21:30",
      "avcUtilAvcPeakHr": "6.308523",
      "overage": "0.360719",
      "capped": null,
      "breach": true
    },
    {
      "csaId": "CSA000000000010",
      "poiId": "0JKL",
      "avcId": "AVC000000000011",
      "priId": "PRI000000000012",
      "speedTier": "25/10",
      "csaPeakHour": null,
      "avcUtilCsaPeakHr": null,
      "avcPeakHour": null,
      "avcUtilAvcPeakHr": null,
      "overage": "0.000000",
      "capped": null,
      "breach": false
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=5&limit=100",
    "first": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=1&limit=100",
    "last": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=14&limit=100",
    "prev": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=4&limit=100",
    "next": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=6&limit=100"
  }
}
```
