# Out of Band Notifications

## Out of Band Notifications

This endpoint lets you manage contacts who will be notified about certain events.

Currently only NBN outage notifications via SMS and email are supported, however other triggers and channels may be added later.

Please note that while email notifications are free, SMS notifications will incur a monthly fee.

End user contacts will be automatically removed if their service is churned away or disconnected.

The text of outage notifications will vary based on the outage, but this is the general format of an end user notification:

``
Hi <end_user_name>,

nbn has scheduled a planned outage in your area. Your internet service at 123 Fake St may experience interruptions between the following times: 10/02/2023 10:00 AEST to 10/02/2023 13:00 AEST, 12/02/2023 10:00 AEST to 12/02/2023 14:30 AEST.

Please contact us if you require more information.

Regards,
<rsp_name>

``

This is the same notification, but for an RSP contact:

``
Please be advised,

nbn has announced a planned outage affecting the following services: VT0000001, VT0000002.

Outage ID: CRQ000000000001.

Planned start: 11/01/2023 05:00 AEST.

Planned end: 13/02/2023 23:00 AEST.

Interruptions are scheduled between the following times: 10/02/2023 10:00 AEST to 10/02/2023 13:00 AEST, 12/02/2023 10:00 AEST to 12/02/2023 14:30 AEST.

This work is part of a Fixed Wireless network upgrade to increase capacity on the  Transmission Network (TX) to match the newly upgraded Radio Access Network (RAN).  This planned outage will align the TX which transmits traffic between multiple towers  to the nbn fibre access point. To avoid services being offline for several days, nbn will  break up the work and perform it overnight (Midnight-9am) and on weekends (6am-6pm Sat and Sun).  Depending on the extent of the upgrade, services will be disrupted by 1-2 outages of up  to 12 hours each. The process to upgrade this TX will span several days with only 2 of  these days being customer impacting. Depending on what tower the end-customers are connected  to in the network topology, they may experience further interruptions that will be  communicated via another CRQ.

Regards,
Virtutel

``


### Register New End User Contact

**URI:** `/out-of-band-notifications/contacts{?apiaryOOBCPostEndUser}`


#### `POST` POST /out-of-band-notifications/contacts{?apiaryOOBCPostEndUser}

End user contacts receive notifications about a single service.

Only one end user contact can be registered for each Virtutel Service ID.

Required scope: create:out-of-band-notifications:contacts


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`

```json
{
  "contactType": "end_user",
  "toName": "John",
  "fromName": "Pebcak Internet Solutions",
  "channel": "sms",
  "recipient": "61400111222",
  "triggers": [
    "outage"
  ],
  "vtServiceId": "VTAABBCCD",
  "timezone": "Australia/Melbourne",
  "businessHoursOnly": false
}
```


*Response — 201:*

Contact successfully added

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "OBC0000000001",
  "contactType": "end_user",
  "toName": "John",
  "fromName": "Pebcak Internet Solutions",
  "channel": "sms",
  "recipient": "61400111222",
  "triggers": [
    "outage"
  ],
  "vtServiceId": "VTAABBCCD",
  "timezone": "Australia/Melbourne",
  "enabled": true,
  "businessHoursOnly": false,
  "replyTo": ""
}
```


*Response — 400:*

Example Bad Request Error (normally caused when one or more values are missing/invalid)

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_short_error": "bad_request",
  "vt_error_desc": "'contactType' must be one of the following ['end_user', 'rsp']"
}
```


### Register New RSP Contact

**URI:** `/out-of-band-notifications/contacts{?apiaryOOBCPostRSP}`


#### `POST` POST /out-of-band-notifications/contacts{?apiaryOOBCPostRSP}

RSP contacts receive notifications about every service.

Up to three RSP contacts are allowed.

Required scope: create:out-of-band-notifications:contacts


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`

```json
{
  "contactType": "rsp",
  "channel": "email",
  "recipient": "alerts@example.com",
  "triggers": [
    "outage"
  ],
  "timezone": "Australia/Melbourne",
  "businessHoursOnly": false
}
```


*Response — 201:*

Contact successfully added

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "id": "OBC0000000002",
  "contactType": "rsp",
  "toName": "",
  "fromName": "",
  "channel": "email",
  "recipient": "alerts@example.com",
  "triggers": [
    "outage"
  ],
  "vtServiceId": "",
  "timezone": "Australia/Melbourne",
  "enabled": true,
  "businessHoursOnly": false,
  "replyTo": ""
}
```


*Response — 400:*

Example Bad Request Error (normally caused when one or more values are missing/invalid)

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_short_error": "bad_request",
  "vt_error_desc": "'contactType' must be one of the following ['end_user', 'rsp']"
}
```


### Fetch Multiple Contacts

**URI:** `/out-of-band-notifications/contacts{?search,contactType,enabled,page,limit,apiaryOOBCGetList}`


#### `GET` GET /out-of-band-notifications/contacts{?search,contactType,enabled,page,limit,apiaryOOBCGetList}

This endpoint is paginated.

Required scope: read:out-of-band-notifications:contacts

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | [Optional] Search for contacts by OBC ID, VT Service ID, Recipient, or all/part of their From Name or To Name |  |
|  | string | no | [Optional] Filter by contact type. Allowed values: 'end_user', or 'rsp'. |  |
|  | boolean | no | [Optional] Filter by enabled value. Allowed values: 'true' or 'false' |  |
|  | number | no | [Optional] Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty contacts array |  |
|  | number | no | [Optional] Maximum number of contacts on a page. Default 100. Minimum 10. Maximum 1000. |  |


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
  "contacts": [
    {
      "id": "OBC0000000001",
      "contactType": "end_user",
      "toName": "John",
      "fromName": "Pebcak Internet Solutions",
      "channel": "sms",
      "recipient": "61400111222",
      "triggers": [
        "outage"
      ],
      "vtServiceId": "VTAABBCCD",
      "timezone": "Australia/Melbourne",
      "enabled": true,
      "businessHoursOnly": false,
      "replyTo": ""
    },
    {
      "id": "OBC0000000002",
      "contactType": "rsp",
      "toName": "",
      "fromName": "",
      "channel": "email",
      "recipient": "alerts@example.com",
      "triggers": [
        "outage"
      ],
      "vtServiceId": "",
      "timezone": "Australia/Melbourne",
      "enabled": true,
      "businessHoursOnly": false,
      "replyTo": ""
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


### Fetch Single Contact

**URI:** `/out-of-band-notifications/contacts/{id}{?apiaryOOBCGetSingle}`


#### `GET` GET /out-of-band-notifications/contacts/{id}{?apiaryOOBCGetSingle}

Required scope: read:out-of-band-notifications:contacts

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | OBC ID for the contact | OBC000000000001 |


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
  "id": "OBC0000000001",
  "contactType": "end_user",
  "toName": "John",
  "fromName": "Pebcak Internet Solutions",
  "channel": "sms",
  "recipient": "61400111222",
  "triggers": [
    "outage"
  ],
  "vtServiceId": "VTAABBCCD",
  "timezone": "Australia/Melbourne",
  "enabled": true,
  "businessHoursOnly": false,
  "replyTo": ""
}
```


*Response — 400:*

Invalid Contact ID

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_short_error": "bad_request",
  "vt_error_desc": "Contact ID must start with 'OBC'"
}
```


*Response — 404:*

Contact ID not found

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_short_error": "not_found",
  "vt_error_desc": "The requested resource was not found"
}
```


### Remove Contact

**URI:** `/out-of-band-notifications/contacts/{id}{?apiaryOOBCDelete}`


#### `DELETE` DELETE /out-of-band-notifications/contacts/{id}{?apiaryOOBCDelete}

Required scope: delete:out-of-band-notifications:contacts

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | OBC ID for the contact | OBC000000000001 |


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
  "vt_error_desc": ""
}
```


*Response — 400:*

Invalid Contact ID format

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_short_error": "bad_request",
  "vt_error_desc": "Contact ID must start with 'OBC'"
}
```


*Response — 404:*

Contact ID not found

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_short_error": "not_found",
  "vt_error_desc": "The requested resource was not found"
}
```
