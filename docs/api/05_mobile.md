# Mobile

## Mobile


### Pre-Ordering ALPHA

**URI:** `/mobile/pre-ordering{?apiaryMobilePreOrdering}`


#### `GET` GET - Request Available Mobile Numbers

**URI:** `/mobile/pre-ordering/available-numbers{?numberType,apiaryRequestAvailableMobileNumbers}`

This operation is used to request a list of available mobile numbers you can use in New Activation (connect) orders.

Required scope: create:mobile-orders

Rate limits: steady - 10 requests/minute, burst - 2 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | Optionally request `Premium` numbers (defaults to `Standard`). Premium numbers contain desirable patterns like repeating digits but attract an additional fee | Standard |


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
  "availableNumbers": [
    {
      "mobileNumber": "61400102030",
      "numberType": "Standard"
    },
    {
      "mobileNumber": "61400102031",
      "numberType": "Standard"
    },
    {
      "mobileNumber": "61400102032",
      "numberType": "Standard"
    },
    {
      "mobileNumber": "61400102033",
      "numberType": "Standard"
    },
    {
      "mobileNumber": "61400102034",
      "numberType": "Standard"
    }
  ]
}
```


#### `POST` POST - Request Dispatch of Mobile Physical SIM Cards

**URI:** `/mobile/pre-ordering/request-physical-sims{?apiaryRequestPhysicalSIMs}`

This operation is used to order a box of physical SIM cards. These will be mailed to you.

Required scope: create:mobile-orders


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
  "sims": [
    {
      "simId": "12345678901234567890",
      "simSerial": "1234567890123"
    },
    {
      "simId": "12345678901234567891",
      "simSerial": "1234567890124"
    },
    {
      "simId": "12345678901234567892",
      "simSerial": "1234567890125"
    },
    {
      "simId": "12345678901234567893",
      "simSerial": "1234567890126"
    },
    {
      "simId": "12345678901234567894",
      "simSerial": "1234567890127"
    }
  ]
}
```


#### `GET` GET - List Assigned Mobile Physical SIMs (uSIMs)

**URI:** `/mobile/pre-ordering/list-sims{?used,apiaryListAssignedMobilePhysicalSIMs}`

This operation is used to request the list of SIMs assigned to you. You can optionally filter just for used status.

Please note this endpoint is paginated.

Required scope: create:mobile-orders

Rate limits: steady - 10 requests/minute, burst - 2 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | boolean | yes | Optionally filter for used/unused SIMs (all SIMs are shown if this parameter is not provided) | false |


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
  "sims": [
    {
      "simId": "12345678901234567890",
      "simSerial": "1234567890123",
      "used": false,
      "expiryDate": "2029-07-01"
    },
    {
      "simId": "12345678901234567891",
      "simSerial": "1234567890124",
      "used": false,
      "expiryDate": "2029-07-01"
    },
    {
      "simId": "12345678901234567892",
      "simSerial": "1234567890125",
      "used": false,
      "expiryDate": "2029-07-01"
    },
    {
      "simId": "12345678901234567893",
      "simSerial": "1234567890126",
      "used": false,
      "expiryDate": "2029-07-01"
    },
    {
      "simId": "12345678901234567894",
      "simSerial": "1234567890127",
      "used": false,
      "expiryDate": "2029-07-01"
    }
  ],
  "_meta": {
    "total_records": 5,
    "page": 1,
    "limit": 100,
    "count": 5
  },
  "_links": {
    "self": "/api/v1/mobile/pre-ordering/list-sims?page=1&limit=100&used=false",
    "first": "/api/v1/mobile/pre-ordering/list-sims?page=1&limit=100&used=false",
    "last": "/api/v1/mobile/pre-ordering/list-sims?page=1&limit=100&used=false"
  }
}
```


### eSIM Activation Codes ALPHA

**URI:** `/mobile/esim-activation-codes{?apiaryMobileESimActivationCodesSection}`


#### `POST` CALLBACK (POST) - ESimActivationCodeGenerated (eSIM activation code generated)

**URI:** `/these-are-sent-to-your-server{?apiaryMobileESimActivationCodeGenerated}`

This callback is sent as soon as an activation code is generated for an eSIM-based Connect Order, or SIM swap to an eSIM. The callback contains both the
raw activation code as a string, as well as the Base64 representation of the activation code as a QR Code PNG image.


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "52f65fa6-f66c-4bf2-b2ac-9527e2caf559",
  "eventTime": "2020-10-01T06:03:24+00:00",
  "eventType": "MobileMobileESimActivationCodeNotification",
  "event": {
    "id": "VTORD0000000000001",
    "notificationType": "ESimActivationCodeGenerated",
    "reason": "eSIM Activation Code Generated",
    "msisdn": "61400123456",
    "activationCode": "LPA:1$ab-cd-ef.esim.example.com$AABBCCDDEEFF11223344556677889900",
    "qrCode": {
      "mimeType": "image/png",
      "encoding": "base64",
      "imageData": "iVBORw0KGgoAAAANSUhEUgAAA5cAAAISCAYAAABYn...."
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` POST - Generate Mobile eSIM QR Code

**URI:** `/mobile/esim-qr-codes{?apiaryMobileESimQRCodeSectionPost}`

This operation is used to generate a base64 encoded string representing the QR code image for an eSIM.

Please note that you cannot generate a QR code for an eSIM until the activation code has been generated (normally this is shortly after
Telstra order completion - i.e. order status is TELSTRA_ORDER_COMPLETED). You will be notified with an ESimActivationCodeGenerated callback (described above),
which also contains the QR code image data that this endpoint generates.

Required scope: create:mobile-orders

Rate limits: steady - 60 requests/minute, burst - 5 requests/second


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "vtOrderId": "VTORDAADD1122FF"
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
  "mimeType": "image/png",
  "encoding": "base64",
  "imageData": "iVBORw0KGgoAAAANSUhEUgAAA5cAAAISCAYAAABYn...."
}
```


### SIM Swaps ALPHA

**URI:** `/mobile/sim-swaps{?apiaryMobileSimSwapsSection}`


#### `POST` POST - Request SIM Swap

**URI:** `/mobile/sim-swaps{?apiaryMobileSimSwapsPost}`

This operation is used to replace the SIM for a given mobile service with a new one.

You can either specify the ICC ID of the new physical SIM, or request an eSIM (but not both).

SIM swaps are usually completed in a few minutes.

If you request an eSIM, you'll receive an ESimActivationCodeGenerated callback with the activation code for the new eSIM (the same callback
you get when ordering a new service on an eSIM). Please see the eSIM Activation Codes section for details.

Once the SIM swap is complete, you'll receive a SimSwapCompleted callback (documented below) regardless of the new SIM type.

Required scope: create:mobile-orders

Rate limits: steady - 60 requests/minute, burst - 5 requests/second


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "serviceId": "61400000000",
  "simId": "89600000000000000001"
}
```


*Response — 201:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": ""
}
```


#### `POST` CALLBACK (POST) - SIM Swap Completed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileSimSwapCompletedCallback}`

This callback is sent to your server as soon as the SIM swap has been completed.


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "52f65fa6-f66c-4bf2-b2ac-9527e2caf559",
  "eventTime": "2020-10-01T06:03:24+00:00",
  "eventType": "MobileSimSwapNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "SimSwapCompleted",
    "reason": "SIM swap completed",
    "oldSimId": "89600000000000000000",
    "newSimId": "89600000000000000001"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Add Bolt-On ALPHA

**URI:** `/mobile/add-bolt-on{?apiaryMobileAddBoltOn}`


#### `POST` POST - Add Bolt-On to Existing Mobile Service

**URI:** `/mobile/add-bolt-on{?apiaryMobileBoltOnPOST}`

Required scope: create:mobile-orders

Rate limits: steady - 60 requests/minute, burst - 5 requests/second


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "serviceId": "VT0000001",
  "refill": "MDP5"
}
```


*Response — 201:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": ""
}
```


### Excess Limits ALPHA

**URI:** `/mobile/excess-limits{?apiaryMobileExcessLimits}`


#### `POST` POST - Set Mobile Excess Limit

**URI:** `/mobile/excess-limits{?serviceId,apiarySETMobileSetExcessLimits}`

This operation is used to change the excess limit to a new value.

Note that the new value cannot be the same as the old value, and you must wait 5 minutes after one excess limit change is complete before
submitting another one for the same service.

Changes are made asynchronously and usually complete in 1-2 minutes.

Required scope: create:mobile-orders

Rate limits: steady - 30 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID in VTxxxxxxx format or mobile number in 614xxxxxxxx format | VT0000001 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "excessLimit": {
    "limit": "50"
  }
}
```


*Response — 201:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": ""
}
```


#### `GET` GET - Check Mobile Excess Limit

**URI:** `/mobile/excess-limits{?serviceId,apiaryCheckMobileSetExcessLimit}`

Required scope: create:mobile-orders

Rate limits: steady - 30 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID in VTxxxxxxx format or mobile number in 614xxxxxxxx format | VT0000001 |


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
  "excessLimit": {
    "limit": "50",
    "units": "dollars"
  }
}
```


### GSM Configuration ALPHA

**URI:** `/mobile/gsm-config{?apiaryMobileGSMConfig}`


#### `GET` GET - Fetch GSM Configuration

**URI:** `/mobile/gsm-configuration{?serviceId,apiaryCheckGSMConfig}`

This operation returns the GSM configuration of a mobile service.

Required scope: create:mobile-orders

Rate limits: steady - 60 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID or MSISDN (number in 61400000000 format) | VT0000001 |


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
  "modificationInProgress": false,
  "callForwarding": {
    "unconditional": {
      "enabled": false,
      "forwardTo": ""
    },
    "noReply": {
      "enabled": false,
      "forwardTo": "61101"
    },
    "notReachable": {
      "enabled": false,
      "forwardTo": "61234567890"
    },
    "subscriberBusy": {
      "enabled": false,
      "forwardTo": "61101"
    }
  },
  "callManagement": {
    "callHold": {
      "enabled": false
    },
    "callWaiting": {
      "enabled": false
    },
    "multiParty": {
      "enabled": false
    }
  },
  "callBarring": {
    "icRoaming": {
      "enabled": false
    },
    "allIncoming": {
      "enabled": false
    },
    "allOutgoing": {
      "enabled": false
    },
    "allIdd": {
      "enabled": false
    },
    "iddExceptHome": {
      "enabled": false
    },
    "premiumEnterprise": {
      "enabled": false
    },
    "premiumInfo": {
      "enabled": false
    },
    "handsetOutgoing": {
      "enabled": false
    },
    "handsetIncoming": {
      "enabled": false
    }
  },
  "misc": {
    "allMms": {
      "enabled": false
    },
    "mmsVideo": {
      "enabled": false
    },
    "smsOutgoing": {
      "enabled": false
    },
    "smsIncoming": {
      "enabled": false
    },
    "voicemail": {
      "enabled": false
    },
    "internationalRoaming": {
      "enabled": false
    },
    "gprsBasicService": {
      "enabled": false
    }
  }
}
```


#### `POST` POST - Modify GSM Configuration

**URI:** `/mobile/gsm-configuration{?serviceId,apiaryModifyGSMConfiguration}`

This operation is used to update one or more of the GSM configuration options for a mobile service.

You do not need to supply values for all options - only the ones you're changing. Any omitted objects will keep their existing values.
For example, you can just supply the `callForwarding`.`unanswered` object and the `callManagement`.`callHold` object if you only
want to update the configuration for those two features.

It can take several minutes for this operation to complete. You cannot make another update request until the previous one has completed.
You can check if there is another update in progress, just GET this endpoint and check the `modificationInProgress` flag.

Changes for each specific feature have an additional 15 minute cooldown (e.g. if you disable `callManagement`.`callWaiting` in one request,
you must wait 15 minutes after the `modificationInProgress` flag changes to false before enabling `callManagement`.`callWaiting`.

Pay special attention to the `forwardTo` numbers in the `callForwarding` section. These are always required when `enabled` is set to true,
but ignored when `enabled` is set to false.

Rate limits: steady - 30 requests/minute, burst - 5 requests/second

Required scope: create:mobile-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID or MSISDN (number in 61400000000 format) | VT0000001 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "callForwarding": {
    "unconditional": {
      "enabled": false
    },
    "noReply": {
      "enabled": true,
      "forwardTo": "61101"
    },
    "notReachable": {
      "enabled": true,
      "forwardTo": "61234567890"
    },
    "subscriberBusy": {
      "enabled": true,
      "forwardTo": "61101"
    }
  },
  "callManagement": {
    "callHold": {
      "enabled": false
    },
    "callWaiting": {
      "enabled": false
    },
    "multiParty": {
      "enabled": false
    }
  },
  "callBarring": {
    "icRoaming": {
      "enabled": false
    },
    "allIncoming": {
      "enabled": false
    },
    "allOutgoing": {
      "enabled": false
    },
    "allIdd": {
      "enabled": false
    },
    "iddExceptHome": {
      "enabled": false
    },
    "premiumEnterprise": {
      "enabled": false
    },
    "premiumInfo": {
      "enabled": false
    },
    "handsetOutgoing": {
      "enabled": false
    },
    "handsetIncoming": {
      "enabled": false
    }
  },
  "misc": {
    "allMms": {
      "enabled": false
    },
    "mmsVideo": {
      "enabled": false
    },
    "smsOutgoing": {
      "enabled": false
    },
    "smsIncoming": {
      "enabled": false
    },
    "voicemail": {
      "enabled": false
    },
    "internationalRoaming": {
      "enabled": false
    },
    "gprsBasicService": {
      "enabled": false
    }
  }
}
```


*Response — 201:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": ""
}
```


### Customer Care Numbers ALPHA

**URI:** `/mobile/customer-care-numbers{?apiaryMobileCustomerCareNumbers}`


#### `POST` POST - Add or Update Customer Care Numbers

**URI:** `/mobile/customer-care-numbers{?serviceId,apiaryUpdateMobileCustomerCareNumbers}`

This operation is used to add new customer care numbers, or update to new numbers.

You must provide at least one number for this operation. Numbers must be in E164 format (e.g. 61200000000) and at least 8 digits long.

To remove an existing number, you need to supply a new number to take its place.

You can supply one or both numbers.

Required scope: create:mobile-orders

Rate limits: steady - 60 requests/minute, burst - 5 requests/second


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "number1": "61200000000",
  "number2": "61200000000"
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
  "number1": "61200000000",
  "number2": "61200000001"
}
```


#### `GET` GET - Fetch Customer Care Numbers

**URI:** `/mobile/customer-care-numbers{?serviceId,apiaryFetchCustomerCareNumbers}`

This operation is used to check which customer care numbers have been set.

Required scope: create:mobile-orders

Rate limits: steady - 60 requests/minute, burst - 5 requests/second


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
  "number1": "61200000000",
  "number2": "61200000001"
}
```


### Callbacks - International Roaming Welcome ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileInternationalRoamingWelcomeCallbacks}`

Please note that these callbacks are sent to your callback server(s). Please see the Callbacks section above for information about the
callback registration process.


#### `POST` CALLBACK (POST) - Roaming Welcome (add a travel pack)

**URI:** `/these-are-sent-to-your-server{?apiaryMobileRoamingWelcomeAddTravelPackCallback}`

Sent to an end user when they first attach to a roaming network in a destination with eligibility status: "YES".
They will need to add a travel pack to use their service.


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "dab20b46-dbfd-4797-abe0-fabd5f4ffb25",
  "eventTime": "2026-01-01T17:04:15+00:00",
  "eventType": "MobileACMAInternationalRoamingNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "RoamingWelcomeAddTravelPackNotification",
    "reason": "Roaming Welcome - Add Travel Pack",
    "roamingCountryMCC": "542",
    "roamingCountryName": "Fiji",
    "irnReceiveDate": "2026-01-02T03:04:05",
    "excessUsageCharges": {
      "voicePerMinute": {
        "toRoamingCountry": "1.2",
        "toAustralia": "1.2",
        "toAnyOtherCountry": "1.2",
        "receiveCall": "1.2"
      },
      "dataPerMB": "0.75",
      "mms": {
        "toRoamingCountry": "0.5",
        "toAustralia": "0.5",
        "toAnyOtherCountry": "0.5",
        "receiveMms": "0.0"
      },
      "sms": {
        "toRoamingCountry": "0.5",
        "toAustralia": "0.5",
        "toAnyOtherCountry": "0.5",
        "receiveSms": "0.0"
      }
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - Roaming Welcome (no voice/mms/data)

**URI:** `/these-are-sent-to-your-server{?apiaryMobileRoamingWelcomeNoUsageCallback}`

Sent to an end user when they first attach to a roaming network in a destination with elegibility status: "MT SMS only".
Voice calls, MMS, data, and sending SMS will be unavailable. They may be able to receive SMS. This will be at no charge.


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "dab20b46-dbfd-4797-abe0-fabd5f4ffb25",
  "eventTime": "2026-02-03T02:35:06+00:00",
  "eventType": "MobileACMAInternationalRoamingNotification",
  "event": {
    "id": "61400000001",
    "notificationType": "RoamingWelcomeNoUsageNotification",
    "reason": "Roaming Welcome - No Usage",
    "roamingCountryMCC": "250",
    "roamingCountryName": "Russia",
    "irnReceiveDate": "2026-02-03T12:34:56"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Included Value Plans Data Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataUsageThresholdForIncludedValuePlansCallbacks}`


#### `POST` CALLBACK (POST) - PlanData50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePlanData50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "9b77ae44-9990-4e9b-a3fb-180ada8263f1",
  "eventTime": "2026-01-27T03:08:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "PlanData50PercentUsed",
    "reason": "Plan Data 50% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "15",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - PlanData85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePlanData85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "82f90f6a-a233-40ff-abdd-87caeaaf5b7a",
  "eventTime": "2026-01-27T03:09:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "PlanData85PercentUsed",
    "reason": "Plan Data 85% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "4.5",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - PlanData100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePlanData100PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "67d7ec4c-893d-4082-a132-ebb50e6a87f1",
  "eventTime": "2026-01-27T03:10:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "PlanData100PercentUsed",
    "reason": "Plan Data 100% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "0",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Included Value Plans MMS Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileUsageThresholdForMMSAllowanceCallbacks}`


#### `POST` CALLBACK (POST) - PlanMms50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePlanMms50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "c3754101-7f82-4bf6-aed7-5d356fb7b48b",
  "eventTime": "2026-01-27T03:11:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "PlanMms50PercentUsed",
    "reason": "Plan MMS 50% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "1000",
      "units": "MMS"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - PlanMms85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePlanMms85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "ef9b7445-2f99-4eb0-a3d8-f400463e1571",
  "eventTime": "2026-01-27T03:12:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "PlanMms85PercentUsed",
    "reason": "Plan MMS 85% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "300",
      "units": "MMS"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - PlanMms100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePlanMms100PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "6e771d5d-947b-4c80-a900-c6dcc85d981b",
  "eventTime": "2026-01-27T03:13:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "PlanMms100PercentUsed",
    "reason": "Plan MMS 100% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "0",
      "units": "MMS"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Data Pack Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataPackUsageThresholdCallbacks}`


#### `POST` CALLBACK (POST) - DataPack50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataPack50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "cb91013a-8069-4213-aefc-b16e87301071",
  "eventTime": "2026-01-27T03:05:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataPack50PercentUsed",
    "reason": "Data Pack 50% Used",
    "planName": "MDP5",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "2.5",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - DataPack85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataPack85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "4d020fae-c532-4ea2-ac9c-39f866d68d68",
  "eventTime": "2026-01-27T03:06:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataPack85PercentUsed",
    "reason": "Data Pack 85% Used",
    "planName": "MDP5",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "750",
      "units": "MB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - DataPack100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataPack100PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "37b457d8-618e-401d-a6b1-6d09e8caaeeb",
  "eventTime": "2026-01-27T03:07:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataPack100PercentUsed",
    "reason": "Data Pack 100% Used",
    "planName": "MDP5",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "0",
      "units": "MB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - IDD Pack Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileIDDPackUsageThresholdCallbacks}`


#### `POST` CALLBACK (POST) - IddPack50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileIddPack50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "38161687-51d0-4e89-a3dc-51baae4fc3ad",
  "eventTime": "2026-01-27T03:14:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "IddPack50PercentUsed",
    "reason": "IDD Pack 50% Used",
    "planName": "MIDD120",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "3600",
      "units": "minutes"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - IddPack85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileIddPack85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "ca355c6f-c098-436b-ab80-2f22e95ea2d5",
  "eventTime": "2026-01-27T03:15:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "IddPack85PercentUsed",
    "reason": "IDD Pack 85% Used",
    "planName": "MIDD120",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "1080",
      "units": "minutes"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - IddPack100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileIddPack100PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "28817e69-2a4a-4113-aeb9-f6cdfdefd6fa",
  "eventTime": "2026-01-27T03:16:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "IddPack100PercentUsed",
    "reason": "IDD Pack 100% Used",
    "planName": "MIDD120",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "0",
      "units": "minutes"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Data Bank Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataBankUsageThresholdCallbacks}`

Sent when data bank falls to 20GB, 10GB, 1GB, and 0GB


#### `POST` CALLBACK (POST) - DataBankRemainingData

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataBankRemainingDataNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "c5455634-40ee-4711-a1ee-4b30c3af69b3",
  "eventTime": "2026-01-27T03:17:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataBankRemainingData",
    "reason": "Data Bank Remaining Data",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "10",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Data Only International Roaming Pack Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataOnlyIRPackThresholdCallbacks}`


#### `POST` CALLBACK (POST) - DataOnlyTravelPackNew

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataOnlyTravelPackNewNotification}`

Note that unlike the other callbacks in this section, the New Pack callback only contains the remaining balance (which would be identical to the allowance)


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "805b98fc-feff-4fcd-a7c9-0988a18512ac",
  "eventTime": "2026-01-27T03:34:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataOnlyTravelPackNew",
    "reason": "New Travel Pack Activated",
    "remainingBalance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - DataOnlyTravelPack50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataOnlyTravelPack50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "a0f94436-dbcb-4159-aae9-2470f1dbc255",
  "eventTime": "2026-01-27T03:18:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataOnlyTravelPack50PercentUsed",
    "reason": "Data Only Travel Pack 50% Used",
    "planName": "MTPP",
    "remainingBalance": {
      "value": "500",
      "units": "MB"
    },
    "planAllowance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - DataOnlyTravelPack85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataOnlyTravelPack85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "ae2e534b-1a10-40e6-a92f-254993b7a4c8",
  "eventTime": "2026-01-27T03:19:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataOnlyTravelPack85PercentUsed",
    "reason": "Data Only Travel Pack 85% Used",
    "planName": "MTPP",
    "remainingBalance": {
      "value": "150",
      "units": "MB"
    },
    "planAllowance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - DataOnlyTravelPack100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileDataOnlyTravelPack100PercentUsedNotification}`

Note that unlike the 50% and 85% callbacks, the 100% callback does not contain the `planName`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "dd73729e-4c93-4ec5-a16b-ab47450511a0",
  "eventTime": "2026-01-27T03:20:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "DataOnlyTravelPack100PercentUsed",
    "reason": "Data Only Travel Pack 100% Used",
    "remainingBalance": {
      "value": "0",
      "units": "MB"
    },
    "planAllowance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - International Roaming Pack Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileIRPackThresholdCallbacks}`


#### `POST` CALLBACK (POST) - TravelPackVoice50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackVoice50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "e1ddea34-4815-4d88-ac36-100f64fd089e",
  "eventTime": "2026-01-27T03:27:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackVoice50PercentUsed",
    "reason": "Travel Pack Voice 50% Used",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "600",
      "units": "minutes"
    },
    "planAllowance": {
      "value": "1200",
      "units": "minutes"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackVoice85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackVoice85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "70571256-6b7d-45f8-a971-de4ba67f656c",
  "eventTime": "2026-01-27T03:28:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackVoice85PercentUsed",
    "reason": "Travel Pack Voice 85% Used",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "180",
      "units": "minutes"
    },
    "planAllowance": {
      "value": "1200",
      "units": "minutes"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackVoice100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackVoice100PercentUsedNotification}`

Note that unlike the 50% and 85% callbacks, the 100% callback only contains planAllowance


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "571c83bc-d243-4d40-aca2-e6a05860389b",
  "eventTime": "2026-01-27T03:29:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackVoice100PercentUsed",
    "reason": "Travel Pack Voice 100% Used",
    "planAllowance": {
      "value": "1200",
      "units": "minutes"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackSms50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackSms50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "ed715a4d-6163-4fe4-adf3-2b157be4c5f9",
  "eventTime": "2026-01-27T03:24:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackSms50PercentUsed",
    "reason": "Travel Pack SMS 50% Used",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "10",
      "units": "SMS"
    },
    "planAllowance": {
      "value": "20",
      "units": "SMS"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackSms85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackSms85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "b673c3e6-daf5-4a49-ad2e-26efb4b1dd22",
  "eventTime": "2026-01-27T03:25:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackSms85PercentUsed",
    "reason": "Travel Pack SMS 85% Used",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "3",
      "units": "SMS"
    },
    "planAllowance": {
      "value": "20",
      "units": "SMS"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackSms100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackSms100PercentUsedNotification}`

Note that unlike the 50% and 85% callbacks, the 100% callback does not contain the remaining balance (which would be 0)


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "9fff6e11-25af-4e4f-ae5e-f3898036de6f",
  "eventTime": "2026-01-27T03:26:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackSms100PercentUsed",
    "reason": "Travel Pack SMS 100% Used",
    "planAllowance": {
      "value": "20",
      "units": "SMS"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackData50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackData50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "238a2db3-17bc-442b-ac26-bc700ab946b8",
  "eventTime": "2026-01-27T03:21:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackData50PercentUsed",
    "reason": "Travel Pack Data 50% Used",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "500",
      "units": "MB"
    },
    "planAllowance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackData85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackData85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "632b5f7e-d7ec-4c1b-a5f6-e18d60d52401",
  "eventTime": "2026-01-27T03:22:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackData85PercentUsed",
    "reason": "Travel Pack Data 85% Used",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "150",
      "units": "MB"
    },
    "planAllowance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - TravelPackData100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileTravelPackData100PercentUsedNotification}`

Note that unlike the 50% and 85% callbacks, the 100% callback only contains planAllowance


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "28356878-4647-4aad-a326-5d1252123029",
  "eventTime": "2026-01-27T03:23:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "TravelPackData100PercentUsed",
    "reason": "Travel Pack Data 100% Used",
    "planAllowance": {
      "value": "1",
      "units": "GB"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - PAYG Usage Threshold ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePAYGThresholdCallbacks}`


#### `POST` CALLBACK (POST) - Payg50PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePayg50PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "545fc4f2-62cb-41c9-a140-bba5088f86ed",
  "eventTime": "2026-01-27T03:31:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "Payg50PercentUsed",
    "reason": "PAYG 50% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "5",
      "units": "dollars"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - Payg85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePayg85PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "d58b3d2d-72f3-49de-ac45-3a9bf61b08e3",
  "eventTime": "2026-01-27T03:32:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "Payg85PercentUsed",
    "reason": "PAYG 85% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "1.5",
      "units": "dollars"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - Payg100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePayg100PercentUsedNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "e5bed0ca-6e40-4fcb-a81d-a3081daf680d",
  "eventTime": "2026-01-27T03:33:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "Payg100PercentUsed",
    "reason": "PAYG 100% Used",
    "planName": "MPP1",
    "planExpiry": "2026-05-04",
    "remainingBalance": {
      "value": "0",
      "units": "dollars"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - International Roaming Excess Usage PAYG ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileIRExcessUsagePaygCallbacks}`


#### `POST` CALLBACK (POST) - RoamingPaygExcessUsage

**URI:** `/these-are-sent-to-your-server{?apiaryMobileRoamingPaygExcessUsageNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "3a5ccd17-54a5-4cb9-a25f-492557083148",
  "eventTime": "2026-01-27T03:30:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "RoamingPaygExcessUsage",
    "reason": "Roaming PAYG Excess Usage",
    "planAllowance": {
      "value": "100",
      "units": "dollars"
    }
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Fraud Notifications ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudCallbacks}`


#### `POST` CALLBACK (POST) - FraudInternationalVoiceCounter1500MinLimit85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudInternationalVoiceCounter1500MinLimit85PercentUsedNotification}`

International voice (IDD) counter with limit set to 1,500 minutes minutes 85% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "71e75df6-6a47-4679-aa11-acae1bd73a76",
  "eventTime": "2026-01-27T03:36:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudInternationalVoiceCounter1500MinLimit85PercentUsed",
    "reason": "Fraud 1500min International Voice (IDD) Counter 85% Depleted",
    "planName": "MPP1",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudInternationalVoiceCounter1500MinLimit100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudInternationalVoiceCounter1500MinLimit100PercentUsedNotification}`

International voice (IDD) counter with limit set to 1,500 minutes minutes 100% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "11736667-c048-4a55-a998-0d8b906768bd",
  "eventTime": "2026-01-27T03:37:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudInternationalVoiceCounter1500MinLimit100PercentUsed",
    "reason": "Fraud 1500min International Voice (IDD) Counter 100% Depleted",
    "planName": "MPP1",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudDomesticVoiceCounter1440MinLimit85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudDomesticVoiceCounter1440MinLimit85PercentUsedNotification}`

Domestic voice counter with limit set to 1,440 minutes minutes 85% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "720fdd34-ca73-470e-a956-e336334dda5c",
  "eventTime": "2026-01-27T03:38:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudDomesticVoiceCounter1440MinLimit85PercentUsed",
    "reason": "Fraud 1440min Domestic Voice Counter 85% Depleted",
    "planName": "MPP2",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudDomesticVoiceCounter1440MinLimit100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudDomesticVoiceCounter1440MinLimit100PercentUsedNotification}`

Domestic voice counter with limit set to 1,440 minutes minutes 100% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "6879773e-a0b5-4343-a98e-b3ea96fb0a33",
  "eventTime": "2026-01-27T03:39:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudDomesticVoiceCounter1440MinLimit100PercentUsed",
    "reason": "Fraud 1440min Domestic Voice Counter 100% Depleted",
    "planName": "MPP2",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudInternationalSmsCounter300SmsLimit85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudInternationalSmsCounter300SmsLimit85PercentUsedNotification}`

International SMS counter with limit set to 300 events SMS 85% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "87932fdd-d4d1-4a04-a68e-b894a9cc97be",
  "eventTime": "2026-01-27T03:40:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudInternationalSmsCounter300SmsLimit85PercentUsed",
    "reason": "Fraud 300 International SMS Counter 85% Depleted",
    "planName": "MPP3",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudInternationalSmsCounter300SmsLimit100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudInternationalSmsCounter300SmsLimit100PercentUsedNotification}`

International SMS counter with limit set to 300 events SMS 100% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "99f49248-6e75-4995-a47e-dd16e5ca9ccb",
  "eventTime": "2026-01-27T03:41:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudInternationalSmsCounter300SmsLimit100PercentUsed",
    "reason": "Fraud 300 International SMS Counter 100% Depleted",
    "planName": "MPP3",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudDomesticSmsCounter300SmsLimit85PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudDomesticSmsCounter300SmsLimit85PercentUsedNotification}`

Domestic SMS counter with limit set to 1000 events SMS 85% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "b5c1847b-0d8c-49da-a3bc-ebb48f7174e7",
  "eventTime": "2026-01-27T03:42:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudDomesticSmsCounter300SmsLimit85PercentUsed",
    "reason": "Fraud 1000 Domestic SMS Counter 85% Depleted",
    "planName": "MPP4",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - FraudDomesticSmsCounter300SmsLimit100PercentUsed

**URI:** `/these-are-sent-to-your-server{?apiaryMobileFraudDomesticSmsCounter300SmsLimit100PercentUsedNotification}`

Domestic SMS counter with limit set to 1000 events SMS 100% depleted


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "46fe32a2-c632-4671-ab2a-4a3a466e3bc4",
  "eventTime": "2026-01-27T03:43:05+00:00",
  "eventType": "MobileUsageThresholdNotification",
  "event": {
    "id": "61400000000",
    "notificationType": "FraudDomesticSmsCounter300SmsLimit100PercentUsed",
    "reason": "Fraud 1000 Domestic SMS Counter 100% Depleted",
    "planName": "MPP4",
    "planExpiry": "2026-05-04"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


### Callbacks - Port Out ALPHA

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePortOutCallbacks}`

Please note that these callbacks are sent to your callback server(s). Please see the Callbacks section above for information about the
callback registration process.


#### `POST` CALLBACK (POST) - PortOutPortNotification (initial port out notification)

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePortOutCallbackPortNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "03db6b16-988d-498a-9ef8-ed6ec49a3908",
  "eventTime": "2020-10-01T06:03:24+00:00",
  "eventType": "MobilePortOutEventNotification",
  "event": {
    "id": "390d7208-cc22-4e91-99bc-9e5afd01b8e0",
    "notificationType": "PortOutPortNotification",
    "reason": "Port out has been initiated",
    "msn": "0400123456",
    "customerAuthorityDate": "2026-01-02",
    "accountReferenceNumber": "ABC123456"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - PortOutPortCutoverNotification (port out is proceeding)

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePortOutCallbackPortCutoverNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "03db6b16-988d-498a-9ef8-ed6ec49a3909",
  "eventTime": "2020-10-01T06:03:24+00:00",
  "eventType": "MobilePortOutEventNotification",
  "event": {
    "id": "390d7208-cc22-4e91-99bc-9e5afd01b8e0",
    "notificationType": "PortOutPortCutoverNotification",
    "reason": "Port out has been confirmed and is proceeding",
    "msn": "0400123456"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`


#### `POST` CALLBACK (POST) - PortOutBroadcastPortCutoverNotification (port out has completed)

**URI:** `/these-are-sent-to-your-server{?apiaryMobilePortOutCallbackBroadcastCutoverNotification}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "03db6b16-988d-498a-9ef8-ed6ec49a3910",
  "eventTime": "2020-10-01T06:03:24+00:00",
  "eventType": "MobilePortOutEventNotification",
  "event": {
    "id": "390d7208-cc22-4e91-99bc-9e5afd01b8e0",
    "notificationType": "PortOutBroadcastPortCutoverNotification",
    "reason": "Port out process has been completed",
    "msn": "0400123456"
  }
}
```


*Response — 200:*

Headers:

- `Content-Type: application/json`
