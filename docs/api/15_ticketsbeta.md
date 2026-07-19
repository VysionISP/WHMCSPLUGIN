# Tickets - Beta

## Tickets - Beta

Please note that this endpoint is in beta and may be subject to changes before release.


### Create NBN missing address ticket

**URI:** `/tickets{?apiaryNBNMissingAddressTicket}`


#### `POST` POST /tickets{?apiaryNBNMissingAddressTicket}

This is used when a location is missing from NBN's database. It will create a ticket in our system, and further
correspondence will be via the email addresses you provide.

Please note that in the sandbox, tickets won't actually be created, but you will receive a simulated ticket ID.

Required scope: create:tickets or all:tickets


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "ticketType": "NBN_MISSING_ADDRESS",
  "emailTo": [
    "alice@example.com",
    "bob@example.com"
  ],
  "customerRef": "Order 12345",
  "dwellingType": "Apartment",
  "unitNumber": "7",
  "roadNumber": "12",
  "roadName": "FLINDERS",
  "roadTypeCode": "ST",
  "localityName": "MELBOURNE",
  "stateTerritoryCode": "VIC",
  "latitude": "-37.8160361",
  "longitude": "144.9613312",
  "additionalInfo": "GNAF ID GAVIC1234567890"
}
```


*Response — 201:*

Successfully created ticket.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "ticketId": "123456"
}
```
