# Product Order Qualifications

## Product Order Qualifications


### Check product connect order feasibility

**URI:** `/product-order-qualifications?orderType=connect`

This endpoint can be used to qualify NBN Connect orders (with a few exceptions). Other service types are not currently supported.

Please note that nhas (HFC) order qualifications are not currently possible.

Churn (service transfer) order qualifications for any service type are also not allowed.

Product Order Qualifications use exactly the same format as Connect Orders.

Required scope: create:product-orders


#### `POST` POST - Example FTTP SC2 (feasible with appointment required)

**URI:** `/product-order-qualifications?orderType=connect{?apiaryOrderQualSC2}`


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "65535"
  },
  "contactData": {
    "firstName": "John",
    "lastName": "Smith",
    "phoneNumber": "+61403123456",
    "emailAddress": "johnsmith@example.com"
  },
  "service": {
    "nbn": {
      "nfas": {
        "uniDPortId": "1"
      },
      "speed": "TC450D20U"
    }
  }
}
```


*Response — 200:*

Note that activitySpec and reasonCode will not be included in every response.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "shortfall": [
    {
      "type": "NTD",
      "value": true
    },
    {
      "type": "LEADIN",
      "value": true
    },
    {
      "type": "NBNCOINFRASTRUCTURE",
      "value": false
    },
    {
      "type": "POWERSUPPLYWITHBATTERYBACKUP",
      "value": false
    }
  ],
  "status": "Feasible - Appointment Required",
  "activitySpec": [
    {
      "type": "Standard Install"
    }
  ],
  "reasonCode": [
    {
      "reason": "New Appointment Required - An Appointment is required but was not provided",
      "code": "AACN1603"
    }
  ],
  "serviceQualificationType": "ProductOrder"
}
```


#### `POST` POST - Example FTTP SC3 (feasible)

**URI:** `/product-order-qualifications?orderType=connect{?apiaryOrderQualSC3}`


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "65535"
  },
  "contactData": {
    "firstName": "John",
    "lastName": "Smith",
    "phoneNumber": "+61403123456",
    "emailAddress": "johnsmith@example.com"
  },
  "service": {
    "nbn": {
      "nfas": {
        "ntdId": "NTD000000000001",
        "uniDPortId": "1"
      },
      "speed": "TC450D20U"
    }
  }
}
```


*Response — 200:*

Note that activitySpec and reasonCode will not be included in every response.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "shortfall": [
    {
      "type": "NTD",
      "value": false
    },
    {
      "type": "LEADIN",
      "value": false
    },
    {
      "type": "NBNCOINFRASTRUCTURE",
      "value": false
    },
    {
      "type": "POWERSUPPLYWITHBATTERYBACKUP",
      "value": false
    }
  ],
  "status": "Feasible",
  "serviceQualificationType": "ProductOrder"
}
```


#### `POST` POST - Example Fixed Wireless SC5 (feasible with appointment required)

**URI:** `/product-order-qualifications?orderType=connect{?apiaryOrderQualSC5}`


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "65535"
  },
  "contactData": {
    "firstName": "John",
    "lastName": "Smith",
    "phoneNumber": "+61403123456",
    "emailAddress": "johnsmith@example.com"
  },
  "service": {
    "nbn": {
      "nwas": {
        "uniDPortId": "1"
      },
      "speed": "TC450D20U",
      "serviceRestorationSla": "Standard"
    }
  }
}
```


*Response — 200:*

Note that activitySpec and reasonCode will not be included in every response.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "shortfall": [
    {
      "type": "NTD",
      "value": true
    },
    {
      "type": "NBNCOINFRASTRUCTURE",
      "value": false
    }
  ],
  "status": "Feasible - Appointment Required",
  "activitySpec": [
    {
      "type": "Standard Install"
    }
  ],
  "reasonCode": [
    {
      "reason": "New Appointment Required - An Appointment is required but was not provided",
      "code": "AACN1603"
    }
  ],
  "serviceQualificationType": "ProductOrder"
}
```


#### `POST` POST - Example FTTN SC13 (feasible)

**URI:** `/product-order-qualifications?orderType=connect{?apiaryOrderQualSC13}`


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "65535"
  },
  "contactData": {
    "firstName": "John",
    "lastName": "Smith",
    "phoneNumber": "+61403123456",
    "emailAddress": "johnsmith@example.com"
  },
  "service": {
    "nbn": {
      "ncas": {
        "copperPairId": "CPI000000000001"
      },
      "speed": "TC450D20U"
    }
  }
}
```


*Response — 200:*

Note that activitySpec and reasonCode will not be included in every response.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "shortfall": [
    {
      "type": "LEADIN",
      "value": false
    },
    {
      "type": "NBNCOINFRASTRUCTURE",
      "value": false
    },
    {
      "type": "PATCH",
      "value": false
    }
  ],
  "status": "Feasible",
  "serviceQualificationType": "ProductOrder"
}
```


#### `POST` POST - Example FTTC SC33 (feasible with appointment required)

**URI:** `/product-order-qualifications?orderType=connect{?apiaryOrderQualSC33}`


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "65535"
  },
  "contactData": {
    "firstName": "John",
    "lastName": "Smith",
    "phoneNumber": "+61403123456",
    "emailAddress": "johnsmith@example.com"
  },
  "service": {
    "nbn": {
      "ncas": {
        "dispatchDetails": {
          "authorityToLeave": true,
          "deliveryInstructions": "Leave on the back porch",
          "deliveryContact": {
            "contactName": "Jane Citizen",
            "contactType": "End User",
            "emailAddress": "jane@example.com",
            "notes": "",
            "phoneNumber": "+61000000000"
          },
          "deliveryToAddress": {
            "addressLine1": "727 COLLINS ST",
            "addressLine2": "",
            "addressLine3": "",
            "localityName": "DOCKLANDS",
            "stateTerritoryCode": "VIC",
            "postCode": "3008"
          }
        },
        "copperPairId": "CPI000000000001"
      },
      "speed": "TC450D20U"
    }
  }
}
```


*Response — 200:*

Note that activitySpec and reasonCode will not be included in every response.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "shortfall": [
    {
      "type": "LEADIN",
      "value": false
    },
    {
      "type": "NBNCOINFRASTRUCTURE",
      "value": false
    },
    {
      "type": "PATCH",
      "value": false
    },
    {
      "type": "NCD",
      "value": true
    }
  ],
  "status": "Feasible - Appointment Required",
  "activitySpec": [
    {
      "type": "Standard Install"
    }
  ],
  "reasonCode": [
    {
      "reason": "New Appointment Required - An Appointment is required but was not provided",
      "code": "AACN1603"
    }
  ],
  "serviceQualificationType": "ProductOrder"
}
```


#### `POST` POST - Example FTTC SC34 (feasible, delayed)

**URI:** `/product-order-qualifications?orderType=connect{?apiaryOrderQualSC34}`


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "65535"
  },
  "contactData": {
    "firstName": "John",
    "lastName": "Smith",
    "phoneNumber": "+61403123456",
    "emailAddress": "johnsmith@example.com"
  },
  "service": {
    "nbn": {
      "ncas": {
        "copperPairId": "CPI000000000001"
      },
      "speed": "TC450D20U"
    }
  }
}
```


*Response — 200:*

Note that activitySpec and reasonCode will not be included in every response.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "shortfall": [
    {
      "type": "LEADIN",
      "value": false
    },
    {
      "type": "NBNCOINFRASTRUCTURE",
      "value": true
    },
    {
      "type": "PATCH",
      "value": false
    }
  ],
  "status": "Feasible - Delayed",
  "serviceQualificationType": "ProductOrder"
}
```
