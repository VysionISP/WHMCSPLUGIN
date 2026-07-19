# Product Orders

## Product Orders

Please note that order status values and descriptions have been moved inside the responses (as the `status` enum)

Product Order Callback Values

These are listed roughly in chronological order.

eventType
notificationType
Note

ProductOrderStateChangeNotification
NBNAcknowledged
The order has been submitted to NBN and is awaiting validation

ProductOrderCreationNotification
OrderAccepted
The order has been successfully validated and is accepted by NBN

ProductOrderStateChangeNotification
OrderRejected
The order has failed validation. We will review the order and resubmit it after correcting the issue.

ProductOrderStateChangeNotification
DispatchDetailsRequired
Delivery details are required to send a device to the end user (e.g. sending an NTD to an SC23 location)

ProductOrderStateChangeNotification
InstallFeeConfirmationRequired
The order will generate a subsequent install fee. You must approve it for the order to continue: https://virtutel.docs.apiary.io/#reference/product-orders/update-a-connect-order/patch

ProductOrderStateChangeNotification
DevelopmentChargeConfirmationRequired
The order will generate a New Development Charge. You must approve it for the order to continue: https://virtutel.docs.apiary.io/#reference/product-orders/update-a-connect-order/patch

ProductOrderStateChangeNotification
FibreUpgradeConfirmationRequired
Fibre upgrade orders are subject to penalties if the service is disconnected or the speed downgraded within the first 12 months. You must accept liability for these charges to continue the order: https://virtutel.docs.apiary.io/#reference/product-orders/update-a-connect-order/patch

ProductOrderStateChangeNotification
AppointmentRequired
An appointment is required for the order to continue. See https://virtutel.docs.apiary.io/#reference/appointments

ProductOrderStateChangeNotification
AppointmentRescheduleRequired
The appointment needs to be rescheduled. Normally this is because the work couldn't be completed in the original appointment window. See https://virtutel.docs.apiary.io/#reference/appointments/retrieve-or-modify-existing-appointment/patch

ProductOrderAttributeValueChangeNotification
AppointmentRescheduled
The appointment has been rescheduled and the order can continue

ProductOrderAttributeValueChangeNotification
DeliveryInTransit
NBN has sent out the NTD/NCD requested in the order

ProductOrderAttributeValueChangeNotification
InformationRequiredReminder
NBN is waiting for more information to progress the order. Normally we will resolve this, but we may need to contact you for additional information

ProductOrderAttributeValueChangeNotification
MigrationCutoverFailed

ProductOrderAttributeValueChangeNotification
MigrationCutoverInProgress

ProductOrderAttributeValueChangeNotification
MoreTimeAccepted
Request for more time to complete an action has been accepted by

ProductOrderAttributeValueChangeNotification
MoreTimeRejected
Request for more time to complete an action has been rejected

ProductOrderAttributeValueChangeNotification
OrderAmendAccepted
Additional order data has been provided to NBN and been accepted, (e.g. an appointment)

ProductOrderAttributeValueChangeNotification
OrderAmendRejected
Additional order data has been provided to NBN and been rejected. Normally this is because another amendment is in progress. We will assess the order

ProductOrderAttributeValueChangeNotification
OrderCommentAddedNBN
NBN has added a comment to the order

ProductOrderAttributeValueChangeNotification
OrderCommentAddedRSP
We have added a comment to the order

ProductOrderAttributeValueChangeNotification
PlannedRemediationDate
Planned remediation date for the network activity has been determined or updated. As of 2024-04-11, these will contain the `plannedRemediationDate` key (inside the `event` object). This will be the planned remediation date as a string in YYYY-MM-DD format if it's available, otherwise it will be null.

ProductOrderStateChangeNotification
PortInRejected
Mobile port-in request has been rejected by the losing carrier (both for ARN and DOB). Please use the PATCH method below to correct them, then the order will continue

ProductOrderStateChangeNotification
DeviceDetailsRequired
Additional details are required for the order to progress, (e.g. HFC MAC address required). We will assess the order

ProductOrderStateChangeNotification
DeviceOnlineRequired
NBN is waiting for the NTD/NCD/modem to come online

ProductOrderStateChangeNotification
ManualInterventionRequired
The order is in a state that requires manual intervention from the Virtutel team

ProductOrderStateChangeNotification
WaitingToBeProcessed
Sent after ManualInterventionRequired. The VT team has rectified the issue and the order is waiting for the next processing cycle

ProductOrderStateChangeNotification
NBNActionCompleted
NBN has completed the required action and the order can continue

ProductOrderStateChangeNotification
NBNActionRequired
NBN needs to complete some action before the order can continue (e.g. network augmentation)

ProductOrderStateChangeNotification
RSPActionCompleted
NBN has acknowledged you've performed the required action and the order can proceed

ProductOrderStateChangeNotification
RSPActionRequired
You need to complete some action for the order to proceed, (e.g. have the end user connect a device). Once this is complete you can request that NBN continue the order: https://virtutel.docs.apiary.io/#reference/product-orders/resume-a-connect-order/patch

ProductOrderServiceTestCompleted
ServiceTestCompleted
NBN has detected data flow (or has skipped the service test because it's not necessary)

ProductOrderAttributeValueChangeNotification
ServiceTestCompleted
NBN has detected data flow (or has skipped the service test because it's not necessary)

ProductOrderAttributeValueChangeNotification
ServiceDisconnected
Disconnect orders only. NBN has disconnected the service

ProductOrderStateChangeNotification
ManuallyCancelled
We have determined that the order cannot continue and have marked it for cancellation

ProductOrderRemoveNotification
OrderCancelled
The order has been cancelled by NBN. This is usually because it cannot be completed. We will assess the order

ProductOrderStateChangeNotification
OrderCompleted
Order has been completed on NBN's end, but still needs to be processed into our billing system

ProductOrderStateChangeNotification
VTOrderCancelled
We have cancelled the order in our system. This is the final notification for an unsuccessful order

ProductOrderStateChangeNotification
VTOrderCompleted
The order is fully complete and has been processed into the VT billing system. This is the final notification for a successful order


### Connect Order

**URI:** `/product-orders{?apiaryProductConnectOrders}`


#### `POST` POST - Submit a Connect Order

**URI:** `/product-orders?orderType=connect`

This is used to create a new service or churn an existing service from another provider.

Required scope: create:product-orders

Optional scope: create:mobile-orders (required for mobile orders)


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith / ABC PTY LTD",
    "orderRef": "656565"
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
        "uniDPortId": "1",
        "ntdId": "NTD000000000001"
      },
      "speed": "TC450D20U",
      "churn": {
        "type": "Service Transfer",
        "customerAuthorityDate": "2020-12-02",
        "serviceIDToTransfer": "AVC123456789012"
      }
    }
  },
  "notes": "Beware of the dog"
}
```


*Response — 201:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "vtOrderId": "VTORDAADD1122FF"
}
```


#### `GET` GET - Fetch a single Connect Order

**URI:** `/product-orders/{vtOrderId}{?includeMdfPatch,legacySpeedEnums,apiaryFetchSingleConnectOrder}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | VT Order ID starting with 'VTORD' | VTORD00AABBCC01 |
|  | boolean | no | Whether to include MDF patch information. FTTB orders only. |  |
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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NBN_ACKNOWLEDGED",
  "notes": "",
  "appointmentId": "APT000000000001",
  "orderType": "NBN_CONNECT",
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "656565"
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
        "ntdId": "",
        "uniDPortId": "2",
        "fibreUpgrade": "false",
        "ntdType": "1_PORT"
      },
      "speed": "TC450D20U",
      "staticIp": false,
      "serviceRestorationSla": "Standard",
      "ipAddress": "",
      "churn": {
        "type": "Service Transfer",
        "customerAuthorityDate": "",
        "serviceIDToTransfer": ""
      },
      "avcId": "AVC000000001234",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA"
    }
  },
  "subsequentInstallFeeConfirmed": false,
  "newDevelopmentsChargeConfirmed": false,
  "fibreUpgradeLiabilityConfirmed": false
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


#### `GET` GET - Fetch an FTTB Order with MDF Patch Information

**URI:** `/product-orders/{vtOrderId}{?includeMdfPatch,apiaryFetchSingleFTTBConnectOrderWithMDF}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | VT Order ID starting with 'VTORD' | VTORD00AABBCC01 |
|  | boolean | no | Whether to include MDF patch information. FTTB orders only. | true |


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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NBN_ACKNOWLEDGED",
  "notes": "",
  "appointmentId": "APT000000000001",
  "orderType": "NBN_CONNECT",
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "656565"
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
            "contactType": "primary",
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
          },
          "trackingId": "1234RG567890123400965008",
          "deliveryStatus": "AwaitingCollection",
          "latestEventDate": "2021-09-15T06:12:17Z"
        },
        "dslStabilityProfile": "",
        "potsInterconnect": "",
        "mdfPatchPair": "6PIE-12-345-DSL-6789 (LINE C Pairs) 12"
      }
    }
  },
  "subsequentInstallFeeConfirmed": false,
  "newDevelopmentsChargeConfirmed": false,
  "fibreUpgradeLiabilityConfirmed": false
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


#### `PATCH` PATCH - Update a Connect Order

**URI:** `/product-orders/{vtOrderId}{?legacySpeedEnums,updateOrderPlaceholder}`

Note: all of the request attributes are optional, but you must supply at least one of them.

Note: when you request a replacement NCD/NTD with `replacementDevice` and `dispatchDetails`,
you'll get an HTTP200 response if the initial checks pass but you'll have to wait for an
`OrderAmendAccepted` or `OrderAmendRejected` callback to know if your request was successful.

Required scope: update:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |
|  | boolean | no | Whether to display the old speed enumerations (100D20U, 250D25U, 1000D50U) for FTTP and HFC services |  |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "replacementDevice": true,
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
  "portIn": {
    "authorisationReferenceNumber": "A1B2C3D4",
    "dateOfBirth": "1981-02-03"
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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NBN_ACKNOWLEDGED",
  "notes": "",
  "appointmentId": "APT000000000001",
  "orderType": "NBN_CONNECT",
  "locationId": "LOC0000000001",
  "custData": {
    "business": false,
    "customerName": "John Smith",
    "orderRef": "656565"
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
        "ntdId": "",
        "uniDPortId": "2",
        "fibreUpgrade": "false",
        "ntdType": "1_PORT"
      },
      "speed": "TC450D20U",
      "staticIp": false,
      "serviceRestorationSla": "Standard",
      "ipAddress": "",
      "churn": {
        "type": "Service Transfer",
        "customerAuthorityDate": "",
        "serviceIDToTransfer": ""
      },
      "avcId": "AVC000000001234",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA"
    }
  },
  "subsequentInstallFeeConfirmed": false,
  "newDevelopmentsChargeConfirmed": false,
  "fibreUpgradeLiabilityConfirmed": false
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


#### `PATCH` PATCH - Resume a Connect Order

**URI:** `/product-orders/{vtOrderId}{?resume,resumeOrderPlaceholder}`

This is used to notify NBN that you have completed an action so the order can progress.

Example scenario where this endpoint would be used:

You receive an `RSPActionRequired` callback with the reason `Awaiting Device installation`

You inform the end user that they need to connect their NTD/NCD

End user connects the NTD/NCD

You PATCH to this endpoint to let NBN know the order can proceed

Required scope: update:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |
|  | string | yes | Value must be `true` | true |


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
  "vt_error_desc": ""
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


### Modify Speed Order

**URI:** `/product-orders{?orderType,apiaryModifySpeed}`


#### `POST` POST - Submit a Speed Modification Order

**URI:** `/product-orders?orderType=modify`

Create a speed modification order for an existing NBN service.

Required scope: create:product-orders


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "service": {
    "vtServiceId": "VT0000123",
    "nbn": {
      "speed": "TC450D20U"
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
  "vt_error_desc": "",
  "vtOrderId": "VTORDAADD1122FF"
}
```


#### `GET` GET - Fetch a Speed Modification Order

**URI:** `/product-orders/{vtOrderId}{?legacySpeedEnums,apiaryFetchSingleModifySpeedOrder}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |
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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NBN_ORDER_ACCEPTED",
  "notes": "",
  "orderType": "NBN_MODIFY_BANDWIDTH",
  "service": {
    "vtServiceId": "VT0000001",
    "supplierServiceId": "PRI000000000001",
    "nbn": {
      "speed": "TC450D20U"
    }
  },
  "custData": {
    "business": false
  }
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


### Modify DSL Stability Profile Order

**URI:** `/product-orders{?apiaryModifyStability}`


#### `POST` POST - Submit a DSL Stability Profile Modification Order

**URI:** `/product-orders?orderType=modify`

Create a DSL Stability Profile modification order for an existing FTTB/FTTN service.

Required scope: create:product-orders


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "service": {
    "vtServiceId": "VT0000123",
    "nbn": {
      "ncas": {
        "dslStabilityProfile": "Standard"
      }
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
  "vt_error_desc": "",
  "vtOrderId": "VTORDAADD1122FF"
}
```


#### `GET` GET - Fetch a DSL Stability Profile Modification Order

**URI:** `/product-orders/{vtOrderId}{?apiaryFetchSingleModifyStabilityOrder}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |


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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NBN_ORDER_ACCEPTED",
  "notes": "",
  "orderType": "NBN_MODIFY",
  "service": {
    "vtServiceId": "VT0000001",
    "supplierServiceId": "PRI000000000001",
    "nbn": {
      "ncas": {
        "dslStabilityProfile": "Standard"
      }
    }
  },
  "custData": {
    "business": false
  }
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


### Modify Service Restoration SLA Order

**URI:** `/product-orders{?apiaryModifyServiceRestorationSla}`


#### `POST` POST - Submit a Service Restoration SLA Modification Order

**URI:** `/product-orders?orderType=modify`

Create a Service Restoration SLA modification order for an existing service.

Required scope: create:product-orders


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "service": {
    "vtServiceId": "VT0000123",
    "nbn": {
      "serviceRestorationSla": "Standard"
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
  "vt_error_desc": "",
  "vtOrderId": "VTORDAADD1122FF"
}
```


#### `GET` GET - Fetch a Service Restoration SLA Modification Order

**URI:** `/product-orders/{vtOrderId}{?apiaryFetchSingleModifyStabilityOrder}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |


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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NBN_ORDER_ACCEPTED",
  "notes": "",
  "orderType": "NBN_MODIFY",
  "service": {
    "vtServiceId": "VT0000001",
    "supplierServiceId": "PRI000000000001",
    "nbn": {
      "serviceRestorationSla": "Standard"
    }
  },
  "custData": {
    "business": false
  }
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


### Modify Mobile Plan Order ALPHA

**URI:** `/product-orders{?orderType,apiaryModifyMobilePlan}`


#### `POST` POST - Submit a Mobile Plan Change Order

**URI:** `/product-orders?orderType=modify`

Change the plan of an existing mobile service.

Note that plan changes are applied at the end of each month. You can check the effective date when you GET the resulting order.

Required scopes: create:product-orders  create:mobile-orders


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "service": {
    "vtServiceId": "VT0000123",
    "mobile": {
      "mobilePlan": "MPP1"
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
  "vt_error_desc": "",
  "vtOrderId": "VTORDAADD1122FF"
}
```


#### `GET` GET - Fetch a Mobile Plan Change Order

**URI:** `/product-orders/{vtOrderId}{?apiaryFetchSingleMobilePlanChangeOrder}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |


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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "NEW",
  "notes": "",
  "orderType": "MOBILE_MODIFY",
  "service": {
    "vtServiceId": "VT0000001",
    "mobile": {
      "mobilePlan": "MPP1",
      "effectiveDate": "2025-05-01"
    }
  }
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


### Disconnect Order

**URI:** `/product-orders{?apiaryProductDisconnectOrders}`


#### `POST` POST - Submit a Disconnect Order

**URI:** `/product-orders?orderType=disconnect`

This is used to cancel an active service, (normally at the request of the end user,
e.g. when they're moving out of a house).

Required scope: create:product-orders


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "service": {
    "vtServiceId": "VT0000123"
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
  "vt_error_desc": "",
  "vtOrderId": "VTORDAADD1122FF"
}
```


#### `GET` GET - Fetch a Disconnect Order

**URI:** `/product-orders/{vtOrderId}{?apiaryFetchSingleDisconnectOrder}`

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | VTORD00AABBCC01 |


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
  "vtOrderId": "VTORDAADD1122FF",
  "status": "VT_ORDER_COMPLETED",
  "notes": "",
  "orderType": "NBN_DISCONNECT",
  "service": {
    "vtServiceId": "VT0000001",
    "supplierServiceId": "PRI000000000001"
  }
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


### Fetch Multiple Orders

**URI:** `/product-orders{?status,page,limit,legacySpeedEnums,apiaryMultipleOrders,inFlightOnly}`


#### `GET` GET /product-orders{?status,page,limit,legacySpeedEnums,apiaryMultipleOrders,inFlightOnly}

Please note that Connect, Modify, and Disconnect orders will return different structures (with some common elements like vtOrderId).

This endpoint is paginated.

Required scope: read:product-orders

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | Optional order status filter |  |
|  | number | no | Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty services array |  |
|  | number | no | Maximum number of services on a page. Default 100. Minimum 10. Maximum 1000. |  |
|  | boolean | no | Whether to display the old speed enumerations (100D20U, 250D25U, 1000D50U) for FTTP and HFC services |  |
|  | boolean | no | Limit results to in-flight orders (anything that hasn't been cancelled or completed) |  |


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
  "productOrders": [
    {
      "vtOrderId": "VTORDAADD1122FF",
      "status": "NBN_ACKNOWLEDGED",
      "notes": "",
      "appointmentId": "APT000000000001",
      "orderType": "NBN_CONNECT",
      "locationId": "LOC0000000001",
      "custData": {
        "business": false,
        "customerName": "John Smith",
        "orderRef": "656565"
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
            "ntdId": "",
            "uniDPortId": "2",
            "fibreUpgrade": "false",
            "ntdType": "1_PORT"
          },
          "speed": "TC450D20U",
          "staticIp": false,
          "serviceRestorationSla": "Standard",
          "ipAddress": "",
          "churn": {
            "type": "Service Transfer",
            "customerAuthorityDate": "",
            "serviceIDToTransfer": ""
          },
          "avcId": "AVC000000001234",
          "vlanId": 1234,
          "cTag": 1234,
          "sTag": 2345,
          "poiId": "3NBA"
        }
      },
      "subsequentInstallFeeConfirmed": false,
      "newDevelopmentsChargeConfirmed": false,
      "fibreUpgradeLiabilityConfirmed": false
    },
    {
      "vtOrderId": "VTORDAADD1122FF",
      "status": "VT_ORDER_COMPLETED",
      "notes": "",
      "orderType": "NBN_DISCONNECT",
      "service": {
        "vtServiceId": "VT0000001",
        "supplierServiceId": "PRI000000000001"
      }
    }
  ]
}
```
