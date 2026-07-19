# Service Tests

## Service Tests

Service Test Callback Values

eventType
event.notificationType
event.status
Notes

ServiceTestStateChangeNotification
TestAccepted
Accepted
Not sent for all test types. Test request has been accepted and will be run soon

ServiceTestStateChangeNotification
TestInProgress
InProgress
Not sent for all test types. Test is currently running

ServiceTestStateChangeNotification
TestCompleted
Completed
Test has been completed. Please check `event`.`serviceTest`.`serviceTestResults` to see if it passed or failed

ServiceTestStateChangeNotification
TestCancelled
Cancelled
Test could not be completed, e.g. because a different test is already in progress

ServiceTestStateChangeNotification
TestRejected
Rejected
Test could not be initiated


### Request a Service Test

**URI:** `/service-tests`


#### `POST` POST /service-tests

Run diagnostic service tests.

Please note that the results of a requested test will be sent to you in a callback.

Required scope: create:service-tests


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "serviceType": "NCAS",
  "avcId": "AVC000000000001",
  "testType": "DPU_PORT_STATUS"
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
  "id": "VTTST000000000001"
}
```


### Example Service Test Callbacks

**URI:** `/example-callback-url`

Note that the roles are reversed in this section, (i.e. our machine will POST the callback to your server).


#### `POST` DPU PORT RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestDPUPortReset}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "b3f7ea16-5c05-49dd-af5f-57e1ed22a7bf",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002012",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "DPU Port Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` DPU PORT STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestDPUPortStatus}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "fc31e485-b4c8-4576-a3e4-2f6d248af998",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001001",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "DPU Port Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "type": "DPU Port Measure",
              "dpuPortNumber": "1",
              "ethernetPortOperationalState": "UP",
              "reversePowerState": "Powered",
              "accessLineRate": "118.65/44.34 Mbps",
              "xdslSyncState": "In Sync"
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` DPU STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestDPUStatus}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "656c3ebd-2e34-4cc3-a7ba-586f0d70661c",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001002",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "DPU Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "dpuOperationalState": "UP",
              "enniCVlan": "3",
              "enniSVlan": "3511",
              "type": "DPU Measure"
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` DYNAMIC LINE MANAGEMENT STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestDynamicLineManagementStatus}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "3f0a7128-d165-4a63-aba8-29f26f4575dd",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001003",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Dynamic Line Management Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "type": "DLM Measure",
              "dlmProfileSwitchReasonDownstream": "NA",
              "dlmProfileSwitchReasonUpstream": "NA",
              "dlmProfileChangeProposedTimestamp": "NA",
              "dlmProfileChangeTimestampInPast24hrs": "NA",
              "dlmActive": "Y",
              "count": 1,
              "dlmProfileChangeInPast24hrs": "N",
              "dlmProfileChangeScheduled": "N"
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` EE BTD STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestEEBTDSTATUS}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "c5127e1c-331f-4909-ae99-da9dfc924e98",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "OVC000000000001",
        "type": "OVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001011",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "BTD Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "btd": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Operational State",
                    "value": "Up"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Last Change Time",
                    "value": "26 days, 00:21:12.00 (hr:min:sec)"
                  }
                ]
              }
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` EE LOOPBACK COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestEELOOPBACK}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "ed5f0f83-a28d-40cc-a6f0-08e2cf67b96a",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "OVC000000000001",
        "type": "OVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002016",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Loopback Test",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "highCosProfile": {
                "result": "Passed",
                "profile": "HIGH",
                "measurements": [
                  {
                    "@type": "PacketAnalysis",
                    "id": "Sent",
                    "value": "5",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Received",
                    "value": "5",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Lost",
                    "value": "0",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Lost Percentage",
                    "value": "0",
                    "unit": "%"
                  }
                ]
              },
              "mediumCosProfile": {
                "result": "Passed",
                "profile": "MEDIUM",
                "measurements": [
                  {
                    "@type": "PacketAnalysis",
                    "id": "Sent",
                    "value": "5",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Received",
                    "value": "5",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Lost",
                    "value": "0",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Lost Percentage",
                    "value": "0",
                    "unit": "%"
                  }
                ]
              },
              "lowCosProfile": {
                "result": "Passed",
                "profile": "LOW",
                "measurements": [
                  {
                    "@type": "PacketAnalysis",
                    "id": "Sent",
                    "value": "5",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Received",
                    "value": "5",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Lost",
                    "value": "0",
                    "unit": "Packets"
                  },
                  {
                    "@type": "PacketAnalysis",
                    "id": "Lost Percentage",
                    "value": "0",
                    "unit": "%"
                  }
                ]
              }
            }
          ]
        }
      ],
      "testParameters": {
        "packetSize": 256,
        "classOfService": [
          "LOW",
          "MEDIUM",
          "HIGH"
        ]
      }
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` EE NPT STATISTICAL COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestEENPTSTATISTICAL}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "7141e7b0-32f0-4e90-a3e4-16292daab51d",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "OVC000000000001",
        "type": "OVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001013",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NPT Statistical",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "serviceDetails": [
                {
                  "id": "High",
                  "value": "100"
                },
                {
                  "id": "Medium",
                  "value": "100"
                },
                {
                  "id": "Low",
                  "value": "100"
                },
                {
                  "id": "Route Type",
                  "value": "Local"
                }
              ],
              "cosProfile": [
                {
                  "id": "Frame Delay Status",
                  "value": "Green"
                },
                {
                  "id": "Frame Loss Status",
                  "value": "Green"
                },
                {
                  "id": "Jitter Status",
                  "value": "Green"
                }
              ]
            }
          ]
        }
      ],
      "testParameters": {
        "classOfService": "LOW",
        "startDateTime": "2021-04-22T12:53:44Z",
        "endDateTime": "2021-04-22T13:53:44Z"
      }
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` EE UNI-E PORT RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestEEUNIEPORTRESET}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "42da9482-d0b9-43d0-a965-c533eefa6d43",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "OVC000000000001",
        "type": "OVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002015",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "UNI-E Port Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` EE UNI-E STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestEEUNIESTATUS}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "3e17fb41-3e56-4643-aa6d-a8b6ebab36c9",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "OVC000000000001",
        "type": "OVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001012",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "UNI-E Status",
          "status": "Completed",
          "result": "Failed",
          "testMeasure": [
            {
              "port": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Admin State",
                    "value": "up"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Operational State",
                    "value": "down"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Link State",
                    "value": "No"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Last State Change Date",
                    "value": "08/13/2021 11:29:40"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Port Type",
                    "value": ""
                  }
                ]
              },
              "performance": {
                "measurements": [
                  {
                    "@type": "PerformanceIndicator",
                    "id": "Operational Speed",
                    "unit": "Gbps",
                    "value": "N/A"
                  },
                  {
                    "@type": "PerformanceIndicator",
                    "id": "Config Speed",
                    "unit": "Gbps",
                    "value": "1 Gbps"
                  },
                  {
                    "@type": "PerformanceIndicator",
                    "id": "Operational Duplex",
                    "value": "N/A"
                  },
                  {
                    "@type": "PerformanceIndicator",
                    "id": "Config Duplex",
                    "value": "full"
                  }
                ]
              }
            }
          ],
          "errors": [
            {
              "code": "CO210",
              "reason": "No Ethernet carrier detected. Please check CPE connectivity to the NTD."
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` LINE QUALITY DIAGNOSTIC COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestLineQualityDiagnostic}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "3f8e0eba-f640-4797-a445-57160ed0046c",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002001",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Line Quality Diagnostic",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "performance": {
                "monitoringPeriod": "21613.363",
                "measurements": [
                  {
                    "@type": "Upstream",
                    "id": "Layer 2 Bitrate",
                    "unit": "kbps",
                    "value": "21218.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Layer 2 Bitrate",
                    "unit": "kbps",
                    "value": "51217.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Actual Net Data Rate",
                    "unit": "kbps",
                    "value": "21606.4"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Actual Net Data Rate",
                    "unit": "kbps",
                    "value": "53768.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Attainable Net Data Rate",
                    "unit": "kbps",
                    "value": "28296.8"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Attainable Net Data Rate",
                    "unit": "kbps",
                    "value": "67753.2"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Actual Expected Throughput",
                    "unit": "kbps",
                    "value": "21388.4"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Actual Expected Throughput",
                    "unit": "kbps",
                    "value": "53226.5"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Attainable Expected Throughput",
                    "unit": "kbps",
                    "value": "27964.4"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Attainable Expected Throughput",
                    "unit": "kbps",
                    "value": "66958.7"
                  },
                  {
                    "@type": "Upstream",
                    "id": "FEC Actual Bitrate",
                    "unit": "kbps",
                    "value": "N/A"
                  },
                  {
                    "@type": "Downstream",
                    "id": "FEC Actual Bitrate",
                    "unit": "kbps",
                    "value": "N/A"
                  },
                  {
                    "@type": "Upstream",
                    "id": "FEC Attainable Bitrate",
                    "unit": "kbps",
                    "value": "N/A"
                  },
                  {
                    "@type": "Downstream",
                    "id": "FEC Attainable Bitrate",
                    "unit": "kbps",
                    "value": "N/A"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Actual PSD",
                    "unit": "dBm/Hz",
                    "value": "-60.2"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Actual PSD",
                    "unit": "dBm/Hz",
                    "value": "-55.1"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Noise Margin Average",
                    "unit": "dB",
                    "value": "17.3"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Noise Margin Average",
                    "unit": "dB",
                    "value": "16.8"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Relative Capacity Occupation",
                    "unit": "%",
                    "value": "75.7"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Relative Capacity Occupation",
                    "unit": "%",
                    "value": "81.9"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Output Power",
                    "unit": "dBm",
                    "value": "6.7"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Output Power",
                    "unit": "dBm",
                    "value": "14.3"
                  },
                  {
                    "@type": "Upstream",
                    "id": "User Traffic",
                    "unit": "Kbits",
                    "value": "10.7"
                  },
                  {
                    "@type": "Downstream",
                    "id": "User Traffic",
                    "unit": "Kbits",
                    "value": "460.7"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "5.8"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "127.0"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "18.9"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "17.3"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "32.9"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "23.4"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "127.0"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "49.8"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "6.0"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "127.0"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "21.0"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "13.5"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "34.5"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "23.6"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "127.0"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "49.8"
                  }
                ]
              },
              "serviceStateHistory": {
                "timestamp": "2021-02-11T06:01:11Z",
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Service Stability",
                    "value": "STABLE"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Fallback State",
                    "value": "Not Active"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Physical Profile",
                    "value": "50/20 Stable 12dB"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Vectoring Status",
                    "value": "Enabled both US and DS"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Actual Rtx Mode",
                    "value": "In Use"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Actual Rtx Mode",
                    "value": "In use"
                  }
                ]
              },
              "copperPair": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Estimated DELT Distance",
                    "unit": "m",
                    "value": "385"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Electrical Length",
                    "unit": "dB",
                    "value": "7.9"
                  }
                ]
              },
              "cpe": {
                "name": "Registered - Sagemcom F@st 3864 FW 8.342.3",
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Modem Vendor Id",
                    "unit": "hexadecimal",
                    "value": "B5004244434D0000"
                  },
                  {
                    "@type": "Indicator",
                    "id": "System Vendor Model",
                    "unit": "hexadecimal",
                    "value": "41327076364630333970000000000000"
                  },
                  {
                    "@type": "Indicator",
                    "id": "System Vendor Id",
                    "unit": "hexadecimal",
                    "value": "B5004244434D0000"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Serial Number",
                    "unit": "hexadecimal",
                    "value": "Not Available"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Actual CPE Vectoring Type",
                    "value": "g.vector capable"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Supported CPE Vectoring Type",
                    "value": [
                      "legacy",
                      "g.vector capable"
                    ]
                  }
                ]
              }
            }
          ]
        }
      ],
      "testParameters": {
        "resynchroniseType": "NO_INIT",
        "monitoringDuration": 60
      }
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` LINE STATE DIAGNOSTIC COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestLineStateDiagnostic}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "cb73145e-693a-440c-afe7-be51387a15d3",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001004",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Line State Diagnostic",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "serviceDetails": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "DSL Mode",
                    "value": "VDSL2"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Detected MAC Address 1",
                    "value": "315F36325F38353132204D5435333131"
                  }
                ]
              },
              "performance": {
                "measurements": [
                  {
                    "@type": "Upstream",
                    "id": "RTX Actual Net Data Rate",
                    "unit": "kbps",
                    "value": "11800.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Actual Net Data Rate",
                    "unit": "kbps",
                    "value": "30208.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Attainable Net Data Rate",
                    "unit": "kbps",
                    "value": "41978.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Attainable Net Data Rate",
                    "unit": "kbps",
                    "value": "144088.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Actual Expected Throughput",
                    "unit": "kbps",
                    "value": "11681.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Actual Expected Throughput",
                    "unit": "kbps",
                    "value": "29854.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "RTX Attainable Expected Throughput",
                    "unit": "kbps",
                    "value": "41553.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "RTX Attainable Expected Throughput",
                    "unit": "kbps",
                    "value": "142632.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Actual PSD",
                    "unit": "dBm/Hz",
                    "value": "-81.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Actual PSD",
                    "unit": "dBm/Hz",
                    "value": "-57.3"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Noise Margin Average",
                    "unit": "dB",
                    "value": "19.6"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Noise Margin Average",
                    "unit": "dB",
                    "value": "27.3"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Relative Capacity Occupation",
                    "unit": "Percentage",
                    "value": "44.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Relative Capacity Occupation",
                    "unit": "Percentage",
                    "value": "41.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "Output Power",
                    "unit": "dBm",
                    "value": "-14.0"
                  },
                  {
                    "@type": "Downstream",
                    "id": "Output Power",
                    "unit": "dBm",
                    "value": "12.0"
                  },
                  {
                    "@type": "Upstream",
                    "id": "User Traffic",
                    "unit": "Kbits",
                    "value": "2147483647"
                  },
                  {
                    "@type": "Downstream",
                    "id": "User Traffic",
                    "unit": "Kbits",
                    "value": "2147483647"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "0.0"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "N/A"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "0.8"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "3.8"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "0.0"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "4.0"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Upstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "N/A"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Downstream",
                    "id": "Attenuation Average",
                    "unit": "dB",
                    "value": "7.3"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "0.0"
                  },
                  {
                    "name": "BAND_ID0",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "N/A"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "0.0"
                  },
                  {
                    "name": "BAND_ID1",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "2.3"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "0.0"
                  },
                  {
                    "name": "BAND_ID2",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "3.0"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Upstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "N/A"
                  },
                  {
                    "name": "BAND_ID3",
                    "@type": "Downstream",
                    "id": "Loop Attenuation Average",
                    "unit": "dB",
                    "value": "6.2"
                  }
                ]
              },
              "serviceStateHistory": {
                "timestamp": "2021-03-03T09:59:40Z",
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Operational Status",
                    "value": "Up"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Service Stability",
                    "value": "STABLE"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Fallback State",
                    "value": "Not Active"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Physical Profile",
                    "value": "25/10 Standard 6dB"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Vectoring Status",
                    "value": "Enabled"
                  }
                ]
              },
              "copperPair": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Estimated DELT Distance",
                    "unit": "metre",
                    "value": "24"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Electrical Length",
                    "unit": "dB",
                    "value": "0.5"
                  }
                ]
              },
              "cpe": {
                "name": "Netcomm",
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Modem Vendor Id",
                    "unit": "hexadecimal",
                    "value": "B5004D4554417502"
                  },
                  {
                    "@type": "Indicator",
                    "id": "System Vendor Model",
                    "unit": "hexadecimal",
                    "value": "315F36325F38353132204D5435333131"
                  },
                  {
                    "@type": "Indicator",
                    "id": "System Vendor Id",
                    "unit": "hexadecimal",
                    "value": "B5004D4554410000"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Serial Number",
                    "unit": "hexadecimal",
                    "value": "000EAD334455 SFP-V5311-T-R 8512"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Actual CPE Vectoring Type",
                    "value": "g.vector capable"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Supported CPE Vectoring Type",
                    "value": "legacy,g.vector capable"
                  }
                ]
              }
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` LOOPBACK COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestLoopback}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "c7689912-161b-4ba7-a55e-30abbef3b654",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002002",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Loopback",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` NCD PORT RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestNCDPortReset}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "a8bb3133-9e4a-4d27-a277-518cf63baf7e",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002004",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NCD Port Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` NCD RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestNCDReset}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "e1997eda-c21f-4250-ae50-336bc10c0a1b",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002005",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NCD Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` NCD UNI-D STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestNCDUNIDStatus}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "0206d483-4255-467d-aabc-1a5f8420447d",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001005",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NCD UNI-D Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "type": "NCD UNI-D Measure",
              "ethernetPortOperationalState": "Up",
              "ethernetPortCurrentBitRate": "1000",
              "ethernetPortMaxBitRate": "Auto",
              "ethernetPortCurrentDuplexMode": "Full",
              "ethernetPortConfiguredDuplexMode": "Auto"
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` NTD RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestNTDReset}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "c2c0159e-2080-4278-a896-7162b98f48ba",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002006",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NTD Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` NHAS NTD STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestNHASNTDSTATUS}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "0d52c2dd-1917-4cf7-a269-54cef5030f88",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001017",
        "version": "1.1"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NTD Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "ntd": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "MAC Address",
                    "value": "c8:63:fc:8f:bd:c9"
                  },
                  {
                    "@type": "Indicator",
                    "id": "NTD Up Time",
                    "value": "235759"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Link State",
                    "value": "up"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Link Type",
                    "value": "ETHERNET"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Total Flaps",
                    "value": "2"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Last Flap Time",
                    "value": "2020-11-24T19:55:30.980+11:00"
                  },
                  {
                    "@type": "Indicator",
                    "id": "NTD Revision Timestamp",
                    "value": "2020-11-24T19:55:30.980+11:00"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Operational Speed",
                    "unit": "Mbps",
                    "value": "100"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Duplex",
                    "value": "Full Duplex"
                  }
                ]
              }
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` NFAS NTD STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestNFASNTDSTATUS}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "46dc4ec5-fc16-4c50-a4ee-2922b5ca1465",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001007",
        "version": "1.1"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "NTD Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "ntd": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Serial Number",
                    "value": "ALCLF8A9F146"
                  },
                  {
                    "@type": "Indicator",
                    "id": "NTD ID",
                    "value": "NTD000000000001"
                  },
                  {
                    "@type": "Indicator",
                    "id": "ENNI CVLAN",
                    "value": "20"
                  },
                  {
                    "@type": "Indicator",
                    "id": "ENNI SVLAN",
                    "value": "3501"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Operational State",
                    "value": "UP"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Reporting State",
                    "value": "No Defect"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Backup Power Type",
                    "value": "Battery"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Battery Fail Status",
                    "value": "N/A"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Battery Missing Status",
                    "value": "N/A"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Battery Install Date",
                    "value": ""
                  },
                  {
                    "@type": "Indicator",
                    "id": "NTD Revision Timestamp",
                    "value": "2021-02-17T00:19:05Z"
                  }
                ]
              }
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` PORT RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestPortReset}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "c05c058b-f5dd-401c-aa61-5d180e8eea8c",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002008",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Port Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` SINGLE END LINE TEST COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestSingleEndLineTest}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "ec7b95e9-c32e-4f3e-a391-f3a54c5d427e",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002009",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "Single End Line Test",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "estimatesLoop": {
                "measurements": [
                  {
                    "@type": "Indicator",
                    "id": "Line Length",
                    "unit": "metre",
                    "value": "1675"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Line Length Accuracy",
                    "unit": "metre",
                    "value": "170"
                  },
                  {
                    "@type": "Indicator",
                    "id": "Termination Type",
                    "value": "POWERED_UP_CPE"
                  }
                ],
                "performance": {
                  "measurements": [
                    {
                      "@type": "PerformanceIndicator",
                      "id": "Synch Status Before",
                      "value": "Up"
                    },
                    {
                      "@type": "PerformanceIndicator",
                      "id": "Reliability",
                      "value": [
                        "10%",
                        "Low"
                      ]
                    },
                    {
                      "@type": "PerformanceIndicator",
                      "id": "Estimated Attenuation At 300Khz SELT",
                      "unit": "dB",
                      "value": "24.2"
                    },
                    {
                      "@type": "PerformanceIndicator",
                      "id": "Estimated Attenuation At 1Mhz SELT",
                      "unit": "dB",
                      "value": "41.4"
                    },
                    {
                      "@type": "CapacityIndicator",
                      "id": "capacityProfile",
                      "value": "VDSLoPOTS"
                    },
                    {
                      "@type": "CapacityIndicator",
                      "id": "Upstream",
                      "unit": "kbps",
                      "value": "Not Available"
                    },
                    {
                      "@type": "CapacityIndicator",
                      "id": "Downstream",
                      "unit": "kbps",
                      "value": "Not Available"
                    },
                    {
                      "@type": "CapacityIndicator",
                      "id": "UpstreamAccuracy",
                      "unit": "kbps",
                      "value": "Not Available"
                    },
                    {
                      "@type": "CapacityIndicator",
                      "id": "DownstreamAccuracy",
                      "unit": "kbps",
                      "value": "Not Available"
                    }
                  ]
                }
              },
              "serviceProblem": {
                "problemDescription": "",
                "impactImportanceFactor": "",
                "details": [
                  {
                    "@type": "Indicator",
                    "id": "Problem Area",
                    "value": ""
                  },
                  {
                    "@type": "Indicator",
                    "id": "Confidence Level",
                    "unit": "Percentage",
                    "value": ""
                  },
                  {
                    "@type": "Indicator",
                    "id": "Proposed Repair Actions",
                    "value": ""
                  }
                ]
              }
            }
          ]
        }
      ],
      "testParameters": {
        "forceMeasurement": true
      }
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` UNI-D STATUS COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestUNIDStatus}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "66129c7b-3723-46f2-aa84-a98ad584de7d",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001008",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "UNI-D Status",
          "status": "Completed",
          "result": "Passed",
          "testMeasure": [
            {
              "type": "UNI-D Measure",
              "unidEthernetPortNumber": "2",
              "unidEthernetPortOperationalState": "Up",
              "unidEthernetPortLinkState": "Carrier detected",
              "unidVlanMacAddress": "08:36:C9:20:92:41",
              "unidEthernetPortCurrentState": "No Defect",
              "unidEthernetConfigStatus": "GigE (full duplex)"
            }
          ]
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` WNTD RESET COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestWNTDReset}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "d36771f6-2e85-44a9-ad42-ba82f6b956fc",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000002011",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "WNTD Reset",
          "status": "Completed",
          "result": "Passed"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` WNTD MEASURE COMPLETED CALLBACK

**URI:** `/example-service-test-callback-url{?apiaryServiceTestWNTDMeasure}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "f57e1061-c00c-467a-a62d-621cae5072fb",
  "eventTime": "2021-10-26T01:44:46Z",
  "eventType": "ServiceTestStateChangeNotification",
  "event": {
    "id": "VTTST000000000001",
    "objectType": "ServiceTestEvent",
    "externalId": "VTTST000000000001",
    "status": "Completed",
    "notificationType": "TestCompleted",
    "serviceTest": {
      "serviceRef": {
        "id": "AVC000000000001",
        "type": "AVC"
      },
      "testSpecificationRef": {
        "id": "TST000000001010",
        "version": "1.0"
      },
      "executionDate": {
        "startDateTime": "2021-10-26T01:44:46Z",
        "endDateTime": "2021-10-26T01:44:46Z"
      },
      "serviceTestResults": [
        {
          "type": "WNTD Measure",
          "status": "Completed",
          "ntdId": "NTD000044774149",
          "ntdOperationalState": "ONLINE",
          "ntdVersion": "V3",
          "cellId": "76902915"
        }
      ]
    },
    "reason": "Success",
    "reasonCode": "1"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`
