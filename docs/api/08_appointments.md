# Appointments

## Appointments

The easiest way to determine if your order will require an appointment, (and which demandType to request), is to
run a feasibility check

Appointment Callback Values

eventType
notificationType
Note

AppointmentAttributeChange
NBNInvestigationRequired

AppointmentAttributeValueChangeNotification
AppointmentBooked

AppointmentAttributeValueChangeNotification
AppointmentRescheduleRequired

AppointmentAttributeValueChangeNotification
AppointmentRescheduled

AppointmentAttributeValueChangeNotification
CompletionTimeExtended

AppointmentAttributeValueChangeNotification
NBNInvestigationRequired

AppointmentAttributeValueChangeNotification
TechOnSite

AppointmentAttributeValueChangeNotification
UpdateContactDetails

AppointmentCreationNotification
AppointmentCreated

AppointmentRemoveNotification
AppointmentCancelled

AppointmentStateChangeNotification
AppointmentBooked

AppointmentStateChangeNotification
AppointmentCompleted

AppointmentStateChangeNotification
AppointmentIncomplete

AppointmentStateChangeNotification
AppointmentRescheduleRequired

AppointmentStateChangeNotification
CompletionTimeExtended

AppointmentStateChangeNotification
TechOnSite


### Get available time slots

**URI:** `/appointments/timeslots{?locationId,appointmentId,startDateTime,endDateTime,demandType,appointmentSLA,copperPairId,appointmentSlotType,priorityAssist,serviceRestorationSLA,NBNReferenceID,priId,fibreUpgrade}`


#### `GET` GET /appointments/timeslots{?locationId,appointmentId,startDateTime,endDateTime,demandType,appointmentSLA,copperPairId,appointmentSlotType,priorityAssist,serviceRestorationSLA,NBNReferenceID,priId,fibreUpgrade}

It's typically possible to query for available time slots up to 90 days from the date when the request is made.

If time slots are found that match the query terms and some of them fall within a Major Service Disruption (MSD) zone, then the response will contain the code "MSD-IN-AREA". Only time slots outside the MSD zone will be returned in the response.

A maximum of 25 time slots will be returned.

Required scope: create:appointments

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Appointment location | LOC1234567890 |
|  | string | yes | Earliest time the appointment can begin | 2019-08-20T00:00:00 |
|  | string | yes | Latest time the appointment can begin | 2019-08-20T00:00:00 |
|  | string | yes | Type of work that needs to be carried out. `Standard Install` for most orders, (except for FTTN Service Class 12 with Copper Pair Status `Active` where the demandType will be `Non EU Jumper Only`, and (values: Standard Install, Non EU Jumper Only, Additional Install, Service Restoration, Install - Deinstall) | Standard Install |
|  | string | yes | Time of day to search (values: AM, PM, DAY, AHA, CA) | AM |
|  | string | no | [Optional] Previous appointment ID. Required when rescheduling an appointment | APT123456789012 |
|  | string | no | [Optional] Copper pair ID. Required for FTTB/FTTN activation appointments | CPI000000000001 |
|  | string | no | [Optional] Product Instance ID related to the appointment | PRI000000000001 |
|  | boolean | no | [Optional] 'true' if this request is for a on-demand NFAS upgrade (FibreConnect) order, 'false' or omitted otherwise |  |


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
  "demandType": "Standard Install",
  "searchDate": "2019-08-20T15:10:46+1000",
  "priorityAssist": "No",
  "appointmentSLA": "Standard",
  "place": {
    "id": "LOC000000000001"
  },
  "workZone": {
    "primaryAccessTechnology": "Fibre To The Building",
    "serviceabilityClass": 12,
    "csaID": "CSA123456789012",
    "region": "Minor Rural"
  },
  "relatedEntity": [
    {
      "id": "CPI123456789012",
      "@referredType": "CopperPath"
    }
  ],
  "requestedTimeSlot": {
    "validFor": {
      "startDateTime": "2019-08-20T00:00:00",
      "endDateTime": "2019-09-27T00:00:00"
    },
    "appointmentSlotType": "AM"
  },
  "availableTimeSlot": [
    {
      "appointmentSlotType": "AM",
      "validFor": {
        "startDateTime": "2019-08-23T08:00:00+10:00",
        "endDateTime": "2019-08-23T12:00:00+10:00"
      },
      "withinTimeWindow": false,
      "appointmentLeadTime": "Standard"
    }
  ]
}
```


### Reserve an appointment

**URI:** `/appointments`


#### `POST` POST /appointments

Required scope: create:appointments

Please note that this first requires you to get a timeslot
and create a product order.

Appointments are only allowed for orders in the `NBN_APPOINTMENT_REQUIRED` or `APPOINTMENT_REQUIRED` states.

The appointment is will be added to the order if it's successfully reserved

Please note that certain characters (e.g. the apostrophe) can cause problems in NBN's appointment reservation system. Only ascii letters, numbers, spaces, commas, dots (.), hyphens (-), forward slashes (/), at symbols (@), plus signs (+), underscores (_), and parentheses are allowed in strings


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "vtOrderId": "VTORD00AABBCC01",
  "externalId": "ABC-123",
  "demandType": "Standard Install",
  "siteAccess": {
    "specialConditions": "Site requires access card",
    "notes": "Install next to Power Meter, FL",
    "inductionRequirements": "No",
    "operationalHours": "8am to 4pm",
    "buildingName": "School of the Arts",
    "buildingType": "Education",
    "accessInstructions": "Please see reception for swipe card",
    "accreditationRequirements": "Police Check Required",
    "siteConsiderations": "Heritage, Cultural",
    "hazardsInformation": "No"
  },
  "accessSeekerContact": {
    "contactName": "Jane Citizen",
    "phoneNumber": "0123456789",
    "emailAddress": "jane.citizen@example.com"
  },
  "endUserContact": [
    {
      "contactType": "Primary Contact",
      "contactName": "John Doe",
      "phoneNumber": "+61 111 111 111",
      "notes": "Try to call during business hours"
    }
  ],
  "appointmentSlot": {
    "appointmentSlotType": "AM",
    "validFor": {
      "startDateTime": "2019-08-23T08:00:00+10:00",
      "endDateTime": "2019-08-23T12:00:00+10:00"
    }
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
  "id": "APT123456789012",
  "externalId": "ABC123",
  "status": "RESERVED",
  "reasonCode": {
    "code": "RESERVED"
  }
}
```


### Retrieve or modify existing appointment

**URI:** `/appointments/{appointmentId}{?apiaryRetrieveOrModifyAppointment}`


#### `GET` GET - Fetch an Existing Appointment

**URI:** `/appointments/{appointmentId}{?apiaryGetAppointment}`

Get existing appointment

Required scope: read:appointments

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | NBN Appointment ID | APT123456789012 |


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
  "id": "APT123456789012",
  "externalId": "ABC-123",
  "creationDate": "2020-05-07T14:25:54+10:00",
  "lastUpdate": "2020-05-07T14:25:54+10:00",
  "status": "RESERVED",
  "firstAvailableAppointment": {
    "startDateTime": "2020-05-13T08:00:00+10:00",
    "endDateTime": "2020-05-13T12:00:00+10:00"
  },
  "priorityAssist": "No",
  "appointmentSLA": "Standard",
  "appointmentType": "Appointment",
  "endUserType": "Residential",
  "demandType": "Standard Install",
  "siteAccess": {
    "buildingName": "School of the Arts",
    "buildingType": "Education",
    "accessInstructions": "Please see reception for swipe card",
    "inductionRequirements": "No",
    "accreditationRequirements": "Police Check Required",
    "siteConsiderations": "Heritage, Cultural",
    "operationalHours": "8am to 4pm",
    "hazardsInformation": "No",
    "specialConditions": "Vehicles are not to enter school grounds",
    "notes": "Vehicles can use Delivery Bay parking spots"
  },
  "place": {
    "id": "LOC000000000001"
  },
  "workZone": {
    "primaryAccessTechnology": "Fibre To The Building",
    "serviceabilityClass": 12,
    "csaID": "CSA123456789012",
    "region": "Minor Rural"
  },
  "relatedEntity": [
    {
      "id": "CPI123456789012",
      "@referredType": "CopperPath"
    }
  ],
  "accessSeekerContact": {
    "contactName": "Jane Citizen",
    "phoneNumber": "0123456789",
    "emailAddress": "jane.citizen@example.com"
  },
  "endUserContact": [
    {
      "contactType": "Primary Contact",
      "contactName": "John Doe",
      "phoneNumber": "+61 111 111 111",
      "notes": "Available after 10.00 am"
    },
    {
      "contactType": "Secondary Contact",
      "contactName": "Jane Doe",
      "phoneNumber": "+61 222 222 222",
      "notes": "Available between 2pm - 4pm"
    }
  ],
  "appointmentSlot": {
    "validFor": {
      "startDateTime": "2020-05-13T08:00:00+10:00",
      "endDateTime": "2020-05-13T12:00:00+10:00"
    },
    "appointmentSlotType": "AM",
    "withinTimeWindow": false
  },
  "appointmentHistory": [
    {
      "lastUpdateHistory": "2020-05-07T14:25:54+10:00",
      "reasoncodeHistory": {
        "code": "RESERVED"
      },
      "statusHistory": "RESERVED",
      "appointmentSlotHistory": {
        "validFor": {
          "startDateTime": "2020-05-13T08:00:00+10:00",
          "endDateTime": "2020-05-13T12:00:00+10:00"
        }
      },
      "appointmentSlotType": "AM",
      "withinTimeWindow": false
    }
  ]
}
```


#### `PATCH` PATCH - Reschedule an appointment

**URI:** `/appointments/{appointmentId}{?apiaryRescheduleAppointment}`

Please note that you cannot reschedule an appointment and update end user contacts in the same request.

Required scope: update:appointments

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | NBN Appointment ID | APT123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "appointmentSlot": {
    "appointmentSlotType": "AM",
    "validFor": {
      "startDateTime": "2019-08-23T08:00:00+10:00",
      "endDateTime": "2019-08-23T12:00:00+10:00"
    }
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
  "externalId": "ABC-123",
  "id": "APT123456789012",
  "reasonCode": {
    "code": "BK-RESCHED-CUST"
  },
  "status": "BOOKED"
}
```


#### `PATCH` PATCH - Update End User Contacts

**URI:** `/appointments/{appointmentId}{?apiaryUpdateEndUserContactsAppointment}`

Please note that you cannot update end user contacts and reschedule an appointment in the same request.

If you want to remove a contact from an appointment, simply omit it from the endUserContact array.

Required scope: update:appointments

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | NBN Appointment ID | APT123456789012 |


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "endUserContact": [
    {
      "contactType": "Primary Contact",
      "contactName": "John Doe",
      "phoneNumber": "+61 111 111 111",
      "notes": "Try to call during business hours"
    }
  ]
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
  "externalId": "ABC-123",
  "id": "APT123456789012",
  "reasonCode": {
    "code": "CONTACT-UPDATED"
  },
  "status": "BOOKED"
}
```


#### `DELETE` DELETE - Cancel in-flight appointment

**URI:** `/appointments/{appointmentId}{?apiaryCancelAppointment}`

Required scope: delete:appointments

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | NBN Appointment ID | APT123456789012 |


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
  "externalId": "ABC-123",
  "id": "APT123456789012",
  "reasonCode": {
    "code": "RES-CAN-CUST"
  },
  "status": "CANCELLED"
}
```
