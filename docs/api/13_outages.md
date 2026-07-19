# Outages

## Outages

This endpoint deals with both types of NBN outage - Change Requests (planned outages), and Trouble Tickets (unplanned outages).

There's only one kind of Change Request, but four types of Trouble Ticket:

Infrastructure Restoration: raised for network related incidents (i.e. unplanned outages).

Infrastructure Event: raised for battery alarms.

Service Restoration: raised when an end user service is impacted (e.g. faulty connection or service degradation)

Service Request: raised for any activity that is not end user service impacting such as for address inconsistency. Note that these usually don't contain product or service IDs, which means ownership of them can't be determined and they can't be made available to you.


### List NBN outages

**URI:** `/outages{?showPast,hideFuture,search,minCreatedUtc,maxCreatedUtc,page,limit}`


#### `GET` GET /outages{?showPast,hideFuture,search,minCreatedUtc,maxCreatedUtc,page,limit}

This endpoint is paginated.

Required scope: read:outages

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | [Optional] Search for outages by AVC ID, PRI ID, OVC ID, BPI ID, VT Service ID, CRQ ID, INC ID, or POI ID. Please note that POI ID searches are special because they will only return active services at |  |
|  | string | no | [Optional] Set this to 'true' to include terminal outages (Cancelled/Completed Change Requests, and Cancelled/Closed/Rejected Trouble Tickets). By default, past outages are hidden |  |
|  | string | no | [Optional] Set this to 'true' to hide Scheduled Change Requests. By default, future outages are shown |  |
|  | string | no | [Optional] Limit results to only those outages created at or after this timestamp. UTC timezone, [YYYY]-[MM]-[DD]T[HH]:[MM]:[SS]Z format. |  |
|  | string | no | [Optional] Limit results to only those outages created at or before this timestamp. UTC timezone, [YYYY]-[MM]-[DD]T[HH]:[MM]:[SS]Z format. |  |
|  | number | no | [Optional] Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty outages array |  |
|  | number | no | [Optional] Maximum number of outages on a page. Default 100. Minimum 10. Maximum 1000. |  |


*Request:*

Headers:

- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "outages": [
    {
      "type": "ChangeRequest",
      "id": "CRQ000000000001",
      "created": "2023-01-31T01:23:45Z",
      "status": "InProgress",
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    {
      "type": "TroubleTicket",
      "id": "INC000000000002",
      "created": "2023-01-31T01:23:45Z",
      "status": "Acknowledged",
      "nbnProductIds": [
        "PRI000000000001"
      ],
      "nbnServiceIds": [
        "AVC000000000001"
      ],
      "vtServiceIds": [
        "VT0000001"
      ]
    }
  ],
  "_meta": {
    "total_records": 2,
    "page": 1,
    "limit": 100,
    "count": 2
  },
  "_links": {
    "self": "/api/v1/services?page=1&limit=100",
    "first": "/api/v1/services?page=1&limit=100",
    "last": "/api/v1/services?page=1&limit=100"
  }
}
```


### Retrieve details for a specific outage

**URI:** `/outages{?apiaryOutagesDetailsSection}`

Please note that NBN will not return details for Trouble Tickets after they're closed.


#### `GET` GET - Change Request

**URI:** `/outages/{outageId}{?apiaryChangeRequest}`

Change Requests all use the same schema

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Change Request ID (e.g. CRQ000000000001) or Trouble Ticket ID (e.g. INC000000000001) | CRQ000000000001 |


*Request:*

Headers:

- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "CRQ000000000001",
  "status": "InProgress",
  "subStatus": "Held",
  "changeType": "Standard",
  "category": "Network",
  "requestDate": "2020-01-20T12:00:00Z",
  "duration": "6",
  "plannedStartDate": "2020-01-24T12:00:00Z",
  "plannedEndDate": "2020-01-24T18:00:00Z",
  "impactedLocation": "VIC-Urban",
  "resolution": {
    "task": [
      {
        "name": "interruption1",
        "duration": "30",
        "startDateTime": "2020-01-24T12:00:00Z"
      }
    ]
  },
  "vtServiceIds": [
    "VT0000001",
    "VT0000002"
  ],
  "nbnServiceIds": [
    "AVC000000000001",
    "AVC000000000002"
  ],
  "nbnProductIds": [
    "PRI000000000001",
    "PRI000000000002"
  ]
}
```


#### `GET` GET - Infrastructure Restoration Trouble Ticket

**URI:** `/outages/{outageId}{?apiaryInfrastructureRestorationTroubleTicket}`

Infrastructure Restoration tickets are raised for network related incidents

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Change Request ID (e.g. CRQ000000000001) or Trouble Ticket ID (e.g. INC000000000001) | INC000000000001 |


*Request:*

Headers:

- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "INC000000000001",
  "ticketType": "Service Restoration",
  "creationDate": "2020-01-24T12:00:00Z",
  "plannedRemediationDate": "2020-01-24T12:00:00Z",
  "resolutionDate": "2020-01-24T12:00:00Z",
  "closeDate": "2020-01-24T12:00:00Z",
  "note": [
    {
      "text": "Note description.",
      "date": "2020-01-24T12:00:00Z"
    }
  ],
  "vtServiceIds": [
    "VT0000001",
    "VT0000002"
  ],
  "nbnProductIds": [
    "PRI000000000001",
    "PRI000000000002"
  ],
  "nbnServiceIds": [
    "AVC000000000001",
    "AVC000000000002"
  ],
  "priority": "High",
  "targetResolutionDate": "2020-01-24",
  "estimatedRestorationDate": "2020-01-24T12:00:00Z",
  "reasonCode": {
    "code": "",
    "reason": ""
  }
}
```


#### `GET` GET - Infrastructure Event Trouble Ticket

**URI:** `/outages/{outageId}{?apiaryInfrastructureEventTroubleTicket}`

Infrastructure Event tickets are raised for battery alarms

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Change Request ID (e.g. CRQ000000000001) or Trouble Ticket ID (e.g. INC000000000001) | INC000000000001 |


*Request:*

Headers:

- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "INC000000000001",
  "ticketType": "Service Restoration",
  "creationDate": "2020-01-24T12:00:00Z",
  "plannedRemediationDate": "2020-01-24T12:00:00Z",
  "resolutionDate": "2020-01-24T12:00:00Z",
  "closeDate": "2020-01-24T12:00:00Z",
  "note": [
    {
      "text": "Note description.",
      "date": "2020-01-24T12:00:00Z"
    }
  ],
  "vtServiceIds": [
    "VT0000001",
    "VT0000002"
  ],
  "nbnProductIds": [
    "PRI000000000001",
    "PRI000000000002"
  ],
  "nbnServiceIds": [
    "AVC000000000001",
    "AVC000000000002"
  ],
  "priority": "High",
  "priorityAssist": "No",
  "relatedResource": [
    {
      "id": "NTD000000000001",
      "@referredType": "NTD"
    }
  ]
}
```


#### `GET` GET - Service Restoration Trouble Ticket

**URI:** `/outages/{outageId}{?apiaryServiceRestorationTroubleTicket}`

Service Restoration tickets are raised when an end user service is impacted

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Change Request ID (e.g. CRQ000000000001) or Trouble Ticket ID (e.g. INC000000000001) | INC000000000001 |


*Request:*

Headers:

- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "INC000000000001",
  "ticketType": "Service Restoration",
  "creationDate": "2020-01-24T12:00:00Z",
  "plannedRemediationDate": "2020-01-24T12:00:00Z",
  "resolutionDate": "2020-01-24T12:00:00Z",
  "closeDate": "2020-01-24T12:00:00Z",
  "note": [
    {
      "text": "Note description.",
      "date": "2020-01-24T12:00:00Z"
    }
  ],
  "vtServiceIds": [
    "VT0000001",
    "VT0000002"
  ],
  "nbnProductIds": [
    "PRI000000000001",
    "PRI000000000002"
  ],
  "nbnServiceIds": [
    "AVC000000000001",
    "AVC000000000002"
  ],
  "severity": "Medium",
  "category": "",
  "targetCommitmentDate": "2020-01-24T12:00:00Z",
  "endUserEngagementType": "Appointment",
  "priorityAssist": "No",
  "endUserType": "Residential",
  "reasonCode": {
    "code": "",
    "reason": ""
  },
  "relatedResource": [
    {
      "id": "NTD000000000001",
      "@referredType": "NTD"
    }
  ],
  "relatedAppointment": {
    "id": "APT000000000001"
  },
  "relatedPlace": {
    "id": "LOC000000000001"
  }
}
```


#### `GET` GET - Service Request Trouble Ticket

**URI:** `/outages/{outageId}{?apiaryServiceRequestTroubleTicket}`

Raised for any activity that is not end user service impacting such as for address inconsistency. Note that these usually don't contain product or service IDs, which means you won't normally see them.

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Change Request ID (e.g. CRQ000000000001) or Trouble Ticket ID (e.g. INC000000000001) | INC000000000001 |


*Request:*

Headers:

- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "INC000000000001",
  "ticketType": "Service Restoration",
  "creationDate": "2020-01-24T12:00:00Z",
  "plannedRemediationDate": "2020-01-24T12:00:00Z",
  "resolutionDate": "2020-01-24T12:00:00Z",
  "closeDate": "2020-01-24T12:00:00Z",
  "note": [
    {
      "text": "Note description.",
      "date": "2020-01-24T12:00:00Z"
    }
  ],
  "vtServiceIds": [
    "VT0000001",
    "VT0000002"
  ],
  "nbnProductIds": [
    "PRI000000000001",
    "PRI000000000002"
  ],
  "nbnServiceIds": [
    "AVC000000000001",
    "AVC000000000002"
  ],
  "severity": "Medium",
  "category": "",
  "reasonCode": {
    "code": "",
    "reason": ""
  },
  "relatedAppointment": {
    "id": "APT000000000001"
  },
  "relatedPlace": {
    "id": "LOC000000000001"
  },
  "physicalAddress": {
    "geographicDatum": "GDA94"
  },
  "anticipatedPrimaryAccessTechnology": "Fibre",
  "expectedServiceabilityClass": "1",
  "serviceabilityClass": "3",
  "fnnServiceIdentifier": "0399999999",
  "ullServiceIdentifier": "1601234567",
  "relatedEntityRef": {
    "id": "ORD000000000001",
    "@referredType": "ProductOrder"
  }
}
```


### Example Outage Callbacks

**URI:** `/example-callback-url{?apiaryOutagesCallbacksSection}`

Note that the roles are reversed in this section, (i.e. our machine will POST the callback to your server).


#### `POST` ChangeRequestCreation Callback (ChangeScheduled)

**URI:** `/example-callback-url{?apiaryChangeRequestCreationCallbackChangeScheduled}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "IDGA4FL5DT1GIAQP59NZQO6XO4P75C",
  "eventTime": "2021-03-04T16:38:22+00:00",
  "eventType": "ChangeRequestCreationNotification",
  "event": {
    "id": "CRQ000002652421",
    "notificationType": "ChangeScheduled",
    "reason": "",
    "objectType": "ChangeRequest",
    "status": "Scheduled",
    "changeRequest": {
      "id": "CRQ000002652421",
      "status": "Scheduled",
      "changeType": "Normal",
      "category": "Network",
      "description": "This notification is to let you know that nbn will be performing network maintenance work. Due to this activity the services listed below will experience a loss of connectivity for up to 0 hrs 50 mins during the change window",
      "requestDate": "2021-03-04T05:29:37Z",
      "duration": "745.00",
      "plannedStartDate": "2021-03-26T00:00:00Z",
      "plannedEndDate": "2021-04-26T00:00:00Z",
      "impactedLocation": "test",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption3",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption4",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption5",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestAttributeValueChange Callback (ChangeWindowUpdated)

**URI:** `/example-callback-url{?apiaryChangeRequestAttributeValueChangeCallbackChangeWindowUpdated}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "ChangeRequestAttributeValueChangeNotification",
  "event": {
    "id": "CRQ000000000000",
    "notificationType": "ChangeWindowUpdated",
    "reason": "",
    "status": "Scheduled",
    "objectType": "ChangeRequest",
    "changeRequest": {
      "id": "CRQ000000000000",
      "status": "Scheduled",
      "changeType": "Standard",
      "category": "Network",
      "description": "Description of the change request.",
      "requestDate": "2020-01-20T12:00:00Z",
      "duration": "6",
      "plannedStartDate": "2020-01-26T12:00:00Z",
      "plannedEndDate": "2020-01-26T18:00:00Z",
      "impactedLocation": "VIC-Urban",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "30",
            "startDateTime": "2020-01-26T12:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "30",
            "startDateTime": "2020-01-26T14:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestAttributeValueChange Callback (ChangeUpdated)

**URI:** `/example-callback-url{?apiaryChangeRequestAttributeValueChangeCallbackChangeUpdated}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "ChangeRequestAttributeValueChangeNotification",
  "event": {
    "id": "CRQ000000000000",
    "notificationType": "ChangeUpdated",
    "reason": "",
    "status": "Scheduled",
    "objectType": "ChangeRequest",
    "changeRequest": {
      "id": "CRQ000000000000",
      "status": "Scheduled",
      "changeType": "Standard",
      "category": "Network",
      "description": "Description of the change request. It has been updated.",
      "requestDate": "2020-01-20T12:00:00Z",
      "duration": "6",
      "plannedStartDate": "2020-01-24T12:00:00Z",
      "plannedEndDate": "2020-01-24T18:00:00Z",
      "impactedLocation": "VIC-Urban",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "30",
            "startDateTime": "2020-01-24T12:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "30",
            "startDateTime": "2020-01-24T14:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestStateChange Callback (ChangeStarted)

**URI:** `/example-callback-url{?apiaryChangeRequestStateChangeCallbackChangeStarted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "IDGAJQYTALF7OAQP5TIFQO7HIK8LAL",
  "eventTime": "2021-03-04T20:04:52+00:00",
  "eventType": "ChangeRequestStateChangeNotification",
  "event": {
    "id": "CRQ000002652441",
    "notificationType": "ChangeStarted",
    "reason": "",
    "objectType": "ChangeRequest",
    "status": "InProgress",
    "changeRequest": {
      "id": "CRQ000002652441",
      "status": "InProgress",
      "changeType": "Normal",
      "category": "Network",
      "description": "This notification is to let you know that nbn will be performing network maintenance work. Due to this activity the services listed below will experience a loss of connectivity for up to 0 hrs 10 mins during the change window",
      "requestDate": "2021-03-04T08:43:29Z",
      "duration": "336.00",
      "plannedStartDate": "2021-03-22T05:30:00Z",
      "plannedEndDate": "2021-04-05T04:30:00Z",
      "impactedLocation": "LOC000018740198",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "10",
            "startDateTime": "2021-03-21T18:30:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestStateChange Callback (AdditionalActivityRequired)

**URI:** `/example-callback-url{?apiaryChangeRequestStateChangeCallbackAdditionalActivityRequired}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "IDGA4FL5DT1GIAQP6VR0QO8JP1T2AH",
  "eventTime": "2021-03-05T09:57:09+00:00",
  "eventType": "ChangeRequestStateChangeNotification",
  "event": {
    "id": "CRQ000002652801",
    "notificationType": "AdditionalActivityRequired",
    "reason": "",
    "objectType": "ChangeRequest",
    "status": "InProgress",
    "subStatus": "Held",
    "changeRequest": {
      "id": "CRQ000002652801",
      "status": "InProgress",
      "subStatus": "Held",
      "changeType": "Normal",
      "category": "Network",
      "description": "This notification is to let you know that nbn will be performing network maintenance work. Due to this activity the services listed below will experience a loss of connectivity for up to 0 hrs 50 mins during the change window",
      "requestDate": "2021-03-04T22:38:43Z",
      "duration": "721.00",
      "plannedStartDate": "2021-03-29T00:00:00Z",
      "plannedEndDate": "2021-04-28T00:00:00Z",
      "impactedLocation": "test",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption3",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption4",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption5",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestStateChange Callback (AdditionalActivityCompleted)

**URI:** `/example-callback-url{?apiaryChangeRequestStateChangeCallbackAdditionalActivityCompleted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "IDGA4FL5DT1GIAQP6VZNQO8JXOT2C9",
  "eventTime": "2021-03-05T10:02:10+00:00",
  "eventType": "ChangeRequestStateChangeNotification",
  "event": {
    "id": "CRQ000002652801",
    "notificationType": "AdditionalActivityCompleted",
    "reason": "",
    "objectType": "ChangeRequest",
    "status": "InProgress",
    "changeRequest": {
      "id": "CRQ000002652801",
      "status": "InProgress",
      "changeType": "Normal",
      "category": "Network",
      "description": "This notification is to let you know that nbn will be performing network maintenance work. Due to this activity the services listed below will experience a loss of connectivity for up to 0 hrs 50 mins during the change window",
      "requestDate": "2021-03-04T22:38:43Z",
      "duration": "721.00",
      "plannedStartDate": "2021-03-29T00:00:00Z",
      "plannedEndDate": "2021-04-28T00:00:00Z",
      "impactedLocation": "test",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption3",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption4",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          },
          {
            "name": "interruption5",
            "duration": "10",
            "startDateTime": "2021-03-30T13:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestStateChange Callback (ChangeCompleted)

**URI:** `/example-callback-url{?apiaryChangeRequestStateChangeCallbackChangeCompleted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "IDGAJQYTALF7OAQP6T64QO8H45AS5D",
  "eventTime": "2021-03-05T09:07:23+00:00",
  "eventType": "ChangeRequestStateChangeNotification",
  "event": {
    "id": "CRQ000002652421",
    "notificationType": "ChangeCompleted",
    "reason": "",
    "objectType": "ChangeRequest",
    "status": "Completed",
    "changeRequest": {
      "id": "CRQ000002652421",
      "status": "Completed",
      "changeType": "Normal",
      "category": "Network",
      "description": "This notification is to let you know that nbn will be performing network maintenance work. Due to this activity the services listed below will experience a loss of connectivity for up to 0 hrs 50 mins during the change window",
      "requestDate": "2021-03-04T05:29:37Z",
      "duration": "745.00",
      "plannedStartDate": "2021-03-26T00:00:00Z",
      "plannedEndDate": "2021-04-26T00:00:00Z",
      "impactedLocation": "test",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption3",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption4",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          },
          {
            "name": "interruption5",
            "duration": "10",
            "startDateTime": "2021-04-18T14:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` ChangeRequestRemove Callback (ChangeCancelled)

**URI:** `/example-callback-url{?apiaryChangeRequestRemoveCallbackChangeCancelled}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "IDGAJQYTALF7OAQP6U4VQO8I2WASMO",
  "eventTime": "2021-03-05T09:28:05+00:00",
  "eventType": "ChangeRequestRemoveNotification",
  "event": {
    "id": "CRQ000002652701",
    "notificationType": "ChangeCancelled",
    "reason": "",
    "objectType": "ChangeRequest",
    "status": "Cancelled",
    "changeRequest": {
      "id": "CRQ000002652701",
      "status": "Cancelled",
      "changeType": "Normal",
      "category": "Network",
      "description": "This notification is to let you know that nbn will be performing network maintenance work. Due to this activity the services listed below will experience a loss of connectivity for up to 0 hrs 50 mins during the change window",
      "requestDate": "2021-03-04T22:11:44Z",
      "duration": "336.00",
      "plannedStartDate": "2021-04-05T00:00:00Z",
      "plannedEndDate": "2021-04-19T00:00:00Z",
      "impactedLocation": "test",
      "resolution": {
        "task": [
          {
            "name": "interruption1",
            "duration": "10",
            "startDateTime": "2021-04-05T14:00:00Z"
          },
          {
            "name": "interruption2",
            "duration": "10",
            "startDateTime": "2021-04-05T14:00:00Z"
          },
          {
            "name": "interruption3",
            "duration": "10",
            "startDateTime": "2021-04-05T14:00:00Z"
          },
          {
            "name": "interruption4",
            "duration": "10",
            "startDateTime": "2021-04-05T14:00:00Z"
          },
          {
            "name": "interruption5",
            "duration": "10",
            "startDateTime": "2021-04-05T14:00:00Z"
          }
        ]
      },
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    },
    "reasonCode": ""
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketAttributeValueChange Callback (AmendAccepted)

**URI:** `/example-callback-url{?apiaryTroubleTicketAttributeValueChangeCallbackAmendAccepted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketAttributeValueChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "AmendAccepted",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketAttributeValueChange Callback (BatteryEventReminder)

**URI:** `/example-callback-url{?apiaryTroubleTicketAttributeValueChangeCallbackBatteryEventReminder}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketAttributeValueChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "BatteryEventReminder",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Infrastructure Event",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketAttributeValueChange Callback (ClearanceRejectionDeclined)

**URI:** `/example-callback-url{?apiaryTroubleTicketAttributeValueChangeCallbackClearanceRejectionDeclined}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketAttributeValueChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "ClearanceRejectionDeclined",
    "reason": "Rejection Declined - Fault resolved; new trouble ticket required",
    "status": "Resolved",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "NCRD1408",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketAttributeValueChange Callback (PlannedRemediationDate)

**URI:** `/example-callback-url{?apiaryTroubleTicketAttributeValueChangeCallbackPlannedRemediationDate}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketAttributeValueChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "PlannedRemediationDate",
    "reason": "",
    "status": "InProgress",
    "subStatus": "Held",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Infrastructure Restoration",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketAttributeValueChange Callback (TicketUpdatedNBNCo)

**URI:** `/example-callback-url{?apiaryTroubleTicketAttributeValueChangeCallbackTicketUpdatedNBNCo}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketAttributeValueChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TicketUpdatedNBNCo",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketCreate Callback (TroubleTicketAccepted)

**URI:** `/example-callback-url{?apiaryTroubleTicketCreateCallbackTroubleTicketAccepted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketCreateEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TroubleTicketAccepted",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketCreate Callback (TroubleTicketCreated)

**URI:** `/example-callback-url{?apiaryTroubleTicketCreateCallbackTroubleTicketCreated}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketCreateEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TroubleTicketCreated",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Infrastructure Restoration",
    "category": "Force Majeure",
    "subCategory": "Order",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketDelete Callback (TroubleTicketCancelled)

**URI:** `/example-callback-url{?apiaryTroubleTicketDeleteCallbackTroubleTicketCancelled}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketDeleteEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TroubleTicketCancelled",
    "reason": "",
    "status": "Cancelled",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Infrastructure Restoration",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketInformationRequired Callback (AdditionalInformationRequired)

**URI:** `/example-callback-url{?apiaryTroubleTicketInformationRequiredCallbackAdditionalInformationRequired}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketInformationRequiredEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "AdditionalInformationRequired",
    "reason": "More Information Required",
    "status": "InProgress",
    "subStatus": "Pending",
    "objectType": "TroubleTicket",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "AMIF9500",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketInformationRequired Callback (AppointmentRequired)

**URI:** `/example-callback-url{?apiaryTroubleTicketInformationRequiredCallbackAppointmentRequired}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketInformationRequiredEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "AppointmentRequired",
    "reason": "There is a mismatch between the End User Type specified when reserving the Appointment and the Trouble Ticket to which the Appointment is associated",
    "status": "InProgress",
    "subStatus": "Pending",
    "objectType": "TroubleTicket",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "015510",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketInformationRequired Callback (AppointmentRescheduleRequired)

**URI:** `/example-callback-url{?apiaryTroubleTicketInformationRequiredCallbackAppointmentRescheduleRequired}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketInformationRequiredEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "AppointmentRescheduleRequired",
    "reason": "Reschedule Required - Network shortfall has been resolved",
    "status": "InProgress",
    "subStatus": "Pending",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "XNSF9200",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketResolved Callback (TroubleTicketResolved)

**URI:** `/example-callback-url{?apiaryTroubleTicketResolvedCallbackTroubleTicketResolved}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketResolvedEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TroubleTicketResolved",
    "reason": "No Fault Found - No Action Required by NBN Co",
    "status": "Resolved",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "ANFF1408",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketStatusChange Callback (MonitoringCompleted)

**URI:** `/example-callback-url{?apiaryTroubleTicketStatusChangeCallbackMonitoringCompleted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketStatusChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "MonitoringCompleted",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "Performance latency",
    "subCategory": "Performance Incident",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketStatusChange Callback (MonitoringStarted)

**URI:** `/example-callback-url{?apiaryTroubleTicketStatusChangeCallbackMonitoringStarted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketStatusChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "MonitoringStarted",
    "reason": "Monitoring Started. The incident is being monitored.",
    "status": "InProgress",
    "subStatus": "Monitoring",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "Performance latency",
    "subCategory": "Performance Incident",
    "reasonCode": "NNMP9700",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketStatusChange Callback (NBNActionCompleted)

**URI:** `/example-callback-url{?apiaryTroubleTicketStatusChangeCallbackNBNActionCompleted}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketStatusChangeEvent",
  "event": {
    "id": "INC000000000001",
    "notificationType": "NBNActionCompleted",
    "reason": "",
    "status": "InProgress",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketStatusChange Callback (NBNActionRequired)

**URI:** `/example-callback-url{?apiaryTroubleTicketStatusChangeCallbackNBNActionRequired}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketStatusChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "NBNActionRequired",
    "reason": "There is a known NBN network fault which is related to this trouble ticket. We will provide an update as soon as available.",
    "status": "InProgress",
    "subStatus": "Held",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "NKNF9100",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketStatusChange Callback (TroubleTicketClosed)

**URI:** `/example-callback-url{?apiaryTroubleTicketStatusChangeCallbackTroubleTicketClosed}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketStatusChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TroubleTicketClosed",
    "reason": "No Fault Found - No Action Required by NBN Co",
    "status": "Closed",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "subCategory": "Service Fault",
    "reasonCode": "ANFF1408",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*


#### `POST` TroubleTicketStatusChange Callback (TroubleTicketRejected)

**URI:** `/example-callback-url{?apiaryTroubleTicketStatusChangeCallbackTroubleTicketRejected}`


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "eventUuid": "eda42ff0-cb58-493e-aa64-5c95940003b8",
  "eventTime": "2020-01-24T12:00:00+00:00",
  "eventType": "TroubleTicketStatusChangeEvent",
  "event": {
    "id": "INC000000000000",
    "notificationType": "TroubleTicketRejected",
    "reason": "There is a mismatch between the End User Type specified when reserving the Appointment and the Trouble Ticket to which the Appointment is associated",
    "status": "Rejected",
    "objectType": "TroubleTicketEvent",
    "ticketType": "Service Restoration",
    "category": "No data connection",
    "reasonCode": "015510",
    "troubleTicket": {
      "nbnServiceIds": [
        "AVC000000000001",
        "AVC000000000002"
      ],
      "nbnProductIds": [
        "PRI000000000001",
        "PRI000000000002"
      ],
      "vtServiceIds": [
        "VT0000001",
        "VT0000002"
      ]
    }
  }
}
```


*Response — 200:*
