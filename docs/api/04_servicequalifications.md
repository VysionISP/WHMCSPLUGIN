# Service Qualifications

## Service Qualifications


### Normal SQ

**URI:** `/service-qualifications/{locationId}{?apiaryNormalSQ}`

Required scope: read:service-qualifications


#### `GET` GET - FTTP SERVICE CLASS 0 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTPSERVICECLASS0NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "0",
      "serviceabilityClassReason": "Plan Pending nbn",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    }
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nfas"
}
```


#### `GET` GET - FTTP SERVICE CLASS 1

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTPSERVICECLASS1}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "1",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NFAS",
        "version": "2.0.0",
        "multicast": true,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 90,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 80,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 70,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 100,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 60,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 50,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true,
        "TR069UNIV": true,
        "FTPUNIV": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC4500D200U",
    "TC41000D50U",
    "TC41000D100U",
    "TC41000D400U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC4500D200U",
    "L3TC41000D50U",
    "L3TC41000D100U",
    "L3TC41000D400U"
  ],
  "serviceType": "nfas"
}
```


#### `GET` GET - FTTP SERVICE CLASS 2

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTPSERVICECLASS2}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "2",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NFAS",
        "version": "2.0.0",
        "multicast": true,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 90,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 80,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 70,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 100,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 60,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 50,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true,
        "TR069UNIV": true,
        "FTPUNIV": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC4500D200U",
    "TC41000D50U",
    "TC41000D100U",
    "TC41000D400U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC4500D200U",
    "L3TC41000D50U",
    "L3TC41000D100U",
    "L3TC41000D400U"
  ],
  "serviceType": "nfas"
}
```


#### `GET` GET - FTTP SERVICE CLASS 3

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTPSERVICECLASS3}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "3",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NFAS",
        "version": "2.0.0",
        "multicast": true,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 90,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 80,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 70,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 100,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 60,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 50,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true,
        "TR069UNIV": true,
        "FTPUNIV": false
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158242",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          },
          {
            "id": "1-UNI-D2",
            "status": "Free"
          },
          {
            "id": "1-UNI-D3",
            "status": "Free"
          },
          {
            "id": "1-UNI-D4",
            "status": "Free"
          }
        ],
        "uniPortV": [
          {
            "id": "1-UNI-V1",
            "status": "Free"
          },
          {
            "id": "1-UNI-V2",
            "status": "Free"
          }
        ],
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "NTDBatteryBackup": {
          "batteryPowerUnit": true,
          "powerSupplywithBatteryBackupInstallDate": "2020-06-12T01:06:31Z",
          "batteryPowerUnitMonitored": "ENABLED"
        }
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC4500D200U",
    "TC41000D50U",
    "TC41000D100U",
    "TC41000D400U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC4500D200U",
    "L3TC41000D50U",
    "L3TC41000D100U",
    "L3TC41000D400U"
  ],
  "serviceType": "nfas"
}
```


#### `GET` GET - FIXED WIRELESS SERVICE CLASS 4 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFIXEDWIRELESSSERVICECLASS4NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless",
      "serviceabilityClass": "4",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Minor Rural",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NWAS",
        "version": "2.0.0"
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nwas"
}
```


#### `GET` GET - FIXED WIRELESS SERVICE CLASS 5

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFIXEDWIRELESSSERVICECLASS5}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless",
      "serviceabilityClass": "5",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Remote",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NWAS",
        "version": "2.0.0",
        "speedTierAvailability": [
          "FW Home Fast",
          "FW Superfast"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC4FWP",
    "TC4FWHF",
    "TC4FWSF",
    "L3TC425D5U",
    "L3TC4FWP",
    "L3TC4FWHF",
    "L3TC4FWSF"
  ],
  "serviceType": "nwas"
}
```


#### `GET` GET - FIXED WIRELESS SERVICE CLASS 6

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFIXEDWIRELESSSERVICECLASS6}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless",
      "serviceabilityClass": "6",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Remote",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NWAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 300,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 450,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 600,
            "unitOfMeasure": "Kbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "FW Home Fast",
          "FW Superfast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158513",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          },
          {
            "id": "1-UNI-D2",
            "status": "Free"
          },
          {
            "id": "1-UNI-D3",
            "status": "Free"
          },
          {
            "id": "1-UNI-D4",
            "status": "Free"
          }
        ],
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "speedTiersSupported": [
          {
            "featureType": "FW Home Fast",
            "supported": true
          },
          {
            "featureType": "FW Superfast",
            "supported": true
          }
        ]
      }
    ],
    "notes": [
      {
        "code": "NTD0005",
        "reason": "A WNTD upgrade appointment is required to access Fixed Wireless Superfast",
        "relatedTo": "#/siteRestriction/supportingResource/id['NTD999100158513']"
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC4FWP",
    "TC4FWHF",
    "TC4FWSF",
    "L3TC425D5U",
    "L3TC4FWP",
    "L3TC4FWHF",
    "L3TC4FWSF"
  ],
  "serviceType": "nwas"
}
```


#### `GET` GET - SATELLITE SC7 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseSATELLITESC7NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite",
      "serviceabilityClass": "7",
      "serviceabilityClassReason": "Plan Pending nbn",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Major Rural",
      "CSAId": "CSA100000011053"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NSAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC4",
            "capacity": 25,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "highSpeedNotLessThan": true
          }
        ],
        "TR069UNIV": false,
        "FTPUNIV": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nsas"
}
```


#### `GET` GET - SATELLITE SC8

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseSATELLITESC8}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite",
      "serviceabilityClass": "8",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Major Rural",
      "AETLocation": false,
      "CSAId": "CSA100000011053"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false,
      "antennaDishSize": "80"
    },
    "supportingProductFeatures": [
      {
        "type": "NSAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC4",
            "capacity": 25,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "highSpeedNotLessThan": true
          }
        ],
        "TR069UNIV": false,
        "FTPUNIV": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nsas"
}
```


#### `GET` GET - SATELLITE SC9

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseSATELLITESC9}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite",
      "serviceabilityClass": "9",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Minor Rural",
      "CSAId": "CSA100000011053"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NSAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC4",
            "capacity": 25,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": false
          },
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "highSpeedNotLessThan": true
          }
        ],
        "TR069UNIV": false,
        "FTPUNIV": false
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158521",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          },
          {
            "id": "1-UNI-D2",
            "status": "Free"
          },
          {
            "id": "1-UNI-D3",
            "status": "Free"
          },
          {
            "id": "1-UNI-D4",
            "status": "Free"
          }
        ],
        "antennaDishSize": "80 cm",
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "NTDBandwidth": [
          {
            "bandwidthType": "RemainingBandwidth",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nsas"
}
```


#### `GET` GET - HFC SC20 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseHFCSC20NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "20",
      "serviceabilityClassReason": "Plan Pending nbn",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": false,
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": false
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ],
        "TC2": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nhas"
}
```


#### `GET` GET - HFC SC21

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseHFCSC21}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "21",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": false,
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC41000D50U",
    "TC41000D100U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC41000D50U",
    "L3TC41000D100U"
  ],
  "serviceType": "nhas"
}
```


#### `GET` GET - HFC SC22

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseHFCSC22}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "22",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": false,
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC41000D50U",
    "TC41000D100U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC41000D50U",
    "L3TC41000D100U"
  ],
  "serviceType": "nhas"
}
```


#### `GET` GET - HFC SC23

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseHFCSC23}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "23",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": false,
      "newDevelopmentsChargeApplies": false,
      "selfAndRSPProfessionalInstallEligible": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC41000D50U",
    "TC41000D100U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC41000D50U",
    "L3TC41000D100U"
  ],
  "serviceType": "nhas"
}
```


#### `GET` GET - HFC SC24

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseHFCSC24}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "24",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": true,
      "newDevelopmentsChargeApplies": false,
      "selfAndRSPProfessionalInstallEligible": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true
      }
    ],
    "supportingResource": [
      {
        "id": "NTD400500043901",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC41000D50U",
    "TC41000D100U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC41000D50U",
    "L3TC41000D100U"
  ],
  "serviceType": "nhas"
}
```


#### `GET` GET - FTTB SERVICE CLASS 10 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTBSERVICECLASS10NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "10",
      "serviceabilityClassReason": "Not to be Serviced by nbn",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingResource": [
      {
        "networkCoexistence": true
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTB SERVICE CLASS 12

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTBSERVICECLASS12}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "12",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143105",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "12",
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Active",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 75.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTB SERVICE CLASS 13

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTBSERVICECLASS13}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "13",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143106",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "13",
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 75.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use"
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 10 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTNSERVICECLASS10NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "10",
      "serviceabilityClassReason": "Plan Pending nbn",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingResource": [
      {
        "networkCoexistence": true
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 11

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTNSERVICECLASS11}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "11",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000000770",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 12 ACTIVE

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTNSERVICECLASS12ACTIVE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "12",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143109",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "12",
        "remediationRequired": true,
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Active",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 13

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTNSERVICECLASS13}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "13",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143110",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "13",
        "remediationRequired": true,
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 38.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use"
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 30 (NOT ORDERABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTCSERVICECLASS30NOTORDERABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Rejected",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "30",
      "serviceabilityClassReason": "Plan Pending nbn",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    }
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 31

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTCSERVICECLASS31}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "31",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false,
      "MDU": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 32

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTCSERVICECLASS32}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "32",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false,
      "MDU": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143118",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "32",
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Active",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 70.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 33

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTCSERVICECLASS33}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "33",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false,
      "MDU": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143116",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "33",
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Active",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 70.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 34

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTCSERVICECLASS34}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "34",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false,
      "MDU": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012138148",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "34",
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 70.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use"
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


### Enhanced SQ - POTS Interconnect

**URI:** `/service-qualifications/{locationId}{?apiaryPotsSQ}`

For NCAS (FTTB/FTTN/FTTC) locations with copper pairs that haven't yet transitioned to NBN,
(i.e. they have a `copperPairStatus` instead of an `NBNServiceStatus`)
you can provide the `POTSInterconnect` parameter to help you identify the correct copper
pair for your order.

If provided, `POTSInterconnect` should be set to the Full National Number (FNN) or
Unconditioned Local Loop Identifier (ULL ID). In the response, each copper pair will contain the
boolean `POTSInterconnectMatch` indicating the match outcome.

Required scope: read:service-qualifications


#### `GET` GET - FTTB SERVICE CLASS 12 ACTIVE (POTS MATCH FALSE)

**URI:** `/service-qualifications/{locationId}{?POTSInterconnect,apiarySQResponseFTTBSERVICECLASS12ACTIVEPOTSMATCHFALSE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | ID for FNN, FNN - Special, ULL or ULL - Special | 0312345678 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "12",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.1.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143105",
        "type": "CopperLineResource",
        "version": "2.1.0",
        "networkCoexistence": true,
        "serviceabilityClass": "12",
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Active",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 75.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "POTSInterconnectType": "FNN",
        "POTSInterconnectMatch": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTB SERVICE CLASS 12 ACTIVE (POTS MATCH TRUE)

**URI:** `/service-qualifications/{locationId}{?POTSInterconnect,apiarySQResponseFTTBSERVICECLASS12ACTIVEPOTSMATCHTRUE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | ID for FNN, FNN - Special, ULL or ULL - Special | 0312345678 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "12",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.1.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143105",
        "type": "CopperLineResource",
        "version": "2.1.0",
        "networkCoexistence": true,
        "serviceabilityClass": "12",
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Active",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 75.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "POTSInterconnectType": "FNN",
        "POTSInterconnectMatch": true
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


### Enhanced SQ - Churn Validation

**URI:** `/service-qualifications/{locationId}{?apiaryChurnValidationSQ}`

Once you have permission from the end user to place a churn order on their behalf, you can run an
Enhanced SQ to locate the correct Copper Pair or NTD ID & UNI-D Port.

You'll need the AVC ID of the existing service (either in full e.g. 'AVC123456789012',
or the last five digits e.g. '89012'). You will have to get this from the end user (who will get it
from their existing RSP).

To perform the SQ, you must include the `serviceID` parameter (with the full/partial AVC ID)
and the `customerAuthorityDate` parameter (with the date the end user gave you permission to place
the order, in YYYY-MM-DD format).

The response will include the `serviceIDMatch` boolean with all UNI-D ports and Copper Pairs.
If the AVC ID you supplied matches the service on that UNI-D port or Copper Pair,  `serviceIDMatch`
will be set to true.

If there are active services at the location, the response will also contain the `supportingProduct`
array, (under `siteRestriction`), which contains other details about those services.
Please note that the data in the `supportingProduct` array is sensitive cannot be disclosed to
end users for privacy reasons.
This information is only meant for internal use by your staff and systems.

If the AVC ID you supplied does not match any Copper Pairs or UNI ports, the
`siteRestriction`.`siteRestrictionError` object will be present in the response
with one of the following code & message pairs to explain why:

code
message

RJ002003
The provided AVC does not exist: %serviceID%

RJ002004
The provided AVC has recently been disconnected: %serviceID%

RJ002005
The provided AVC exists at a different location: %serviceID%

RJ002006
The provided AVC has recently been disconnected at a different location: %serviceID%

Required scope: read:service-qualifications


#### `GET` GET - FTTP SERVICE CLASS 3 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseFTTPSERVICECLASS3ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "3",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NFAS",
        "version": "2.0.0",
        "multicast": true,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 90,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 80,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 70,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 100,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 60,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 50,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true,
        "TR069UNIV": true,
        "FTPUNIV": false
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158242",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used",
            "serviceIDMatch": true
          },
          {
            "id": "1-UNI-D2",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-D3",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-D4",
            "status": "Free",
            "serviceIDMatch": false
          }
        ],
        "uniPortV": [
          {
            "id": "1-UNI-V1",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-V2",
            "status": "Free",
            "serviceIDMatch": false
          }
        ],
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "NTDBatteryBackup": {
          "batteryPowerUnit": true,
          "powerSupplywithBatteryBackupInstallDate": "2020-06-12T01:06:31Z",
          "batteryPowerUnitMonitored": "ENABLED"
        }
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110614428",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD999100158242']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC4500D200U",
    "TC41000D50U",
    "TC41000D100U",
    "TC41000D400U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC4500D200U",
    "L3TC41000D50U",
    "L3TC41000D100U",
    "L3TC41000D400U"
  ],
  "serviceType": "nfas"
}
```


#### `GET` GET - FIXED WIRELESS SERVICE CLASS 6 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseFIXEDWIRELESSSERVICECLASS6ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless",
      "serviceabilityClass": "6",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Remote",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NWAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 300,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 450,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 600,
            "unitOfMeasure": "Kbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "FW Home Fast",
          "FW Superfast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158513",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used",
            "serviceIDMatch": true
          },
          {
            "id": "1-UNI-D2",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-D3",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-D4",
            "status": "Free",
            "serviceIDMatch": false
          }
        ],
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "speedTiersSupported": [
          {
            "featureType": "FW Home Fast",
            "supported": true
          },
          {
            "featureType": "FW Superfast",
            "supported": true
          }
        ]
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110615766",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD999100158513']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ],
    "notes": [
      {
        "code": "NTD0005",
        "reason": "A WNTD upgrade appointment is required to access Fixed Wireless Superfast",
        "relatedTo": "#/siteRestriction/supportingResource/id['NTD999100158513']"
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC4FWP",
    "TC4FWHF",
    "TC4FWSF",
    "L3TC425D5U",
    "L3TC4FWP",
    "L3TC4FWHF",
    "L3TC4FWSF"
  ],
  "serviceType": "nwas"
}
```


#### `GET` GET - SATELLITE SC9 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseSATELLITESC9ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite",
      "serviceabilityClass": "9",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Minor Rural",
      "CSAId": "CSA100000011053"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NSAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC4",
            "capacity": 25,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": false
          },
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "highSpeedNotLessThan": true
          }
        ],
        "TR069UNIV": false,
        "FTPUNIV": false
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158521",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used",
            "serviceIDMatch": true
          },
          {
            "id": "1-UNI-D2",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-D3",
            "status": "Free",
            "serviceIDMatch": false
          },
          {
            "id": "1-UNI-D4",
            "status": "Free",
            "serviceIDMatch": false
          }
        ],
        "antennaDishSize": "80 cm",
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "NTDBandwidth": [
          {
            "bandwidthType": "RemainingBandwidth",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          }
        ]
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110615778",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD999100158521']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nsas"
}
```


#### `GET` GET - HFC SC24 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseHFCSC24ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "24",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": true,
      "newDevelopmentsChargeApplies": false,
      "selfAndRSPProfessionalInstallEligible": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true
      }
    ],
    "supportingResource": [
      {
        "id": "NTD400500043901",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used",
            "serviceIDMatch": true
          }
        ]
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI400500053006",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD400500043901']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC41000D50U",
    "TC41000D100U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC41000D50U",
    "L3TC41000D100U"
  ],
  "serviceType": "nhas"
}
```


#### `GET` GET - FTTB SERVICE CLASS 13 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseFTTBSERVICECLASS13ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "13",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143106",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "13",
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 75.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use",
        "serviceIDMatch": true
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110615811",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['CPI300012143106']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 13 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseFTTNSERVICECLASS13ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "13",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143110",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "13",
        "remediationRequired": true,
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 38.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use",
        "serviceIDMatch": true
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110616529",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['CPI300012143110']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 34 (Churn Validation SQ)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,serviceID,apiarySQResponseFTTCSERVICECLASS34ChurnValidationSQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | AVC ID of the existing service (either in full e.g. 'AVC0123456789012' or the last 5 digits e.g. '89012') to check against any UNI-D ports and Copper Pairs at the location. Obtained from the end user  | AVC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "34",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false,
      "MDU": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012138148",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "34",
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 70.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use",
        "serviceIDMatch": true
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110616435",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['CPI300012138148']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


### Enhanced SQ - Customer Authority Date

**URI:** `/service-qualifications/{locationId}{?apiaryCustomerAuthoritySQ}`

This has been deprecated by the Churn Validation SQ above, however it's still available
if you want to use it.

Once you have permission from the end user to place an order on their behalf, you can run an
Enhanced SQ to locate the correct copper pair or NTD ID & UNI-D Port.

To do this, you supply the `customerAuthorityDate` parameter with your request.
If there are active services at the location, the response will also contain the `supportingProduct`
array, (under `siteRestriction`), which contains details about those services.

Please note that the data in the `supportingProduct` array is sensitive cannot be disclosed to
end users. This information is only meant for internal use by your staff and systems.

Required scope: read:service-qualifications


#### `GET` GET - FTTP SERVICE CLASS 3 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseFTTPSERVICECLASS3withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "3",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NFAS",
        "version": "2.0.0",
        "multicast": true,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 90,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 80,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 70,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 100,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 60,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 50,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true,
        "TR069UNIV": true,
        "FTPUNIV": false
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158242",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          },
          {
            "id": "1-UNI-D2",
            "status": "Free"
          },
          {
            "id": "1-UNI-D3",
            "status": "Free"
          },
          {
            "id": "1-UNI-D4",
            "status": "Free"
          }
        ],
        "uniPortV": [
          {
            "id": "1-UNI-V1",
            "status": "Free"
          },
          {
            "id": "1-UNI-V2",
            "status": "Free"
          }
        ],
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "NTDBatteryBackup": {
          "batteryPowerUnit": true,
          "powerSupplywithBatteryBackupInstallDate": "2020-06-12T01:06:31Z",
          "batteryPowerUnitMonitored": "ENABLED"
        }
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110614428",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD999100158242']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC4500D200U",
    "TC41000D50U",
    "TC41000D100U",
    "TC41000D400U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC4500D200U",
    "L3TC41000D50U",
    "L3TC41000D100U",
    "L3TC41000D400U"
  ],
  "serviceType": "nfas"
}
```


#### `GET` GET - FIXED WIRELESS SERVICE CLASS 6 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseFIXEDWIRELESSSERVICECLASS6withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless",
      "serviceabilityClass": "6",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Remote",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NWAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 300,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 450,
            "unitOfMeasure": "Kbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 600,
            "unitOfMeasure": "Kbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "FW Home Fast",
          "FW Superfast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158513",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          },
          {
            "id": "1-UNI-D2",
            "status": "Free"
          },
          {
            "id": "1-UNI-D3",
            "status": "Free"
          },
          {
            "id": "1-UNI-D4",
            "status": "Free"
          }
        ],
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "speedTiersSupported": [
          {
            "featureType": "FW Home Fast",
            "supported": true
          },
          {
            "featureType": "FW Superfast",
            "supported": true
          }
        ]
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110615766",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD999100158513']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ],
    "notes": [
      {
        "code": "NTD0005",
        "reason": "A WNTD upgrade appointment is required to access Fixed Wireless Superfast",
        "relatedTo": "#/siteRestriction/supportingResource/id['NTD999100158513']"
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC4FWP",
    "TC4FWHF",
    "TC4FWSF",
    "L3TC425D5U",
    "L3TC4FWP",
    "L3TC4FWHF",
    "L3TC4FWSF"
  ],
  "serviceType": "nwas"
}
```


#### `GET` GET - SATELLITE SC9 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseSATELLITESC9withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite",
      "serviceabilityClass": "9",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Minor Rural",
      "CSAId": "CSA100000011053"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NSAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC4",
            "capacity": 25,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": false
          },
          {
            "featureType": "TC1",
            "capacity": 150,
            "unitOfMeasure": "Kbps",
            "highSpeedNotLessThan": true
          }
        ],
        "TR069UNIV": false,
        "FTPUNIV": false
      }
    ],
    "supportingResource": [
      {
        "id": "NTD999100158521",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          },
          {
            "id": "1-UNI-D2",
            "status": "Free"
          },
          {
            "id": "1-UNI-D3",
            "status": "Free"
          },
          {
            "id": "1-UNI-D4",
            "status": "Free"
          }
        ],
        "antennaDishSize": "80 cm",
        "NTDLocation": "INDOOR",
        "NTDType": "INTERNAL",
        "NTDPowerType": "AC",
        "NTDBandwidth": [
          {
            "bandwidthType": "RemainingBandwidth",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          }
        ]
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110615778",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD999100158521']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [],
  "serviceType": "nsas"
}
```


#### `GET` GET - HFC SC24 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseHFCSC24withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC",
      "serviceabilityClass": "24",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "inHomeAmplifier": true,
      "newDevelopmentsChargeApplies": false,
      "selfAndRSPProfessionalInstallEligible": false
    },
    "supportingProductFeatures": [
      {
        "type": "NHAS",
        "version": "2.0.0",
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true
      }
    ],
    "supportingResource": [
      {
        "id": "NTD400500043901",
        "type": "NTD",
        "version": "2.0.0",
        "uniPortD": [
          {
            "id": "1-UNI-D1",
            "status": "Used"
          }
        ]
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI400500053006",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['NTD400500043901']/uniPortD/id['1-UNI-D1']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC41000D50U",
    "TC41000D100U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC41000D50U",
    "L3TC41000D100U"
  ],
  "serviceType": "nhas"
}
```


#### `GET` GET - FTTB SERVICE CLASS 13 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseFTTBSERVICECLASS13withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building",
      "serviceabilityClass": "13",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Main Distribution Frame",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143106",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "13",
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 75.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use"
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110615811",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['CPI300012143106']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 13 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseFTTNSERVICECLASS13withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "13",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA300000000862",
      "poiId": "0SANDBOX"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012143110",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "13",
        "remediationRequired": true,
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 38.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use"
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110616529",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['CPI300012143110']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTC SERVICE CLASS 34 (with Customer Authority Date)

**URI:** `/service-qualifications/{locationId}{?customerAuthorityDate,apiarySQResponseFTTCSERVICECLASS34withCustomerAuthorityDate}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | When the customer authorised you to place an order on their behalf. Must be less than 45 days old. Format YYYY-MM-DD | 2020-12-01 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb",
      "serviceabilityClass": "34",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA200000010503",
      "poiId": "2LID"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "safetyCriticalServiceAlert": false,
      "MDU": false
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.0.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI300012138148",
        "type": "CopperLineResource",
        "version": "2.0.0",
        "networkCoexistence": true,
        "serviceabilityClass": "34",
        "subsequentInstallationChargeApplies": false,
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 100.0
          },
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 70.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 20.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 40.0
          }
        ],
        "NBNServiceStatus": "Line In Use"
      }
    ],
    "supportingProduct": [
      {
        "id": "PRI999110616435",
        "priorityAssist": "No",
        "serviceProviderId": "1110",
        "resourceRef": [
          "#/siteRestriction/supportingResource/id['CPI300012138148']"
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


### Fibre Upgrade SQ

**URI:** `/service-qualifications/{locationId}{?apiaryNebsCoatSQ}`

When an SQ returns the `alternativeTechnology` key (under `siteRestriction`.`supportingTechnology`)
with the value 'Fibre', the location may be eligible for an on-demand upgrade to NFAS (FTTP).

You can run a 'Fibre SQ' on these locations by including the `productType` parameter
with the value 'NFAS'. The response will be a standard NFAS (FTTP) SQ response, with a service class
of 0, 1, 2, or 3. Only service class 1, 2, and 3 sites will accept fibre upgrade orders.

Required scope: read:service-qualifications


#### `GET` GET - FTTN SERVICE CLASS 13 (ALTERNATE TECHNOLOGY AVAILABLE)

**URI:** `/service-qualifications/{locationId}{?apiarySQResponseFTTNSERVICECLASS13ALTERNATETECHNOLOGYAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node",
      "serviceabilityClass": "12",
      "alternativeTechnology": "Fibre",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA400000010875",
      "poiId": "4TNS"
    },
    "supportingRelatedLocationFeatures": {
      "networkBoundaryPoint": "Telecommunications Outlet",
      "newDevelopmentsChargeApplies": false,
      "nonPremiseLocation": "No"
    },
    "supportingProductFeatures": [
      {
        "type": "NCAS",
        "version": "2.3.0",
        "multicast": false,
        "capacityAvailability": [
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": false
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast"
        ]
      }
    ],
    "supportingResource": [
      {
        "id": "CPI000000000000",
        "type": "CopperLineResource",
        "version": "2.3.0",
        "networkCoexistence": true,
        "serviceabilityClass": "12",
        "remediationRequired": true,
        "subsequentInstallationChargeApplies": false,
        "copperPairStatus": "Inactive",
        "bandwidthRatesSupported": [
          {
            "bandwidthRate": 5,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 10,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 20,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": true
          },
          {
            "bandwidthRate": 30,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          },
          {
            "bandwidthRate": 40,
            "featureType": "TC2",
            "unitOfMeasure": "Mbps",
            "supported": false
          }
        ],
        "copperBandwidthRates": [
          {
            "bandwidthType": "DownstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "DownstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamLowerRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          },
          {
            "bandwidthType": "UpstreamUpperRate",
            "featureType": "TC4",
            "unitOfMeasure": "Mbps",
            "bandwidth": 50.0
          }
        ]
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4100D40U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4100D40U"
  ],
  "serviceType": "ncas"
}
```


#### `GET` GET - FTTN SERVICE CLASS 13 (FIBRE SQ)

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseFTTNSERVICECLASS13FIBRESQ}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request a Fibre SQ with value 'NFAS' | NFAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "serviceabilityStatus": "Serviceable - Shortfall",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre",
      "serviceabilityClass": "1",
      "businessFibre": false
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Urban",
      "CSAId": "CSA400000010875",
      "poiId": "4TNS"
    },
    "supportingRelatedLocationFeatures": {
      "newDevelopmentsChargeApplies": false
    },
    "supportingProductFeatures": [
      {
        "type": "NFAS",
        "version": "2.3.0",
        "multicast": true,
        "capacityAvailability": [
          {
            "featureType": "TC2",
            "capacity": 90,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 5,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 50,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 80,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 70,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 10,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 30,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 100,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 60,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC2",
            "capacity": 40,
            "unitOfMeasure": "Mbps",
            "available": true
          },
          {
            "featureType": "TC1",
            "capacity": 2,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          },
          {
            "featureType": "TC2",
            "capacity": 20,
            "unitOfMeasure": "Mbps",
            "highSpeedNotLessThan": true
          }
        ],
        "speedTierAvailability": [
          "Home Fast",
          "Home Superfast",
          "Home Ultrafast"
        ],
        "TC2": true,
        "TR069UNIV": true,
        "FTPUNIV": false
      }
    ]
  },
  "virtutelSpeedsAvailable": [
    "TC425D5U",
    "TC425D10U",
    "TC450D20U",
    "TC4100D20U",
    "TC4500D50U",
    "TC4100D40U",
    "TC4250D25U",
    "TC4750D50U",
    "TC4250D100U",
    "TC4500D200U",
    "TC41000D50U",
    "TC41000D100U",
    "TC41000D400U",
    "L3TC425D5U",
    "L3TC425D10U",
    "L3TC450D20U",
    "L3TC4100D20U",
    "L3TC4500D50U",
    "L3TC4100D40U",
    "L3TC4250D25U",
    "L3TC4750D50U",
    "L3TC4250D100U",
    "L3TC4500D200U",
    "L3TC41000D50U",
    "L3TC41000D100U",
    "L3TC41000D400U"
  ],
  "serviceType": "nfas"
}
```


### Enterprise Ethernet SQ

**URI:** `/service-qualifications/{locationId}{?apiaryEeSQ}`

Use the `productType` parameter with value 'EEAS' to run an EE SQ.

Required scope: read:service-qualifications


#### `GET` GET - EEAS (FTTP) VALID CATEGORY A

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTPVALIDCATEGORYA}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Metropolitan",
      "zone": "Zone 1",
      "localPOI": "3NTF - QLD SMALL POI",
      "routeTypeZone": "2"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "A"
    }
  }
}
```


#### `GET` GET - EEAS (FIXED WIRELESS) VALID CATEGORY A

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFIXEDWIRELESSVALIDCATEGORYA}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Regional Centre",
      "zone": "Zone 1",
      "localPOI": "4NTF - NSW MEDIUM POI",
      "routeTypeZone": "2"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "A"
    }
  }
}
```


#### `GET` GET - EEAS (SATELLITE) VALID CATEGORY C

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASSATELLITEVALIDCATEGORYC}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Regional Centre",
      "zone": "Zone 1",
      "localPOI": "4NTF - NSW MEDIUM POI",
      "routeTypeZone": "1"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "C"
    }
  }
}
```


#### `GET` GET - EEAS (HFC) VALID CATEGORY A

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASHFCVALIDCATEGORYA}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Major Rural",
      "zone": "Zone 1",
      "localPOI": "4NTF - NSW MEDIUM POI",
      "routeTypeZone": "1"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "A"
    }
  }
}
```


#### `GET` GET - EEAS (FTTB) VALID CATEGORY B

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTBVALIDCATEGORYB}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Major Rural",
      "zone": "Zone 1",
      "localPOI": "4NTF - NSW MEDIUM POI",
      "routeTypeZone": "1"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "B"
    }
  }
}
```


#### `GET` GET - EEAS (FTTN) VALID CATEGORY B

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTNVALIDCATEGORYB}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Metropolitan",
      "zone": "Zone 1",
      "localPOI": "3NTF - QLD SMALL POI",
      "routeTypeZone": "1"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "B"
    }
  }
}
```


#### `GET` GET - EEAS (FTTC) VALID CATEGORY C

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTCVALIDCATEGORYC}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Valid",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb"
    },
    "supportingRelatedSiteBoundaries": {
      "region": "Major Rural",
      "zone": "Zone 1",
      "localPOI": "4NTF - NSW MEDIUM POI",
      "routeTypeZone": "1"
    },
    "supportingRelatedLocationFeatures": {
      "fibreBuildCategory": "C"
    }
  }
}
```


#### `GET` GET - EEAS (FTTP) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTPPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre"
    }
  }
}
```


#### `GET` GET - EEAS (FIXED WIRELESS) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFIXEDWIRELESSPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Wireless"
    }
  }
}
```


#### `GET` GET - EEAS (SATELLITE) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASSATELLITEPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Satellite"
    }
  }
}
```


#### `GET` GET - EEAS (HFC) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASHFCPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "HFC"
    }
  }
}
```


#### `GET` GET - EEAS (FTTB) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTBPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Building"
    }
  }
}
```


#### `GET` GET - EEAS (FTTN) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTNPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Node"
    }
  }
}
```


#### `GET` GET - EEAS (FTTC) PRODUCT UNAVAILABLE

**URI:** `/service-qualifications/{locationId}{?productType,apiarySQResponseEEASFTTCPRODUCTUNAVAILABLE}`

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Location ID to perform the service qualification on | LOC123456789012 |
|  | string | yes | Request an Enterprise Ethernet SQ with value 'EEAS' | EEAS |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "id": "LOC123456789012",
  "siteRestriction": {
    "id": "LOC123456789012",
    "status": "Product Unavailable",
    "supportingTechnology": {
      "primaryAccessTechnology": "Fibre To The Curb"
    }
  }
}
```
