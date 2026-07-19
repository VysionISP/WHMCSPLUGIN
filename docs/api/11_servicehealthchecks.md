# Service Health Checks

## Service Health Checks

Service Health Callback Values

eventType
notificationType
Note

ServiceHealthStateChangeNotification
ServiceHealthAccepted
Service health request has been accepted and the report is being compiled

ServiceHealthStateChangeNotification
ServiceHealthCompleted
Service health report has been completed and is available to GET


### Request NBN Service Health

**URI:** `/service-health-checks`


#### `POST` POST /service-health-checks

The service health information will be compiled and you will be sent a ServiceHealthCompleted callback when it's done.

Please note that FTTC and FTTP health checks are still in the trial phase so they may not behave as expected.

Please note that you cannot run Service Health Checks on Enterprise Ethernet services.

[Sandbox only] NBN isn't allowing service health checks via their staging (sandbox) API at the moment so callbacks and responses will be simulated. This does not apply to the production API.

Required scope: create:service-health-checks


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`

```json
{
  "vtServiceId": "VT1234567"
}
```


*Response — 202:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "VTTST000000000001",
  "status": "Acknowledged"
}
```


### View service health report

**URI:** `/service-health-checks/{id}`


#### `GET` GET /service-health-checks/{id}

Please note that this will only return the health report after you've POSTed a request using the endpoint above
and received a ServiceHealthCompleted callback.

Please also note that the responses for different service types will vary slightly.

Required scope: read:service-health-checks

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | VT Test ID | VTTST000000000001 |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Example FTTN response

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "status": "Complete",
  "externalId": "VTTST000000000001",
  "id": "VTTST000000000001",
  "avcId": "AVC002000206240",
  "currentCondition": {
    "code": "STA10008",
    "status": "Red",
    "createdDateTime": "2020-02-15T00:02:59.216Z",
    "alertMessage": "Potential Service Stability Trouble",
    "summary": "The service has been unstable in the last 24 or 48 hours.",
    "nextAction": [
      {
        "code": "NBA10208",
        "description": "Please perform standard troubleshooting with your customer before raising a service restoration trouble ticket to nbn."
      }
    ]
  },
  "overviewIndicator": {
    "connectivity": "Green",
    "performance": "Green",
    "stability": "Red"
  },
  "serviceHealthSpecification": {
    "id": "HLT000000000001",
    "description": "FTTN Service Health Specification",
    "version": "2.0",
    "@type": "FTTN",
    "specificationType": "Service Health Template"
  },
  "healthCategory": [
    {
      "type": "OperationalStatus",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "OpMain",
          "id": "serviceState",
          "status": "Green",
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": null,
          "value": "Up"
        },
        {
          "@type": "OpMain",
          "id": "lineStatusChange",
          "status": null,
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "2016-12-19T05:47:36Z"
        }
      ]
    },
    {
      "type": "Speed",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "Downstream",
          "id": "ActualLineRate",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": "Mbps",
          "value": "39"
        },
        {
          "@type": "Upstream",
          "id": "ActualLineRate",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": "Mbps",
          "value": "15"
        },
        {
          "@type": "Downstream",
          "id": "AttainableLineRate",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": "Mbps",
          "value": "39"
        },
        {
          "@type": "Upstream",
          "id": "AttainableLineRate",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": "Mbps",
          "value": "15"
        },
        {
          "@type": "Downstream",
          "id": "AttainableLineRate2Hrs",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": "Mbps",
          "value": "39"
        },
        {
          "@type": "Upstream",
          "id": "AttainableLineRate2Hrs",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": "Mbps",
          "value": "15"
        },
        {
          "@type": "Downstream",
          "id": "AttainableLineRate7Days",
          "status": null,
          "timeStamp": "2020-02-14T09:27:40.948Z",
          "unit": "Mbps",
          "value": "39"
        },
        {
          "@type": "Upstream",
          "id": "AttainableLineRate7Days",
          "status": null,
          "timeStamp": "2020-02-14T09:27:40.948Z",
          "unit": "Mbps",
          "value": "15"
        },
        {
          "@type": "Downstream",
          "id": "AssuredLineRate",
          "status": null,
          "timeStamp": "2020-02-15T00:02:59.208Z",
          "unit": "Mbps",
          "value": "38"
        },
        {
          "@type": "Upstream",
          "id": "AssuredLineRate",
          "status": null,
          "timeStamp": "2020-02-15T00:02:59.208Z",
          "unit": "Mbps",
          "value": "26"
        }
      ]
    },
    {
      "type": "Dropouts",
      "version": "2.0",
      "healthCategoryItem": [
        {
          "@type": "DoToday",
          "id": "total",
          "status": null,
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "14"
        },
        {
          "@type": "DoYesterday",
          "id": "total",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do2Days",
          "id": "total",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do7Days",
          "id": "total",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do30Days",
          "id": "total",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "DoToday",
          "id": "initiated",
          "status": null,
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "0"
        },
        {
          "@type": "DoYesterday",
          "id": "initiated",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do2Days",
          "id": "initiated",
          "status": null,
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "0"
        },
        {
          "@type": "Do7Days",
          "id": "initiated",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do30Days",
          "id": "initiated",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "DoToday",
          "id": "network",
          "status": "Red",
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "7"
        },
        {
          "@type": "DoYesterday",
          "id": "network",
          "status": "Red",
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "7"
        },
        {
          "@type": "Do2Days",
          "id": "network",
          "status": "Red",
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "7"
        },
        {
          "@type": "Do7Days",
          "id": "network",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do30Days",
          "id": "network",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "DoToday",
          "id": "unexpected",
          "status": "Red",
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "7"
        },
        {
          "@type": "DoYesterday",
          "id": "unexpected",
          "status": "Red",
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "7"
        },
        {
          "@type": "Do2Days",
          "id": "unexpected",
          "status": "Red",
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Count",
          "value": "7"
        },
        {
          "@type": "Do7Days",
          "id": "unexpected",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "Do30Days",
          "id": "unexpected",
          "status": null,
          "timeStamp": null,
          "unit": "Count",
          "value": null
        },
        {
          "@type": "DoToday",
          "id": "unavailablePeriod",
          "status": null,
          "timeStamp": "2020-10-02T04:58:31.172Z",
          "unit": "Seconds",
          "value": "0"
        },
        {
          "@type": "DoYesterday",
          "id": "unavailablePeriod",
          "status": null,
          "timeStamp": null,
          "unit": "Seconds",
          "value": null
        },
        {
          "@type": "Do2Days",
          "id": "unavailablePeriod",
          "status": null,
          "timeStamp": null,
          "unit": "Seconds",
          "value": null
        },
        {
          "@type": "Do7Days",
          "id": "unavailablePeriod",
          "status": null,
          "timeStamp": null,
          "unit": "Seconds",
          "value": null
        },
        {
          "@type": "Do30Days",
          "id": "unavailablePeriod",
          "status": null,
          "timeStamp": null,
          "unit": "Seconds",
          "value": null
        }
      ]
    },
    {
      "type": "CPE",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "CPEMain",
          "id": "macAddress",
          "status": null,
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "AC:DE:48:00:00:01"
        },
        {
          "@type": "CPEMain",
          "id": "make",
          "status": null,
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "BDCM"
        },
        {
          "@type": "CPEMain",
          "id": "model",
          "status": null,
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "BDCM"
        },
        {
          "@type": "CPEMain",
          "id": "serialNo",
          "status": null,
          "timeStamp": "2020-02-14T09:27:40.948Z",
          "unit": null,
          "value": "CP1809RAPPA DJA0230TLS 17.6"
        },
        {
          "@type": "CPEMain",
          "id": "firmwareVersion",
          "status": null,
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "A2pv6F039v4"
        },
        {
          "@type": "CPEMain",
          "id": "compatibility",
          "status": "Green",
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "Compatible"
        },
        {
          "@type": "CPEMain",
          "id": "registration",
          "status": null,
          "timeStamp": "2020-02-14T10:26:21.000Z",
          "unit": null,
          "value": "Unknown"
        },
        {
          "@type": "CPEHistory",
          "id": "replaceCount",
          "status": null,
          "timeStamp": "2020-02-14T09:46:51.000Z",
          "unit": null,
          "value": "0"
        },
        {
          "@type": "CPEHistory",
          "id": "period",
          "status": null,
          "timeStamp": null,
          "unit": "Days",
          "value": "30"
        }
      ]
    },
    {
      "type": "SeamlessRateAdaptation",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "Downstream",
          "id": "mode",
          "status": "Green",
          "timeStamp": "2020-02-14T09:27:40.948Z",
          "unit": null,
          "value": "Dynamic"
        },
        {
          "@type": "Upstream",
          "id": "mode",
          "status": "Green",
          "timeStamp": "2020-02-14T09:27:40.948Z",
          "unit": null,
          "value": "Dynamic"
        }
      ]
    },
    {
      "type": "InHomeWiring",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "InHomeWiringMain",
          "id": "inHomeWiring",
          "status": "Red",
          "timeStamp": "2020-02-14T09:27:40.948Z",
          "unit": null,
          "value": "Bridge Tap Detected"
        }
      ]
    },
    {
      "type": "DynamicLineManagement",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "InHomeWiringMain",
          "id": "state",
          "status": "Grey",
          "timeStamp": null,
          "unit": null,
          "value": null
        },
        {
          "@type": "ScheduledAction",
          "id": "upstream",
          "status": null,
          "timeStamp": null,
          "unit": null,
          "value": null
        },
        {
          "@type": "ScheduledAction",
          "id": "downstream",
          "status": null,
          "timeStamp": null,
          "unit": null,
          "value": null
        }
      ]
    },
    {
      "type": "Outage",
      "version": "1.0",
      "healthCategoryItem": [
        {
          "@type": "OutageMain",
          "id": "currentOutage",
          "status": null,
          "timeStamp": null,
          "unit": null,
          "value": null
        }
      ]
    }
  ]
}
```


### Example Service Health Callbacks

**URI:** `/example-health-callback-url`

Note that the roles are reversed in this section, (i.e. our machine will POST the callback to your server).


#### `POST` FTTB HEALTH COMPLETED CALLBACK

**URI:** `/example-health-callback-url{?apiaryFTTBHealthCallback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "982d0827-fa28-4871-a363-85471d193037",
  "eventTime": "2020-09-30T07:32:59.567Z",
  "eventType": "ServiceHealthStateChangeNotification",
  "event": {
    "@type": "FTTB",
    "externalId": "VTTST000000000001",
    "id": "VTTST000000000001",
    "notificationType": "ServiceHealthCompleted",
    "objectType": "serviceHealth",
    "status": "Complete",
    "reason": "",
    "reasonCode": "",
    "serviceHealth": {
      "status": "Complete",
      "externalId": "VTTST000000000001",
      "id": "VTTST000000000001",
      "avcId": "AVC000000000001",
      "currentCondition": {
        "code": "CON10002",
        "status": "Red",
        "createdDateTime": "2020-09-30T07:32:59.567Z",
        "alertMessage": "Service is Offline",
        "summary": "The customer's modem was not detected as connected.",
        "nextAction": [
          {
            "code": "NBA10102",
            "description": "Please perform troubleshooting and ensure your customer's modem is switched on and connected correctly prior to raising a service restoration trouble ticket to nbn to investigate."
          }
        ]
      },
      "overviewIndicator": {
        "connectivity": "Red",
        "performance": "Green",
        "stability": "Green"
      },
      "serviceHealthSpecification": {
        "id": "HLT000000000001",
        "description": "FTTB Service Health Specification",
        "version": "2.0",
        "@type": "FTTB",
        "specificationType": "Service Health Template"
      },
      "healthCategory": [
        {
          "type": "OperationalStatus",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OpMain",
              "id": "serviceState",
              "status": "Red",
              "timeStamp": "2020-09-30T06:25:16.777Z",
              "unit": null,
              "value": "Down"
            },
            {
              "@type": "OpMain",
              "id": "lineStatusChange",
              "status": null,
              "timeStamp": "2020-09-30T06:25:16.777Z",
              "unit": null,
              "value": "2020-08-20T00:50:50.286Z"
            }
          ]
        },
        {
          "type": "Speed",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "Downstream",
              "id": "ActualLineRate",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Mbps",
              "value": "39.0"
            },
            {
              "@type": "Upstream",
              "id": "ActualLineRate",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Downstream",
              "id": "AttainableLineRate",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Mbps",
              "value": "39.0"
            },
            {
              "@type": "Upstream",
              "id": "AttainableLineRate",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Mbps",
              "value": "150.0"
            },
            {
              "@type": "Downstream",
              "id": "AttainableLineRate2Hrs",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Mbps",
              "value": "39.0"
            },
            {
              "@type": "Upstream",
              "id": "AttainableLineRate2Hrs",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Mbps",
              "value": "150.0"
            },
            {
              "@type": "Downstream",
              "id": "AttainableLineRate7Days",
              "status": null,
              "timeStamp": "2020-09-30T07:32:54.053Z",
              "unit": "Mbps",
              "value": "39.0"
            },
            {
              "@type": "Upstream",
              "id": "AttainableLineRate7Days",
              "status": null,
              "timeStamp": "2020-09-30T07:32:54.053Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Downstream",
              "id": "AssuredLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:32:59.552Z",
              "unit": "Mbps",
              "value": "38.4"
            },
            {
              "@type": "Upstream",
              "id": "AssuredLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:32:59.552Z",
              "unit": "Mbps",
              "value": "14.4"
            }
          ]
        },
        {
          "type": "Dropouts",
          "version": "2.0",
          "healthCategoryItem": [
            {
              "@type": "DoToday",
              "id": "total",
              "status": null,
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "3"
            },
            {
              "@type": "DoYesterday",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do2Days",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do7Days",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "DoYesterday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do2Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do7Days",
              "id": "initiated",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "initiated",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "DoYesterday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do2Days",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do7Days",
              "id": "network",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "network",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "DoYesterday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do2Days",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do7Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": "2020-09-30T07:33:00.907Z",
              "unit": "Seconds",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            },
            {
              "@type": "Do2Days",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            },
            {
              "@type": "Do7Days",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            }
          ]
        },
        {
          "type": "Cpe",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "CPEMain",
              "id": "macAddress",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": null,
              "value": "12:13:14:16"
            },
            {
              "@type": "CPEMain",
              "id": "make",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.131Z",
              "unit": null,
              "value": "Test"
            },
            {
              "@type": "CPEMain",
              "id": "model",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.131Z",
              "unit": null,
              "value": "12345"
            },
            {
              "@type": "CPEMain",
              "id": "serialNo",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.131Z",
              "unit": null,
              "value": "12345"
            },
            {
              "@type": "CPEMain",
              "id": "firmwareVersion",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.131Z",
              "unit": null,
              "value": "12.0"
            },
            {
              "@type": "CPEMain",
              "id": "compatibility",
              "status": "Grey",
              "timeStamp": "2020-09-30T06:25:16.777Z",
              "unit": null,
              "value": "Unknown"
            },
            {
              "@type": "CPEMain",
              "id": "registration",
              "status": null,
              "timeStamp": "2020-09-30T06:25:16.777Z",
              "unit": null,
              "value": "Unknown"
            },
            {
              "@type": "CPEHistory",
              "id": "replaceCount",
              "status": null,
              "timeStamp": "2020-09-30T06:25:05.126Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "CPEHistory",
              "id": "period",
              "status": null,
              "timeStamp": "2020-09-30T07:32:54.058Z",
              "unit": "Days",
              "value": "30"
            }
          ]
        },
        {
          "type": "SeamlessRateAdaptation",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "Downstream",
              "id": "mode",
              "status": "Green",
              "timeStamp": "2020-09-30T06:25:05.131Z",
              "unit": null,
              "value": "Dynamic"
            },
            {
              "@type": "Upstream",
              "id": "mode",
              "status": "Green",
              "timeStamp": "2020-09-30T06:25:05.131Z",
              "unit": null,
              "value": "Dynamic"
            }
          ]
        },
        {
          "type": "InHomeWiring",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "InHomeWiringMain",
              "id": "inHomeWiring",
              "status": "Grey",
              "timeStamp": "2020-09-30T06:25:05.136Z",
              "unit": null,
              "value": "Inconclusive"
            }
          ]
        },
        {
          "type": "DynamicLineManagement",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "InHomeWiringMain",
              "id": "state",
              "status": "Grey",
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "ScheduledAction",
              "id": "upstream",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "ScheduledAction",
              "id": "downstream",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "Outage",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OutageMain",
              "id": "currentOutage",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        }
      ]
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` FTTC HEALTH COMPLETED CALLBACK

**URI:** `/example-health-callback-url{?apiaryFTTCHealthCallback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "e55aa822-46bd-4677-a541-0d1ae1a338cc",
  "eventTime": "2020-10-01T06:03:24.965Z",
  "eventType": "ServiceHealthStateChangeNotification",
  "event": {
    "@type": "FTTC",
    "externalId": "VTTST000000000001",
    "id": "VTTST000000000001",
    "notificationType": "ServiceHealthCompleted",
    "objectType": "serviceHealth",
    "status": "Complete",
    "reason": "",
    "reasonCode": "",
    "serviceHealth": {
      "status": "Complete",
      "externalId": "VTTST000000000001",
      "id": "VTTST000000000001",
      "avcId": "AVC000000000001",
      "currentCondition": {
        "code": "STA30013",
        "status": "Red",
        "createdDateTime": "2020-10-01T06:03:24.965Z",
        "alertMessage": "Potential Line Impairment Bridge Tap Detected",
        "summary": "The service has been stable over the 2 days, however, a bridge tap has been detected.",
        "nextAction": [
          {
            "code": "NBA30213",
            "description": "The service is meeting the stability threshold, but testing indicates a bridge tap is present that may be impacting stability. The customer may get better stability if the bridge tap is removed. A service restoration trouble ticket should not be raised for this service at this time as it will most likely result in No Fault Found."
          }
        ]
      },
      "overviewIndicator": {
        "connectivity": "Green",
        "performance": "Green",
        "stability": "Green"
      },
      "serviceHealthSpecification": {
        "id": "HLT000000000003",
        "description": "FTTC Service Health Specification",
        "version": "1.0",
        "@type": "FTTC",
        "specificationType": "Service Health Template"
      },
      "healthCategory": [
        {
          "type": "OperationalStatus",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OpMain",
              "id": "serviceState",
              "status": "Green",
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": null,
              "value": "Up"
            },
            {
              "@type": "OpMain",
              "id": "lastStatusChange",
              "status": null,
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": null,
              "value": "2018-02-21T08:35:03Z"
            },
            {
              "@type": "OpMain",
              "id": "reversePowerState",
              "status": null,
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": null,
              "value": "Powered"
            }
          ]
        },
        {
          "type": "Speed",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "Downstream",
              "id": "actualLineRate",
              "status": "Grey",
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": "Mbps",
              "value": "109"
            },
            {
              "@type": "Upstream",
              "id": "actualLineRate",
              "status": "Grey",
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": "Mbps",
              "value": "44.34"
            }
          ]
        },
        {
          "type": "Cpe",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "CPEMain",
              "id": "macAddress",
              "status": "Grey",
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": null,
              "value": "undefined;"
            }
          ]
        },
        {
          "type": "Ncd",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "NCDMain",
              "id": "macAddress",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.000Z",
              "unit": null,
              "value": "18:F1:45:A8:B3:A9"
            },
            {
              "@type": "NCDMain",
              "id": "make",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "NCDMain",
              "id": "portId",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.000Z",
              "unit": null,
              "value": "UNI-D 1"
            },
            {
              "@type": "NCDMain",
              "id": "portState",
              "status": null,
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": null,
              "value": "Up"
            },
            {
              "@type": "NCDMain",
              "id": "currentSpeedAndDuplex",
              "status": null,
              "timeStamp": "2020-10-01T05:57:25.283Z",
              "unit": null,
              "value": "1000/Full"
            }
          ]
        },
        {
          "type": "InHomeWiring",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "InHomeWiringMain",
              "id": "inHomeWiring",
              "status": "Red",
              "timeStamp": "2020-10-01T01:41:28.000Z",
              "unit": null,
              "value": "Bridge Tap Detected"
            },
            {
              "@type": "InHomeWiringMain",
              "id": "lineImpairments",
              "status": "Red",
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "Dropouts",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "DoYesterday",
              "id": "total",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "total",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "total",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "total",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "initiated",
              "status": "Green",
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "2"
            },
            {
              "@type": "Do2Days",
              "id": "initiated",
              "status": "Green",
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "2"
            },
            {
              "@type": "Do7Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "2"
            },
            {
              "@type": "Do7Days",
              "id": "network",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "network",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "2"
            },
            {
              "@type": "Do2Days",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "2"
            },
            {
              "@type": "Do7Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": "2020-10-01T01:41:28.313Z",
              "unit": "Count",
              "value": "0"
            }
          ]
        },
        {
          "type": "Outage",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OutageMain",
              "id": "currentOutage",
              "status": null,
              "timeStamp": "2020-10-01T06:03:38.770Z",
              "unit": null,
              "value": "false"
            }
          ]
        }
      ]
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` FTTN HEALTH COMPLETED CALLBACK

**URI:** `/example-health-callback-url{?apiaryFTTNHealthCallback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "96fe6111-dd8a-42d9-af69-906d897d3787",
  "eventTime": "2020-09-30T07:06:31.948Z",
  "eventType": "ServiceHealthStateChangeNotification",
  "event": {
    "@type": "FTTN",
    "externalId": "VTTST000000000001",
    "id": "VTTST000000000001",
    "notificationType": "ServiceHealthCompleted",
    "objectType": "serviceHealth",
    "status": "Complete",
    "reason": "",
    "reasonCode": "",
    "serviceHealth": {
      "status": "Complete",
      "externalId": "VTTST000000000001",
      "id": "VTTST000000000001",
      "avcId": "AVC000000000001",
      "currentCondition": {
        "code": "OTH10001",
        "status": "Green",
        "createdDateTime": "2020-09-30T07:06:31.948Z",
        "alertMessage": "No Fault Detected",
        "summary": "The service appears to be working within nbn specifications.",
        "nextAction": [
          {
            "code": "NBA10001",
            "description": "If your customer is still experiencing an issue, obtain your customer's modem MAC address and check it matches your internal systems and the details recorded in the Service Health Summary. If details match, please troubleshoot the customer's equipment configuration. If the details do not match, please raise a service loss trouble ticket to nbn to investigate a possible jumpering issue. Include the details of the different MAC address in the trouble ticket"
          }
        ]
      },
      "overviewIndicator": {
        "connectivity": "Green",
        "performance": "Green",
        "stability": "Green"
      },
      "serviceHealthSpecification": {
        "id": "HLT000000000001",
        "description": "FTTN Service Health Specification",
        "version": "2.0",
        "@type": "FTTN",
        "specificationType": "Service Health Template"
      },
      "healthCategory": [
        {
          "type": "OperationalStatus",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OpMain",
              "id": "serviceState",
              "status": "Green",
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "Up"
            },
            {
              "@type": "OpMain",
              "id": "lineStatusChange",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "2020-09-28T04:39:37.180Z"
            }
          ]
        },
        {
          "type": "Speed",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "Downstream",
              "id": "ActualLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.777Z",
              "unit": "Mbps",
              "value": "10.0"
            },
            {
              "@type": "Upstream",
              "id": "ActualLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.777Z",
              "unit": "Mbps",
              "value": "10.0"
            },
            {
              "@type": "Downstream",
              "id": "AttainableLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": "Mbps",
              "value": "110.6"
            },
            {
              "@type": "Upstream",
              "id": "AttainableLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": "Mbps",
              "value": "50.4"
            },
            {
              "@type": "Downstream",
              "id": "AttainableLineRate2Hrs",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.777Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Upstream",
              "id": "AttainableLineRate2Hrs",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.777Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Downstream",
              "id": "AttainableLineRate7Days",
              "status": null,
              "timeStamp": "2020-09-30T07:06:26.457Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Upstream",
              "id": "AttainableLineRate7Days",
              "status": null,
              "timeStamp": "2020-09-30T07:06:26.457Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Downstream",
              "id": "AssuredLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:06:31.941Z",
              "unit": "Mbps",
              "value": "15.0"
            },
            {
              "@type": "Upstream",
              "id": "AssuredLineRate",
              "status": null,
              "timeStamp": "2020-09-30T07:06:31.941Z",
              "unit": "Mbps",
              "value": "15.0"
            }
          ]
        },
        {
          "type": "Dropouts",
          "version": "2.0",
          "healthCategoryItem": [
            {
              "@type": "DoToday",
              "id": "total",
              "status": null,
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "3"
            },
            {
              "@type": "DoYesterday",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do2Days",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do7Days",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "total",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "DoYesterday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do2Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do7Days",
              "id": "initiated",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "initiated",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "DoYesterday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do2Days",
              "id": "network",
              "status": "Green",
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do7Days",
              "id": "network",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "network",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "DoYesterday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do2Days",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Count",
              "value": "1"
            },
            {
              "@type": "Do7Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": null,
              "unit": "Count",
              "value": null
            },
            {
              "@type": "DoToday",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": "2020-09-30T07:06:33.275Z",
              "unit": "Seconds",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            },
            {
              "@type": "Do2Days",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            },
            {
              "@type": "Do7Days",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            },
            {
              "@type": "Do30Days",
              "id": "unavailablePeriod",
              "status": null,
              "timeStamp": null,
              "unit": "Seconds",
              "value": null
            }
          ]
        },
        {
          "type": "Cpe",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "CPEMain",
              "id": "macAddress",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.777Z",
              "unit": null,
              "value": "12:13:14:16"
            },
            {
              "@type": "CPEMain",
              "id": "make",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "BDCM"
            },
            {
              "@type": "CPEMain",
              "id": "model",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "BDCM"
            },
            {
              "@type": "CPEMain",
              "id": "serialNo",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.784Z",
              "unit": null,
              "value": "12345"
            },
            {
              "@type": "CPEMain",
              "id": "firmwareVersion",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "A2pv6F039v4"
            },
            {
              "@type": "CPEMain",
              "id": "compatibility",
              "status": "Green",
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "Compatible"
            },
            {
              "@type": "CPEMain",
              "id": "registration",
              "status": null,
              "timeStamp": "2020-09-30T07:04:58.986Z",
              "unit": null,
              "value": "Unknown"
            },
            {
              "@type": "CPEHistory",
              "id": "replaceCount",
              "status": null,
              "timeStamp": "2020-09-30T07:04:47.777Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "CPEHistory",
              "id": "period",
              "status": null,
              "timeStamp": "2020-09-30T07:06:26.463Z",
              "unit": "Days",
              "value": "30"
            }
          ]
        },
        {
          "type": "SeamlessRateAdaptation",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "Downstream",
              "id": "mode",
              "status": "Green",
              "timeStamp": "2020-09-30T07:04:47.784Z",
              "unit": null,
              "value": "Dynamic"
            },
            {
              "@type": "Upstream",
              "id": "mode",
              "status": "Green",
              "timeStamp": "2020-09-30T07:04:47.784Z",
              "unit": null,
              "value": "Dynamic"
            }
          ]
        },
        {
          "type": "InHomeWiring",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "InHomeWiringMain",
              "id": "inHomeWiring",
              "status": "Grey",
              "timeStamp": "2020-09-30T07:04:47.789Z",
              "unit": null,
              "value": "Inconclusive"
            }
          ]
        },
        {
          "type": "DynamicLineManagement",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "InHomeWiringMain",
              "id": "state",
              "status": "Grey",
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "ScheduledAction",
              "id": "upstream",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "ScheduledAction",
              "id": "downstream",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "Outage",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OutageMain",
              "id": "currentOutage",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        }
      ]
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` HFC HEALTH COMPLETED CALLBACK

**URI:** `/example-health-callback-url{?apiaryHFCHealthCallback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "e4167108-a737-47f5-a965-e32e021fc3bd",
  "eventTime": "2021-08-12T01:46:01.828Z",
  "eventType": "ServiceHealthStateChangeNotification",
  "event": {
    "@type": "HFC",
    "externalId": "VTTST000000000001",
    "id": "VTTST000000000001",
    "notificationType": "ServiceHealthCompleted",
    "objectType": "serviceHealth",
    "status": "Complete",
    "reason": "",
    "reasonCode": "",
    "serviceHealth": {
      "status": "Complete",
      "externalId": "VTTST000000000001",
      "id": "VTTST000000000001",
      "avcId": "AVC000000000001",
      "currentCondition": {
        "code": "PER20002",
        "status": "Red",
        "createdDateTime": "2021-08-12T01:46:01.828Z",
        "alertMessage": "Network Utilisation Issue Detected",
        "summary": "HFC Signal is within specifications and a network upgrade was recently completed",
        "nextAction": [
          {
            "code": "NBA20302",
            "description": "A network upgrade was recently completed and optimisation of the network is in progress. Refer to the nbn change request (CRQ) for further information. There may be a delay between the completion of the upgrade and noticeable improvement of the customer's experience. If your customer is still experiencing performance issues, 24 hrs after the CRQ has closed, complete standard pre-checks including an NTD reboot with your customer and raise a service restoration trouble ticket to nbn."
          }
        ]
      },
      "overviewIndicator": {
        "connectivity": "Green",
        "performance": "Green",
        "stability": "Green"
      },
      "serviceHealthSpecification": {
        "id": "HLT000000000002",
        "description": "HFC Service Health Specification",
        "version": "1.2",
        "@type": "HFC",
        "specificationType": "Service Health Template"
      },
      "healthCategory": [
        {
          "type": "Speed",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "HFCSignalMain",
              "id": "status",
              "status": "Green",
              "timeStamp": "2021-08-12T01:46:01.848Z",
              "unit": null,
              "value": "No Issue Detected"
            }
          ]
        },
        {
          "type": "Cpe",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "CPEMain",
              "id": "macAddress",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "OperationalStatus",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OpMain",
              "id": "serviceState",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "Up"
            },
            {
              "@type": "OpMain",
              "id": "uptime",
              "status": null,
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": "Seconds",
              "value": "605081"
            },
            {
              "@type": "OpMain",
              "id": "lastStatusChange",
              "status": null,
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "2021-08-09T01:41:51Z"
            }
          ]
        },
        {
          "type": "Dropouts",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "DoMain",
              "id": "timeOfLastDropout",
              "status": null,
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "2021-08-05T00:41:26Z"
            },
            {
              "@type": "DoToday",
              "id": "total",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "total",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "total",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "total",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "total",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoToday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoToday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "network",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "network",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "network",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoToday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": "2021-08-12T01:45:41.454Z",
              "unit": "Count",
              "value": "0"
            }
          ]
        },
        {
          "type": "NTD",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "NTDMain",
              "id": "macAddress",
              "status": null,
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "20:3D:66:AF:C0:59"
            },
            {
              "@type": "NTDMain",
              "id": "serviceConfiguration",
              "status": "Green",
              "timeStamp": "2021-08-12T01:46:01.821Z",
              "unit": null,
              "value": "Aligned"
            },
            {
              "@type": "NTDMain",
              "id": "model",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "CM8200B"
            },
            {
              "@type": "NTDMain",
              "id": "portId",
              "status": null,
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "UNI-D1"
            },
            {
              "@type": "NTDMain",
              "id": "portState",
              "status": "Green",
              "timeStamp": "2021-08-12T01:45:59Z",
              "unit": null,
              "value": "Up"
            }
          ]
        },
        {
          "type": "NodeUtilisation",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "NuYesterday",
              "id": "statusUpstream",
              "status": "Green",
              "timeStamp": "2021-08-09T06:04:21.482Z",
              "unit": null,
              "value": "No Issue Detected"
            },
            {
              "@type": "Nu7Days",
              "id": "statusUpstream",
              "status": "Green",
              "timeStamp": "2021-08-09T06:04:21.484Z",
              "unit": null,
              "value": "No Issue Detected"
            },
            {
              "@type": "NuYesterday",
              "id": "statusDownstream",
              "status": "Red",
              "timeStamp": "2021-08-09T06:04:21.482Z",
              "unit": null,
              "value": "Issue Detected"
            },
            {
              "@type": "Nu7Days",
              "id": "statusDownstream",
              "status": "Green",
              "timeStamp": "2021-08-09T06:04:21.484Z",
              "unit": null,
              "value": "No Issue Detected"
            }
          ]
        },
        {
          "type": "Outage",
          "version": "1.2",
          "healthCategoryItem": [
            {
              "@type": "OutageMain",
              "id": "currentOutage",
              "status": null,
              "timeStamp": "2021-08-12T01:46:01.807Z",
              "unit": null,
              "value": "false"
            },
            {
              "@type": "CompletedLast7Days",
              "id": "plannedOutageId",
              "status": null,
              "timeStamp": "2021-08-12T01:46:01.807Z",
              "unit": null,
              "value": "CRQ000002672310"
            },
            {
              "@type": "CompletedLast7Days",
              "id": "plannedOutageId",
              "status": null,
              "timeStamp": "2021-08-12T01:46:01.807Z",
              "unit": null,
              "value": "CRQ000002671705"
            },
            {
              "@type": "CompletedLast7Days",
              "id": "plannedOutageId",
              "status": null,
              "timeStamp": "2021-08-12T01:46:01.807Z",
              "unit": null,
              "value": "CRQ000002672308"
            },
            {
              "@type": "ScheduledNext60Days",
              "id": "plannedOutageId",
              "status": null,
              "timeStamp": "2021-08-12T01:46:01.807Z",
              "unit": null,
              "value": "CRQ000002672901"
            }
          ]
        }
      ]
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` FTTP HEALTH COMPLETED CALLBACK

**URI:** `/example-health-callback-url{?apiaryFTTPHealthCallback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "3139b28d-fe0d-4873-a2fa-42b13eb367a9",
  "eventTime": "2021-10-26T01:44:46.550Z",
  "eventType": "ServiceHealthStateChangeNotification",
  "event": {
    "@type": "FTTP",
    "externalId": "VTTST000000000001",
    "id": "VTTST000000000001",
    "notificationType": "ServiceHealthCompleted",
    "objectType": "serviceHealth",
    "status": "Complete",
    "reason": "",
    "reasonCode": "",
    "serviceHealth": {
      "status": "Complete",
      "externalId": "VTTST000000000001",
      "id": "VTTST000000000001",
      "avcId": "AVC000000000001",
      "currentCondition": {
        "code": "OTH60001",
        "status": "Green",
        "createdDateTime": "2021-10-26T01:44:46.550Z",
        "alertMessage": "No Fault Detected",
        "summary": "The service appears to be working within nbn parameters.",
        "nextAction": [
          {
            "code": "NBA60001",
            "description": "If your customer is still experiencing an issue, obtain your customer's modem MAC address and NTD (Network Termination Device) Serial number and check it matches your internal systems and the details recorded in the Service Health Summary.  If the details match, please troubleshoot the customer's equipment configuration.  If the details do not match, please raise a service request to nbn to investigate a possible addressing issue. Include the details of the different MAC address and NTD Serial number in the trouble ticket."
          }
        ]
      },
      "overviewIndicator": {
        "connectivity": "Green",
        "performance": "Green",
        "stability": "Green"
      },
      "serviceHealthSpecification": {
        "id": "HLT000000000005",
        "description": "FTTP Service Health Specification",
        "version": "1.0",
        "@type": "FTTP"
      },
      "healthCategory": [
        {
          "type": "OperationalStatus",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OpMain",
              "id": "serviceState",
              "status": "Green",
              "timeStamp": "2021-10-26T12:44:39.253Z",
              "unit": null,
              "value": "Up"
            },
            {
              "@type": "OpMain",
              "id": "lastStatusChange",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "OpMain",
              "id": "lastChangeReason",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "OpticalSignal",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OptMain",
              "id": "status",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:46.547Z",
              "unit": null,
              "value": "No Issue Detected"
            }
          ]
        },
        {
          "type": "Cpe",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "CPEMain",
              "id": "macAddress",
              "status": null,
              "timeStamp": "2021-10-26T01:44:46.559Z",
              "unit": null,
              "value": "CA:FE:BE:EF:CA:FE"
            }
          ]
        },
        {
          "type": "NTD",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "NTDMain",
              "id": "ntdId",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.180Z",
              "unit": null,
              "value": "NTD0000000000001"
            },
            {
              "@type": "Data",
              "id": "portId",
              "status": null,
              "timeStamp": "2021-10-26T01:44:42.462Z",
              "unit": null,
              "value": "UNI-D2"
            },
            {
              "@type": "Data",
              "id": "portState",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:42.462Z",
              "unit": null,
              "value": "Up"
            },
            {
              "@type": "NTDMain",
              "id": "serialNumber",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.180Z",
              "unit": null,
              "value": "ALF00000001"
            },
            {
              "@type": "NTDMain",
              "id": "make",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "NTDMain",
              "id": "installLocation",
              "status": null,
              "timeStamp": "2021-10-25T10:00:00.000Z",
              "unit": null,
              "value": "Indoor"
            },
            {
              "@type": "NTDMain",
              "id": "currentSpeedAndDuplex",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "Dropouts",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "DoToday",
              "id": "total",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "total",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "total",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "total",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "total",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoToday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "initiated",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoToday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "network",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "network",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "network",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "network",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoToday",
              "id": "unexpected",
              "status": "Grey",
              "timeStamp": "2021-10-26T01:44:46.560Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "DoYesterday",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do2Days",
              "id": "unexpected",
              "status": "Green",
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do7Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            },
            {
              "@type": "Do30Days",
              "id": "unexpected",
              "status": null,
              "timeStamp": "2021-10-26T01:44:39.190Z",
              "unit": "Count",
              "value": "0"
            }
          ]
        },
        {
          "type": "Outage",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OutageMain",
              "id": "currentOutage",
              "status": null,
              "timeStamp": "2021-10-26T01:44:46.543Z",
              "unit": null,
              "value": "false"
            }
          ]
        }
      ]
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` FIXED WIRELESS HEALTH COMPLETED CALLBACK

**URI:** `/example-health-callback-url{?apiaryFixedWirelessHealthCallback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "4dfebf65-3de5-4309-ae20-c0d4b6be1511",
  "eventTime": "2020-10-02T02:12:05.635Z",
  "eventType": "ServiceHealthStateChangeNotification",
  "event": {
    "@type": "Fixed Wireless",
    "externalId": "VTTST000000000001",
    "id": "VTTST000000000001",
    "notificationType": "ServiceHealthCompleted",
    "objectType": "serviceHealth",
    "status": "Complete",
    "reason": "",
    "reasonCode": "",
    "serviceHealth": {
      "status": "Complete",
      "externalId": "VTTST000000000001",
      "id": "VTTST000000000001",
      "avcId": "AVC000000000001",
      "currentCondition": {
        "code": "OUT00001",
        "status": "Red",
        "createdDateTime": "2020-10-02T02:12:05.635Z",
        "alertMessage": "Network Outage In Progress",
        "summary": "This service is likely impacted by a known network outage.",
        "nextAction": [
          {
            "code": "NBA00001",
            "description": "A service restoration trouble ticket should not be raised for this service at this time as it will most likely result in No Action Required.  We recommend waiting until the outage has concluded. Once you have received notification that the outage has been resolved please ask your customer to restart their modem and Network Termination Device (NTD) confirm that their service has been restored."
          }
        ]
      },
      "overviewIndicator": {
        "connectivity": "Grey",
        "performance": "Amber",
        "stability": "Grey"
      },
      "serviceHealthSpecification": {
        "id": "HLT000000000004",
        "description": "Fixed Wireless Service Health Specification",
        "version": "1.0",
        "@type": "Fixed Wireless",
        "specificationType": "Service Health Template"
      },
      "healthCategory": [
        {
          "type": "OperationalStatus",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OpMain",
              "id": "serviceState",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "NTD",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "NTDMain",
              "id": "ntdId",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.126Z",
              "unit": null,
              "value": "NTD999100171650"
            },
            {
              "@type": "NTDMain",
              "id": "portId",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.126Z",
              "unit": null,
              "value": "UNI-D1"
            },
            {
              "@type": "NTDMain",
              "id": "ntdVersion",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "Network",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "Cell",
              "id": "busyHourPerformance",
              "status": "Grey",
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "Cell",
              "id": "forecastUpgradeDate",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.175Z",
              "unit": null,
              "value": "Oct-2020"
            },
            {
              "@type": "Cell",
              "id": "plannedActivity",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            },
            {
              "@type": "Backhaul",
              "id": "busyHourPerformance",
              "status": "Amber",
              "timeStamp": "2020-10-02T02:12:05.175Z",
              "unit": null,
              "value": "Link has been upgraded within the last 28 days"
            },
            {
              "@type": "Backhaul",
              "id": "forecastUpgradeDate",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.175Z",
              "unit": null,
              "value": "Oct-2020"
            },
            {
              "@type": "Backhaul",
              "id": "plannedActivity",
              "status": null,
              "timeStamp": null,
              "unit": null,
              "value": null
            }
          ]
        },
        {
          "type": "Outage",
          "version": "1.0",
          "healthCategoryItem": [
            {
              "@type": "OutageMain",
              "id": "currentOutage",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.631Z",
              "unit": null,
              "value": "true"
            },
            {
              "@type": "OutageMain",
              "id": "plannedOutageId",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.631Z",
              "unit": null,
              "value": "CRQ000002619201"
            },
            {
              "@type": "OutageMain",
              "id": "unplannedOutageIds",
              "status": null,
              "timeStamp": "2020-10-02T02:12:05.631Z",
              "unit": null,
              "value": "INC000010146336"
            }
          ]
        }
      ]
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`
