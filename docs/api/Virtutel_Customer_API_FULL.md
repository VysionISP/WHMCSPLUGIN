# Virtutel Customer API

*Complete API reference exported from Apiary (virtutelcustomerapi.docs.apiary.io)*  
*Last updated: 2026-07-02T07:38:45.659Z*  
*Format: apiblueprint*

## Base URLs

- **Production:** `https://mars.as24516.net/api/v1/`
- **Mock server:** `https://private-91407b-virtutelcustomerapi.apiary-mock.com/api/v1/`
- **Proxy:** `https://private-91407b-virtutelcustomerapi.apiary-proxy.com/api/v1/`

## Introduction

Important Changes

This API is still being expanded and improved. Any important changes will be listed here.

2026-07-02: Removed the option to disable pamServiceInfo for mobile services (in the GSM Configuration Update operation)

2026-06-26: Removed Mobile Port Out Requests endpoint

2026-06-18: Added `contractStartDate` field to /services responses

2026-04-22: Added PORT_IN_REJECTED status to denote when a mobile port-in has been rejected by the losing carrier (both for ARN and DOB). Please
use the Product Orders PATCH method to correct them, then the order will continue

2026-01-14: Added suspensions endpoint (beta). This lets you temporarily suspend layer 3 NBN services.

2025-10-09: Added the `custData.business` boolean to Modify order responses. This can be used when you need to reserve an appointment for a Modify
order, since appointments for Business services require the `siteAccess.inductionRequirements` and `siteAccess.operationalHours` fields (in
addition to all of the fields required for a Residential appointment).

2025-10-01: Fixed bug that rejected valid speed enumerations for Fibre Upgrade orders (only the speed enums for the original access technology
were being accepted)

2025-09-22: Added the optional `ntdType` field to Product Connect Orders for NFAS (FTTP) that can be used to select between 1 and 4 port NTDs
for FTTP connect orders. When not supplied, an appropriate default will be chosen depending on the existing site configuration. See the `ntdType`
field in the 'Submit a Connect Order' section for more details.

2025-09-14: Added speed enumerations for 500/50, 750/50, and 1000/100 to align with NBN's Accelerate Great changes to FTTP and HFC (a.k.a. Speed Boost).
Other service types remain unchanged. 100/20 is being replaced by 500/50, 250/25 is being replaced by 750/50, and 1000/50 is being replaced by 1000/100.
To maintain backwards compatibility, you will still see the old speed enumerations in the `virtutelSpeedsAvailable` array (in addition to the
new tiers). You will still be able to place orders using the superseded enumerations, however they will automatically be changed to their new
equivalents when you GET /product-orders or /services. While you transition to the new enumerations, you can add `legacySpeedEnums=true`
to your /product-orders and /services GET requests to revert back to the old enumerations. Any service that has been reverted will also include
the `speedBoostFlag` key with a value of true.

Sandbox vs. Production

By default until you have completed some of the basic tests with the test data provided,
you will not be able to gain access to the production system.

Please note all production requests are made on port 443.

Please note all sandbox requests are made on port 8443.

Certification

You must demonstrate successful calls to all documented methods for each endpoint you wish to use in Production.
The only exceptions to this are the PATCH method for Product Orders, and the PATCH and DELETE methods for Appointments,
(orders progress too quickly in the sandbox for these to be used).

If you wish to use any endpoint that generates callbacks, (Product Orders, Appointments,
Service Health Checks, or Service Tests), you must successfully register a callback URL and receive at least
one callback to be certified.

Also note that Product Orders and Appointments certifications are only available together. This means that you must
complete at least one order that requires an appointment, (e.g. at an NFAS (FTTP) Service Class 2 location), to gain
this certification.

Please contact us when you're ready to be assessed. We'll review your transactions in our Sandbox logs and
either provide feedback or certify you for specific scopes in production.

Test Data

Test data will be provided to you after you sign the API Licence Agreement.

Our test data provided is generic and can potentially be changed by any party in the Sandbox API
hence we recommend doing full SQ on each LOC prior to using to determine current state.

While you're welcome to run Service Qualifications on any location you find in the sandbox environment,
we ask that you don't place any orders for locations that haven't been specifically issued
to you in the test data document.

Please note that live data, (e.g. NBN Location IDs), will not work in the Sandbox API,
and test data will not work in the Production API.

Authentication

The API requires you to supply valid access token with every request.
Please see the Access Tokens section for details on how to obtain and use these tokens.

Firewall

All Virtutel servers are protected by strict firewall polices that block traffic by default.
Please ensure the correct forms have been filled out to ensure your traffic is allowed through.

Please note that the Sandbox (test/staging) and Production (live) servers each have their own independent firewalls.

You will initially be allowed through the Sandbox firewall.
Only after completing certification will you be allowed through the production firewall.

Standard response fields

Responses will always be a JSON object, with `vt_success` (boolean), `vt_short_error` (string),
and `vt_error_desc` (string) present at the root level.

vt_success will normally be true, but will be set to false if an error has been detected

vt_short_error is a short-form, generalised error code, e.g. "auth_error"

vt_error_desc is a longer, human-readable error description, usually with more detail,
e.g. "You must supply a valid access token with your request"

When `vt_success` is true, `vt_short_error` and `vt_error_desc` will be empty.

Example requests and responses

Please note that the example requests and responses provided are automatically generated and may not correspond with each other.
Additionally, the example values within a given request or response may not be valid together.

NBN Service Classes

Service Class
Description

0
Planned to be serviced by Fibre in the future

1
Serviceable by Fibre, no drop or NTD in place

2
Serviceable by Fibre, drop in place, no NTD in place

3
Serviceable by Fibre, drop and NTD in place

4
Planned to be serviced by Wireless

5
Serviceable by Wireless, NTD not installed

6
Serviceable by Wireless, NTD installed

7
Planned to be serviced by Satellite

8
Serviceable by Satellite, Satellite - NTD not installed

9
Serviceable by Satellite, Satellite - NTD installed

10
Planned to be serviceable by Copper

11
Serviceable by copper, active node present

12
Serviceable by copper, jumpering is required

13
Serviceable by copper, infrastructure in place

20
Planned to be serviced by HFC, outside plant does not exist

21
Serviceable by HFC, the location has a street TAP but requires a lead-in, PCD, internal tie cable, and wall-plate/socket

22
Serviceable by HFC, the location has a street TAP, lead-in & PCD in place, but no internal tie-cables with wall plates/sockets

23
Serviceable by HFC, the location has a wall-plate/socket but no HFC NTD

24
Serviceable by HFC, Location has a wall-plate/socket & HFC NTD and is ''Ready to Connect

30
Planned to be serviceable by FTTC

31
Serviceable by FTTC, no line available (NCD required)

32
Serviceable by FTTC, cut-in required (NCD required)

33
Serviceable by FTTC, cut-in complete (NCD required)

34
Serviceable by FTTC, infrastructure in place (Check NCD status)

Fibre Upgrade Orders

As part of the Fibre Connect program, NBN is offering FTTP (NFAS) upgrades to selected FTTN and FTTC locations.

The process is very similar to a standard NFAS order:

Run a normal Service Qualification and ensure the `siteRestriction`.`supportingTechnology`.`alternativeTechnology`
key is present in the response, and has the value 'Fibre'.

Run a Fibre Upgrade SQ, (by specifying the URL parameter `productType` with value 'NFAS'), and ensure the site
has a service class of 1, 2, or 3.

Assemble a normal NFAS (FTTP) order, then add the `service`.`nbn`.`nfas`.`fibreUpgrade` key with the boolean
value 'true'. Note that the service must have a 100Mbps minimum downstream speed.

Optional: run a Product Order Qualification

Submit the order

Wait for the FibreUpgradeConfirmationRequired callback

Penalties can apply to Fibre Upgrade services, (details below). To accept responsibility for these potential
charges and proceed, PATCH the order with the boolean `fibreUpgradeLiabilityConfirmed` set to `true`.

Wait for the AppointmentRequired callback

Get appointment timeslots as you normally would, but with the `fibreUpgrade` parameter set to 'true', and
`demandType` set to 'Standard Install'. Do not include a copper pair ID.

Reserve an appointment using one of the timeslots returned.

From here, the order should proceed as if it was a normal fibre order.

Note that any existing FTTN/FTTC services will continue to operate at the location for the next 12 months, and you will
continue to be billed for them if they're not cancelled. After 12 months, any remaining copper services will be
automatically disconnected.

Also note that penalty fees apply during the first 12 months of the fibre service if the speed is downgraded
below 100Mbps downstream, or if the service is cancelled.

Churn Validation

Also referred to as 'Service Transfer Validation'. As of April 18 2025, the AVC ID of the service being churned
will be required for all churn orders (except reversals). Until then, you can optionally provide it with your
churn orders.

This change is meant to reduce the number of erroneous churns, and thus reversals. This change is also beneficial
to you as the RSP gaining the service, because it makes the process of identifying the correct copper pair or UNI-D
port much easier.

You'll need to get the AVC ID from the end user, (who in turn will get it from the RSP losing the service).

Please note that you'll also need to be set up to provide AVC IDs to customers who are churning away from you. You can
retrieve these from our /services endpoint.

Either the entire AVC ID can be used, (e.g. AVC123456789012), or just the last five digits, (e.g. 89012).

Once the end user has given you the full/partial AVC ID and permission to place an order on their behalf, you can
verify it by running an Enhanced Service Qualification with the Customer Authority Date, (in the `customerAuthorityDate`
parameter) and AVC ID (in the `serviceID` parameter):

e.g. `GET /api/v1/service-qualifications/LOC000000000001?customerAuthorityDate=2025-01-01&serviceID=89012`

Each UNI-D port or copper pair object in the response will contain the boolean `serviceIDMatch`, indicating whether
the AVC ID you supplied matches the service on that UNI-D port or copper pair.

Once you've found the matching UNI-D port or copper pair, you can place your churn order as normal. The only difference
is that you supply the full/partial AVC ID in the `service`.`nbn`.`churn`.`serviceIDToTransfer` field in the order.

To simplify the testing process, we're allowing you to churn your own services in our Sandbox environment.
This means you can get the correct AVC ID from the /services endpoint to use in your SQ and order.

Fixed Wireless High Speed Tiers

High speed tiers are now orderable at some locations.

We've added the following speed enumerations:

TC4FWHF - Layer 2, TC4, Fixed Wireless Home Fast (PIR of 200-250/8-20 Mbps)

TC4FWSF - Layer 2, TC4, Fixed Wireless Superfast (PIR of 400/10-40 Mbps)

L3TC4FWHF - Layer 3, TC4, Fixed Wireless Home Fast (PIR of 200-250/8-20 Mbps)

L3TC4FWSF - Layer 3, TC4, Fixed Wireless Superfast (PIR of 400/10-40 Mbps)

Ordering process (applies to both Connect and Modify orders)

First, run a Service Qualification and ensure the site supports the site supports the high speed tier
you're interested in.
You can either check the siteRestriction.supportingProductFeatures[0].speedTierAvailability array or
our virtutelSpeedsAvailable array.

If you're ordering a service on an existing NTD, you also need to check the
siteRestriction.supportingResource[<ntd_index>].speedTiersSupported array to make sure the speed you've selected
is supported by that specific NTD.

In some cases, the NTD will need to be upgraded to support the new speed. If that's the case, there will be a note
about it in siteRestriction.notes. Codes NTD0001 and NTD0002 mean that an upgrade will be required for all high speed
tiers, and NTD0005 means that an upgrade will only be required for Superfast.

Assuming your chosen speed tier is supported by the site/NTD, you submit the order to us as you normally would.

If the NTD needs to be upgraded, we'll prompt you for an appointment. The demandType for these upgrades is
'Additional Install'.

Notes about Apiary

Required vs optional URL Parameters

Apiary displays all possible URL query parameters in their examples, but not every parameter is required in every request.

Parameters are only required in a request if the circle immediately to the right of the parameter name is solid.

If the circle is hollow, the parameter is optional and doesn't need to be included with every request.

If you're unsure what a given circle means, you can hover your cursor over it and a tooltip will appear.

Required vs optional Request Attributes

Request attributes are only required in a request if they're explicitly marked 'required',
(directly under the attribute name), otherwise they're optional and can be safely omitted.

Note that nested keys marked 'required' are only required if their parent is included in the request.

In the following example, `a_required_key` only needs to be included in the request if `an_optional_key`
is also included.

``{
    "an_optional_key": {
        "a_required_key": "some_value"
    }
}
``

Apiary's Console (Try) Functionality

Please note that the 'mock server' is simply displaying static responses based on the documentation.

Unfortunately it's not currently possible to use the console with our Sandbox API.

While it's technically possible for you to use the console with our Production API, it's discouraged.

## Table of Contents

1. [Access Tokens](#access-tokens) — 1 resource(s)
2. [Callbacks](#callbacks) — 3 resource(s)
3. [Address Searches](#address-searches) — 1 resource(s)
4. [Service Qualifications](#service-qualifications) — 6 resource(s)
5. [Mobile](#mobile) — 19 resource(s)
6. [Product Order Qualifications](#product-order-qualifications) — 1 resource(s)
7. [Product Orders](#product-orders) — 7 resource(s)
8. [Appointments](#appointments) — 3 resource(s)
9. [Services](#services) — 3 resource(s)
10. [CVC Entitlements](#cvc-entitlements) — 1 resource(s)
11. [Service Health Checks](#service-health-checks) — 3 resource(s)
12. [Service Tests](#service-tests) — 2 resource(s)
13. [Outages](#outages) — 3 resource(s)
14. [Demographics](#demographics) — 1 resource(s)
15. [Tickets - Beta](#tickets-beta) — 1 resource(s)
16. [Out of Band Notifications](#out-of-band-notifications) — 5 resource(s)
17. [Invoices - Beta](#invoices-beta) — 2 resource(s)
18. [AVC Utilisation - Beta](#avc-utilisation-beta) — 2 resource(s)
19. [Suspensions - Beta](#suspensions-beta) — 3 resource(s)
20. [Overview - Beta](#overview-beta) — 1 resource(s)
21. [One-Off Charges](#one-off-charges) — 1 resource(s)


## Access Tokens


### Generate a new access token

**URI:** `/oauth/tokens`

In order to make requests to the API, you will need to supply a valid access token.

This endpoint lets you generate an access token using the `client_id` and `client_secret` you will be issued
when you have completed the necessary paperwork.

The Sandbox and Production APIs do not share client IDs, client secrets, or access tokens.

Please note that access tokens have a lifetime, (30 days at the time of writing).
Generating a new token with every request is fine when you're testing, but in production you
should be re-using tokens for the majority of their lifetime.

Once you have obtained an access token, you can use it by supplying it in the header of your requests
under the `Authorization` key. Note that you must prepend 'Bearer' to the token, (with a space in between).

e.g. if your access token is `eyJhbfh0dsadfs9h0fdshfsdhmudsfhuifd98yhdfs`,
your request headers would look something like this:

``Accept: application/json
Content-Type: application/json
Authorization: Bearer eyJhbfh0dsadfs9h0fdshfsdhmudsfhuifd98yhdfs
``


#### `POST` POST /oauth/tokens


*Request:*

Headers:

- `Content-Type: application/json`

```json
{
  "client_id": "replace_this_with_your_issued_client_id",
  "client_secret": "replace_this_with_your_issued_client_secret",
  "audience": "mars.as24516.net",
  "grant_type": "client_credentials"
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
  "access_token": "abcdefghijklmnopqrstuvwxyz",
  "expires_in": 604800,
  "scope": "read:service-qualifications all:appointments",
  "token_type": "Bearer"
}
```


## Callbacks

Callbacks are asynchronous notifications. You must register at least one callback URL to receive them.

Callbacks are POSTed as JSON objects, (Content-Type: application/json), to to URL(s) you supply.

Your server should respond with an HTTP2xx series status code.

All callbacks will be sent from mars.as24516.net.

If your server is unreachable or responds with a non-HTTP2xx status code, we will try again later.
After several failed attempts, the callback will be marked as failed and will not be re-sent.

At the root level, callbacks will contain the following keys:

`eventUuid` (string) - (e.g. c1043a09-44db-4a66-b00d-5caad94d657e)

`eventTime` (string) - ISO8601 format with timezone (e.g. 2019-11-07T14:01:07+11:00)

`eventType` (string) - Basic event type (e.g. ProductOrderStateChangeNotification)

`event` (object) - Details about the event that triggered the callback

The event object will always contain the following keys, (but may contain others depending on the event):

`id` (string) - ID that triggered the event (e.g. VTORD00000000001)

`notificationType` (string) - Specific event type (e.g. OrderCompleted)

`reason` (string, can be empty) - Detailed reason for the event (e.g. The service has been transferred to a new Access Seeker at request of end user)

Example callback server implementation:

``# This is a simple callback server example in Python using the Flask framework
# Find out more about Flask here: https://pypi.org/project/Flask/

from pprint import pprint

from flask import Flask
from flask import jsonify
from flask import request

app = Flask(__name__)

@app.route("/callbacks", methods=["POST"])
def callbacks():
    print("Got a new callback!")
    pprint(request.get_json())
    return jsonify({"message": "ok"})

if __name__ == "__main__":
    # Run Flask on port 5000 with SSL enabled.
    # Note that you'll need to supply a valid SSL certificate and key
    ssl_context = ("/path/to/cert.pem", "/path/to/key.pem")
    app.run(host="0.0.0.0", port=5000, ssl_context=ssl_context)
``


### Register New Callback URL or List Existing URLs

**URI:** `/callbacks/urls`


#### `POST` POST /callbacks/urls

Register a new Callback URL

You can register up to five URLs and they will all be sent copies of each callback.

URLs must begin with https://

Your hostname must be registered with us before you can register a callback URL for it.

Your server must have a valid SSL certificate to receive callbacks. Self-signed certificates are not allowed.

Your server must send the appropriate intermediate certificate(s) along with your server certificate.
You can use the DigiCert Certificate Checker to validate your configuration: https://www.digicert.com/help/

Note that you can include query parameters in your URL, e.g.
'https://your-domain.com/path/for/callbacks?token=5e940482728ceb26452608e4b35be1876d09ceb8'

Required scope: create:callbacks


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "url": "https://your-domain.com/path/for/callbacks"
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
  "id": "CRI000000000001"
}
```


#### `GET` GET /callbacks/urls

Get a list of registered Callbacks

Required scope: read:callbacks


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
  "registeredCallbacks": [
    {
      "id": "CRI000000000001",
      "url": "https://your-domain.com/path/for/callbacks"
    }
  ]
}
```


### Remove a Callback URL

**URI:** `/callbacks/urls/{callbackRegistrationId}`


#### `DELETE` DELETE /callbacks/urls/{callbackRegistrationId}

Required scope: delete:callbacks

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | CRI000000000001 |


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


### Send a test Callback

**URI:** `/callbacks/tests/{callbackRegistrationId}`


#### `POST` POST /callbacks/tests/{callbackRegistrationId}

Returns HTTP200 if the callback was successfully sent, or HTTP4xx/HTTP5xx if there was a problem.

If your server responds with a non-HTTP2xx status code, it will be included in `vt_error_desc`
to help you debug the issue.

Required scope: create:callbacks

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | CRI0000000001 |


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


## Address Searches


### Search for NBN Location IDs

**URI:** `/locations{?apiaryAddressSearch}`

Address matching is not case sensitive.

If multiple results are found, they will be ordered from closest to furthest match, with the closest being in position 0 of the array.

If no results are found, you will still receive an HTTP200 code but the responseData array will be empty.
The only exception is the reverse location ID lookup, where you'll receive an HTTP404 error instead.

Please note that in the sandbox, you can only search for locations provided in the test data. Locations in the real world will not resolve to sandbox LOC IDs.

Required scope: read:locations


#### `POST` POST - Unstructured address search

**URI:** `/locations{?apiaryUnstructuredAddressSearch}`

Please note that unstructured searches are unreliable in NBN's staging environment, but work as expected in production.

Unstructured address search patterns:

``<unitTypeCode> <unitNumber> <roadNumber1>-<roadNumber2> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>

<unitTypeCode> <unitNumber> <roadNumber1> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>

<unitTypeCode> <unitNumber> <roadNumber1> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode>

<unitTypeCode> <unitNumber> <roadNumber1>-<roadNumber2> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode>

<roadNumber1>-<roadNumber2> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>

<roadNumber1> <roadName> <roadTypeCode> <localityName> <stateTerritoryCode> <postcode>
``


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "unstructured": {
    "address": "546 FLINDERS ST MELBOURNE 3000",
    "fuzzy": false
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
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "2",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 2 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 2 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "3",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 3 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 3 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


#### `POST` POST - Structured address search

**URI:** `/locations{?apiaryStructuredAddressSearch}`

Most of the time you'll only need to provide roadNumber1, roadName, roadTypeCode, localityName, postcode, and stateTerritoryCode, but you can supply any fields that apply to the address.

Note that while `stateTerritoryCode` and `postcode` are marked 'required', you only need to provide one of them, (though it's usually a good idea to provide both).


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "structured": {
    "roadNumber1": "546",
    "roadName": "FLINDERS",
    "roadTypeCode": "ST",
    "localityName": "MELBOURNE",
    "stateTerritoryCode": "VIC",
    "postcode": "3000"
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
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "2",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 2 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 2 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "3",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 3 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 3 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


#### `POST` POST - Search by G-NAF ID

**URI:** `/locations{?apiaryGNAFAddressSearch}`

See https://geoscape.com.au/ for more details on G-NAF IDs

Please note that the `fullAddress` value will always be a blank string because NBN does not return address details
for G-NAF searches. If you need any address information, please perform a 'Reverse Location ID Lookup' (details below).


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "GNAF": "GANSW098742316"
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
  "responseData": [
    {
      "id": "LOC123456789001",
      "fullAddress": ""
    }
  ]
}
```


#### `POST` POST - Search by coordinates

**URI:** `/locations{?apiaryCoOrdAddressSearch}`

Search by latitude and longitude


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "coordinates": {
    "latitude": "-37.82052",
    "longitude": "144.95614684"
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
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "2",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 2 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 2 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    },
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "3",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 3 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 3 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


#### `POST` POST - Reverse Location ID Lookup

**URI:** `/locations{?apiaryReverseAddressSearch}`

Useful if you need to convert an NBN Location ID to an address.

Note that this will either return a single result, or HTTP404 if the location can't be found.


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "locationId": "LOC123456789001"
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
  "responseData": [
    {
      "id": "LOC123456789001",
      "unitTypeCode": "UNIT",
      "unitNumber": "1",
      "roadNumber1": "546",
      "roadNumber2": "548",
      "roadName": "FLINDERS",
      "roadTypeCode": "ST",
      "localityName": "MELBOURNE",
      "stateTerritoryCode": "VIC",
      "postcode": "3000",
      "fullAddress": "UNIT 1 546-548 FLINDERS ST MELBOURNE VIC 3000",
      "formattedAddress": "Unit 1 546-548 Flinders St, Melbourne, VIC",
      "latitude": "-37.8204062",
      "longitude": "144.9560276"
    }
  ]
}
```


*Response — 404:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": false,
  "vt_error_desc": "Your request generated an NBN error status code",
  "vt_short_error": "nbn_error",
  "code": "012000",
  "type": "InvalidAddressException",
  "reason": "No records were found to match the NBN Location ID LOC123456789001 specified in the request."
}
```


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


## Services

Services Callback Values

eventType
notificationType
Note

ProductAttributeValueChangeNotification
ProductInstanceUpdated
Sent when NBN updates something to do with a service (e.g. changing UNI-D port on an NTD because the old port was faulty

ProductRemoveNotification
ProductInstanceDisconnected
Service has been disconnected, (usually because it’s been churned to another provider)


### Get Service from VT Service ID

**URI:** `/services{?vtServiceId,includeMdfPatch,legacySpeedEnums}`


#### `GET` GET /services{?vtServiceId,includeMdfPatch,legacySpeedEnums}

Required scope: read:services

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID. Must start with 'VT' | VT0000123 |
|  | boolean | no | [Optional] Include MDF patch information. Only allowed for NBN FTTB services. |  |
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
  "services": [
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    }
  ]
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


### Get Service from Supplier Service ID

**URI:** `/services{?supplierServiceId,includeMdfPatch,legacySpeedEnums}`


#### `GET` GET /services{?supplierServiceId,includeMdfPatch,legacySpeedEnums}

Required scope: read:services

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes |  | PRI000000001234 |
|  | boolean | no | [Optional] Include MDF patch information. Only allowed for NBN FTTB services. |  |
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
  "services": [
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    }
  ]
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


### Get All Services or Search for Services Containing a Specific String

**URI:** `/services{?searchString,page,limit,status,legacySpeedEnums}`


#### `GET` GET /services{?searchString,page,limit,status,legacySpeedEnums}

Note that the results from this endpoint are paginated

Required scope: read:services

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | Omit this or leave it blank to return all services. Matches any part of VT Service ID, Supplier Service ID, Service Type, Supplier, Description, Additional Description, AVC ID, or LOC ID | Jane Citizen |
|  | number | no | Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty services array | 5 |
|  | number | no | Maximum number of services on a page. Default 100. Minimum 10. Maximum 1000. | 100 |
|  | string | no | Filter by service status (Active or Cancelled) | Active |
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
  "services": [
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    },
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    },
    {
      "vtServiceId": "VT0000123",
      "supplierServiceId": "PRI000000001234",
      "avcId": "AVC000000001234",
      "locationId": "LOC000000000001",
      "cpiNtdId": "NTD000000000001",
      "uniDPortId": "2",
      "csaId": "CSA000000000001",
      "serviceType": "nbn",
      "supplier": "nbn Co Limited",
      "description": "NBN TC-4 Access Layer 2 Agg Home Fast",
      "additionalDescription": "12345 - Jane Citizen",
      "chargeExPerMonth": "70.0000",
      "currency": "AUD",
      "contractStartDate": "2021-01-01",
      "lastInvoiced": "2021-05-01",
      "invoicedToDate": "2021-05-31",
      "status": "Active",
      "speed": "TC4100D40U",
      "accessTechnology": {
        "baseType": "NFAS",
        "subType": "FTTP"
      },
      "serviceRestorationSla": "Standard",
      "dslStabilityProfile": "Standard",
      "vlanId": 1234,
      "cTag": 1234,
      "sTag": 2345,
      "poiId": "3NBA",
      "invoiceCountry": "AUS"
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/services?page=5&limit=100",
    "first": "/api/v1/services?page=1&limit=100",
    "last": "/api/v1/services?page=14&limit=100",
    "prev": "/api/v1/services?page=4&limit=100",
    "next": "/api/v1/services?page=6&limit=100"
  }
}
```


## CVC Entitlements


### Request CVC Entitlements

**URI:** `/cvc-entitlements`


#### `GET` GET /cvc-entitlements

Please note that these figures are from a specific snapshot in time and should only be used as a guide.

Required scope: read:cvc-entitlements


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`


*Response — 200:*

Please note that while only three POIs are shown in this example,
all 121 could potentially be returned to you if you have at least one TC4 service at each POI.

In the sandbox, all services are under the `0SANDBOX` POI.

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "entitlements": {
    "2ALB": {
      "tc4ServiceCount": 12,
      "entitlementTotalMbps": "40.25"
    },
    "2BLK": {
      "tc4ServiceCount": 20,
      "entitlementTotalMbps": "120.00"
    },
    "2BLV": {
      "tc4ServiceCount": 1,
      "entitlementTotalMbps": "1.00"
    }
  }
}
```


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


## Demographics


### Retrieve Demographics by POI ID, Post Code, CSA ID, or State

**URI:** `/demographics/{areaId}`

Note that AVC usage and CVC inclusions are only granular to the POI level, so those figures
will not be included when you search for a post code.

Note that the list of post codes will only be returned when the area requested is a POI or CSA ID.


#### `GET` GET /demographics/{areaId}

Required scope: read:demographics

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | NBN POI ID (e.g. `2NWR`), Australian Postal Code (e.g. `2000`), NBN CSA ID (e.g. `CSA300000010923`), or Australian State (`ACT`, `NSW`, `NT`, `QLD`, `SA`, `TAS`, `VIC`, `WA`) | 2NWR |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "demographics": {
    "2NWR": {
      "averageAvcUsageMbps": "1.1234",
      "peakHourAvcUsageMbps": "2.3456",
      "averageCvcInclusionMbps": "2.0480",
      "censusData": {
        "2016": {
          "averageHouseholdSize": "2.3778",
          "averagePersonsPerBedroom": "0.7500",
          "medianAge": 48,
          "medianMortgageRepaymentYearly": 18204,
          "medianPersonalIncomeYearly": 28782,
          "medianRentYearly": 14560,
          "medianTotalHouseholdIncomeYearly": 54756
        },
        "2021": {
          "averageHouseholdSize": "2.300",
          "averagePersonsPerBedroom": "0.7000",
          "medianAge": 49,
          "medianMortgageRepaymentYearly": 20206,
          "medianPersonalIncomeYearly": 30784,
          "medianRentYearly": 16562,
          "medianTotalHouseholdIncomeYearly": 58759
        }
      },
      "postCodes": [
        "2487",
        "2527",
        "2529",
        "2530",
        "2533",
        "2534",
        "2535",
        "2536",
        "2537",
        "2538",
        "2539",
        "2540",
        "2541",
        "2546",
        "2548",
        "2549",
        "2550",
        "2551"
      ]
    }
  }
}
```


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


## Invoices - Beta

Please note that this endpoint is currently in beta. There may be breaking changes before it's released.


### List Invoice Summaries

**URI:** `/invoices{?invoiceCountry,minIssueDateUtc,page,limit,apiaryInvoicesSummaries}`

Note that these responses are paginated.

Please note that in the sandbox environment, invoices will be dynamically generated so they may change over time.


#### `GET` GET /invoices{?invoiceCountry,minIssueDateUtc,page,limit,apiaryInvoicesSummaries}

Required scope: read:invoices

Rate limits: steady - 120 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Which country the invoices are generated in (you can find this for each service using the /services endpoint) (values: AUS, NZ) | AUS |
|  | string | no | [Optional] Filter for invoices issued on or after a specific point in time (UTC in YYYY-MM-DD[T]HH:MM:SS format) | 2023-01-02T03:45:60 |
|  | number | no | [Optional] Fetch a specific page of results. Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty invoices array | 5 |
|  | number | no | [Optional] Maximum number of services on a page. Default 100. Minimum 10. Maximum 1000. | 100 |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "invoices": [
    {
      "uuid": "e2b8fe03-827a-4057-baaa-110bda293d41",
      "invoiceNumber": "INV-1234",
      "invoiceCountry": "AUS",
      "issueDateUtc": "2023-04-05T06:07:08",
      "dueDateUtc": "2023-09-10T11:12:13",
      "lastUpdatedUtc": "2024-01-02T03:45:06",
      "amountDue": "370.3500",
      "amountPaid": "0.0000",
      "amountCredited": "0.0000",
      "subtotal": "336.6600",
      "totalTax": "33.6600",
      "invoiceTotal": "370.3500",
      "currencyCode": "AUD"
    },
    {
      "uuid": "e2b8fe03-827a-4057-baaa-110bda293d41",
      "invoiceNumber": "INV-1234",
      "invoiceCountry": "AUS",
      "issueDateUtc": "2023-04-05T06:07:08",
      "dueDateUtc": "2023-09-10T11:12:13",
      "lastUpdatedUtc": "2024-01-02T03:45:06",
      "amountDue": "370.3500",
      "amountPaid": "0.0000",
      "amountCredited": "0.0000",
      "subtotal": "336.6600",
      "totalTax": "33.6600",
      "invoiceTotal": "370.3500",
      "currencyCode": "AUD"
    },
    {
      "uuid": "e2b8fe03-827a-4057-baaa-110bda293d41",
      "invoiceNumber": "INV-1234",
      "invoiceCountry": "AUS",
      "issueDateUtc": "2023-04-05T06:07:08",
      "dueDateUtc": "2023-09-10T11:12:13",
      "lastUpdatedUtc": "2024-01-02T03:45:06",
      "amountDue": "370.3500",
      "amountPaid": "0.0000",
      "amountCredited": "0.0000",
      "subtotal": "336.6600",
      "totalTax": "33.6600",
      "invoiceTotal": "370.3500",
      "currencyCode": "AUD"
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/invoices?page=5&limit=100&invoiceCountry=AUS&minIssueDateUtd=2023-01-02T03:45:60",
    "first": "/api/v1/invoices?page=1&limit=100&invoiceCountry=AUS&minIssueDateUtd=2023-01-02T03:45:60",
    "last": "/api/v1/invoices?page=14&limit=100&invoiceCountry=AUS&minIssueDateUtd=2023-01-02T03:45:60",
    "prev": "/api/v1/invoices?page=4&limit=100&invoiceCountry=AUS&minIssueDateUtd=2023-01-02T03:45:60",
    "next": "/api/v1/invoices?page=6&limit=100&invoiceCountry=AUS&minIssueDateUtd=2023-01-02T03:45:60"
  }
}
```


### Fetch Detailed Invoice

**URI:** `/invoices/{uuid}{?invoiceCountry,apiaryInvoicesDetail}`

This is very similar to the summary response, however it contains all of the line items from the invoice.

Please note that in the sandbox environment, invoices will be dynamically generated so they may change over time.


#### `GET` GET /invoices/{uuid}{?invoiceCountry,apiaryInvoicesDetail}

Required scope: read:invoices

Rate limits: steady - 120 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Invoice UUID. You can get this from the invoice summary response. | e2b8fe03-827a-4057-baaa-110bda293d41 |
|  | string | yes | Where the invoice was generated. You can get this from the invoice summary response. (values: AUS, NZ) | AUS |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "uuid": "e2b8fe03-827a-4057-baaa-110bda293d41",
  "invoiceNumber": "INV-1234",
  "invoiceCountry": "AUS",
  "issueDateUtc": "2023-04-05T06:07:08",
  "dueDateUtc": "2023-09-10T11:12:13",
  "lastUpdatedUtc": "2024-01-02T03:45:06",
  "amountDue": "370.3500",
  "amountPaid": "0.0000",
  "amountCredited": "0.0000",
  "subtotal": "336.6600",
  "totalTax": "33.6600",
  "invoiceTotal": "370.3500",
  "currencyCode": "AUD",
  "lineTotalsType": "Exclusive",
  "lines": [
    {
      "description": "nbn Layer 2 Aggregated 50/20 - Alice Smith (PRI000000000001) Period: 01/04/23 - 30/04/23",
      "quantity": "1.0000",
      "unitAmount": "112.2300",
      "taxAmount": "11.2200",
      "lineTotal": "112.2300"
    },
    {
      "description": "nbn Layer 2 Aggregated 50/20 - Alice Smith (PRI000000000001) Period: 01/04/23 - 30/04/23",
      "quantity": "1.0000",
      "unitAmount": "112.2300",
      "taxAmount": "11.2200",
      "lineTotal": "112.2300"
    },
    {
      "description": "nbn Layer 2 Aggregated 50/20 - Alice Smith (PRI000000000001) Period: 01/04/23 - 30/04/23",
      "quantity": "1.0000",
      "unitAmount": "112.2300",
      "taxAmount": "11.2200",
      "lineTotal": "112.2300"
    }
  ]
}
```


## AVC Utilisation - Beta

This is currently in beta.
There may be breaking changes before it's released.

The sandbox data is currently randomly generated. The structures and formats match what you'd see in
production, but the data itself is nonsensical and will change with each request.


### List Available Reports

**URI:** `/avc-utilisation/list-reports{?fromCreatedUtc,toCreatedUtc,page,limit,apiaryAvcUtilisationListReports}`

Retrieves a list of available reports.

NBN will occasionally issue an updated report for a given day. When this happens, the old report will
still be available, however there will be a new entry for the same reportDate with a more recent `createdUtc` timestamp.

Results are sorted by createdDate in ascending order. This means that if NBN issues an updated report for a previous
day, the `reportDate`s will not be in chronological order.

These responses are paginated.

In the sandbox environment, this data will be dynamically generated and may change over time.


#### `GET` GET /avc-utilisation/list-reports{?fromCreatedUtc,toCreatedUtc,page,limit,apiaryAvcUtilisationListReports}

Required scope: read:avc-utilisation

Rate limits: steady - 20 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | no | [Optional] Limit results to reports created at or after this time. YYYY-MM-DDTHH:MM:SS format. |  |
|  | string | no | [Optional] Limit results to reports created at or before this time. YYYY-MM-DDTHH:MM:SS format. |  |
|  | number | no | [Optional] Fetch a specific page of results. Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty invoices array | 5 |
|  | number | no | [Optional] Maximum number of entries on a page. Default 100. Minimum 10. Maximum 1000. | 100 |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "records": [
    {
      "uuid": "c7a19ada-0a6e-419d-9fc4-fd563c18674a",
      "reportDate": "2023-11-01",
      "createdUtc": "2023-11-02T04:02:03",
      "name": "AVC Utilisation Report - 01 November 2023"
    },
    {
      "uuid": "6d9b20ca-b901-48ad-b245-6c73e7fdf400",
      "reportDate": "2023-11-01",
      "createdUtc": "2023-11-02T08:02:03",
      "name": "UPDATED AVC Utilisation Report - 01 November 2023"
    },
    {
      "uuid": "47c28cb8-127f-4994-bdfc-cde36da5faf6",
      "reportDate": "2023-11-02",
      "createdUtc": "2023-11-04T01:02:03",
      "name": "AVC Utilisation Report - 02 November 2023"
    },
    {
      "uuid": "86417817-8297-40fd-a749-4943283a67ec",
      "reportDate": "2023-11-03",
      "createdUtc": "2023-11-05T01:02:03",
      "name": "AVC Utilisation Report - 03 November 2023"
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/avc-utilisation/list-reports?page=5&limit=100",
    "first": "/api/v1/avc-utilisation/list-reports?page=1&limit=100",
    "last": "/api/v1/avc-utilisation/list-reports?page=14&limit=100",
    "prev": "/api/v1/avc-utilisation/list-reports?page=4&limit=100",
    "next": "/api/v1/avc-utilisation/list-reports?page=6&limit=100"
  }
}
```


### Fetch Records by Report ID as JSON

**URI:** `/avc-utilisation/by-uuid/{uuid}{?page,limit,apiaryAvcUtilisationRecordsByReportIDJSON}`

Returns records for the given report ID.

In the sandbox environment, this data will be dynamically generated and may change over time.


#### `GET` GET /avc-utilisation/by-uuid/{uuid}{?page,limit,apiaryAvcUtilisationRecordsByReportIDJSON}

Required scope: read:avc-utilisation

Rate limits: steady - 10 requests/minute, burst - 2 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Report ID | c7a19ada-0a6e-419d-9fc4-fd563c18674a |
|  | number | no | [Optional] Fetch a specific page of results. Default 1. Minimum 1. Note that if you request an invalid page, the API will return HTTP200 with an empty invoices array | 5 |
|  | number | no | [Optional] Maximum number of entries on a page. Default 10000. Minimum 100. Maximum 10000. | 100 |


*Request:*

Headers:

- `Content-Type: application/json`
- `authorization: Bearer put_your_access_token_here`


*Response — 200:*

Headers:

- `Content-Type: application/json`

```json
{
  "vt_success": true,
  "vt_short_error": "",
  "vt_error_desc": "",
  "uuid": "c7a19ada-0a6e-419d-9fc4-fd563c18674a",
  "reportDate": "2023-11-01",
  "createdUtc": "2023-11-02T04:02:03",
  "name": "AVC Utilisation Report - 01 November 2023",
  "reports": [
    {
      "csaId": "CSA000000000001",
      "poiId": "0ABC",
      "avcId": "AVC000000000002",
      "priId": "PRI000000000003",
      "speedTier": "12/1",
      "csaPeakHour": "19:30",
      "avcUtilCsaPeakHr": "0.001212",
      "avcPeakHour": "15:00",
      "avcUtilAvcPeakHr": "0.006458",
      "overage": "0.001212",
      "capped": null,
      "breach": false
    },
    {
      "csaId": "CSA000000000004",
      "poiId": "0DEF",
      "avcId": "AVC000000000005",
      "priId": "PRI000000000006",
      "speedTier": "25/5",
      "csaPeakHour": "20:00",
      "avcUtilCsaPeakHr": "0.015772",
      "avcPeakHour": "10:00",
      "avcUtilAvcPeakHr": "1.331757",
      "overage": "0.000000",
      "capped": null,
      "breach": false
    },
    {
      "csaId": "CSA000000000007",
      "poiId": "0GHI",
      "avcId": "AVC000000000008",
      "priId": "PRI000000000009",
      "speedTier": "50/20",
      "csaPeakHour": "23:00",
      "avcUtilCsaPeakHr": "3.860719",
      "avcPeakHour": "21:30",
      "avcUtilAvcPeakHr": "6.308523",
      "overage": "0.360719",
      "capped": null,
      "breach": true
    },
    {
      "csaId": "CSA000000000010",
      "poiId": "0JKL",
      "avcId": "AVC000000000011",
      "priId": "PRI000000000012",
      "speedTier": "25/10",
      "csaPeakHour": null,
      "avcUtilCsaPeakHr": null,
      "avcPeakHour": null,
      "avcUtilAvcPeakHr": null,
      "overage": "0.000000",
      "capped": null,
      "breach": false
    }
  ],
  "_meta": {
    "total_records": 1337,
    "page": 5,
    "limit": 100,
    "count": 100
  },
  "_links": {
    "self": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=5&limit=100",
    "first": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=1&limit=100",
    "last": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=14&limit=100",
    "prev": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=4&limit=100",
    "next": "/api/v1/avc-utilisation/by-uuid/c7a19ada-0a6e-419d-9fc4-fd563c18674a?page=6&limit=100"
  }
}
```


## Suspensions - Beta

This is currently in beta.
There may be breaking changes before it's released.


### Suspend a Service BETA

**URI:** `/suspensions{?apiarySuspendAService}`


#### `POST` POST - Suspend a Service

**URI:** `/suspensions`

Currently only Layer 3 NBN services can be suspended.

Required scopes: all:suspensions


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "vtServiceId": "VT0000123",
  "action": "suspend"
}
```


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


### Resume a Service BETA

**URI:** `/suspensions{?apiaryResumeAService}`


#### `POST` POST - Resume a Service

**URI:** `/suspensions`

This operation will unsuspend a previously suspended service

Required scopes: all:suspensions


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "vtServiceId": "VT0000123",
  "action": "resume"
}
```


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


### Drop a Session BETA

**URI:** `/suspensions{?apiaryDropASession}`


#### `POST` POST - Drop a Session

**URI:** `/suspensions`

This operation is used to drop a session without suspending the underlying service. This can be helpful when moving a service to a faster plan,
rather than having the end user restart the equipment at their premises.

Currently only Layer 3 NBN sessions can be dropped.

Required scopes: all:suspensions


*Request:*

Headers:

- `Content-Type: application/json`
- `Authorization: Bearer put_your_access_token_here`

```json
{
  "vtServiceId": "VT0000123",
  "action": "drop"
}
```


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


## Overview - Beta

This is currently in beta.
There may be breaking changes before it's released.


### Overview BETA

**URI:** `/overview{?apiaryOverview}`


#### `GET` GET - Fetch Overview

**URI:** `/overview`


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
  "activeServiceCount": 123,
  "inFlightOrders": {
    "count": 3,
    "summaries": [
      {
        "vtOrderId": "VTORD0000000001",
        "orderType": "NBN_MODIFY",
        "status": "NEW"
      },
      {
        "vtOrderId": "VTORD0000000002",
        "orderType": "NBN_CONNECT",
        "status": "NBN_APPOINTMENT_REQUIRED"
      },
      {
        "vtOrderId": "VTORD0000000003",
        "orderType": "NBN_DISCONNECT",
        "status": "NBN_ACKNOWLEDGED"
      }
    ]
  }
}
```


## One-Off Charges


### One-Off Charges BETA

**URI:** `/one-off-charges{?apiaryOneOffCharges}`


#### `GET` GET - Request One-Off-Charges for a Service

**URI:** `/one-off-charges{?serviceId,apiaryRequestAvailableMobileNumbers}`

This operation is used to request a list of the one-off charges generated by a given service.

Required scope: read:services

Rate limits: steady - 120 requests/minute, burst - 5 requests/second

**Parameters:**

| Parameter | Type | Required | Description | Example |
|---|---|---|---|---|
|  | string | yes | Virtutel Service ID or Supplier Service ID | PRI000000000001 |


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
  "oneOffCharges": [
    {
      "relatedServiceId": "PRI000000000001",
      "fullDescription": "nbn Transfer",
      "chargeEx": "5.00",
      "taxable": true,
      "currency": "AUD"
    },
    {
      "relatedServiceId": "PRI000000000001",
      "fullDescription": "Example credit",
      "chargeEx": "-1.23",
      "taxable": true,
      "currency": "AUD"
    }
  ]
}
```


## Data Structures


### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    55142,
                    31
                  ]
                ]
              }
            ]
          },
          "content": "LocationResponse1"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        55175,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN Location ID"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        55175,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "id"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        55175,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "LOC123456789001"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        55228,
                        168
                      ]
                    ]
                  }
                ]
              },
              "content": "Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina."
            }
          },
          "attributes": {
            "typeAttributes": [
              "optional"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        55228,
                        168
                      ]
                    ]
                  }
                ]
              },
              "content": "unitTypeCode"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        55228,
                        168
                      ]
                    ]
                  }
                ],
                "samples": [
                  [
                    {
                      "element": "string",
                      "content": "UNIT"
                    }
                  ]
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                55420,
                                25
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Antenna"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            55420,
                            25
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "ANT"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                55455,
                                27
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Apartment"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            55455,
                            27
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "APT"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                55492,
                                42
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Automated Teller Machine"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            55492,
                            42
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "ATM"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                55544,
                                24
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Block"
                    }
               
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    77006,
                    31
                  ]
                ]
              }
            ]
          },
          "content": "LocationResponse2"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        77039,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN Location ID"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        77039,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "id"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        77039,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "LOC123456789001"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        77092,
                        168
                      ]
                    ]
                  }
                ]
              },
              "content": "Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina."
            }
          },
          "attributes": {
            "typeAttributes": [
              "optional"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        77092,
                        168
                      ]
                    ]
                  }
                ]
              },
              "content": "unitTypeCode"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        77092,
                        168
                      ]
                    ]
                  }
                ],
                "samples": [
                  [
                    {
                      "element": "string",
                      "content": "UNIT"
                    }
                  ]
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                77284,
                                25
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Antenna"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            77284,
                            25
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "ANT"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                77319,
                                27
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Apartment"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            77319,
                            27
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "APT"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                77356,
                                42
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Automated Teller Machine"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            77356,
                            42
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "ATM"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                77408,
                                24
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Block"
                    }
               
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    98916,
                    31
                  ]
                ]
              }
            ]
          },
          "content": "LocationResponse3"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        98949,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN Location ID"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        98949,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "id"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        98949,
                        51
                      ]
                    ]
                  }
                ]
              },
              "content": "LOC123456789001"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        99002,
                        168
                      ]
                    ]
                  }
                ]
              },
              "content": "Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina."
            }
          },
          "attributes": {
            "typeAttributes": [
              "optional"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        99002,
                        168
                      ]
                    ]
                  }
                ]
              },
              "content": "unitTypeCode"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        99002,
                        168
                      ]
                    ]
                  }
                ],
                "samples": [
                  [
                    {
                      "element": "string",
                      "content": "UNIT"
                    }
                  ]
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                99194,
                                25
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Antenna"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            99194,
                            25
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "ANT"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                99229,
                                27
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Apartment"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            99229,
                            27
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "APT"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                99266,
                                42
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Automated Teller Machine"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            99266,
                            42
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "ATM"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                99318,
                                24
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Block"
                    }
               
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1405419,
                    30
                  ]
                ]
              }
            ]
          },
          "content": "VTProductOrderConnectBody"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1405451,
                        60
                      ]
                    ]
                  }
                ]
              },
              "content": "Virtutel order ID"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1405451,
                        60
                      ]
                    ]
                  }
                ]
              },
              "content": "vtOrderId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1405451,
                        60
                      ]
                    ]
                  }
                ]
              },
              "content": "VTORDAADD1122FF"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1405513,
                        246
                      ]
                    ]
                  }
                ]
              },
              "content": "Current order status. Note that we use the 'NBN_' prefix for any status derived from an NBN order state, which means that some status values will begin with 'NBN_NBN_'. These are listed roughly in chronological order."
            }
          },
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1405513,
                        246
                      ]
                    ]
                  }
                ]
              },
              "content": "status"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1405513,
                        246
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1405783,
                                93
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "The order has been submitted to NBN and is awaiting validation"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1405783,
                            93
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NBN_ACKNOWLEDGED"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1405886,
                                59
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "The order has been successfully submitted"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1405886,
                            59
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NEW"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1405955,
                                134
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "The order has failed validation. We will review the order and resubmit it after correcting the issue."
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1405955,
                            134
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NBN_ORDER_REJECTED"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1406099,
                                97
                              ]
                            ]
                          }
                   
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1427023,
                    33
                  ]
                ]
              }
            ]
          },
          "content": "VTProductOrderDisconnectBody"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1427058,
                        60
                      ]
                    ]
                  }
                ]
              },
              "content": "Virtutel order ID"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1427058,
                        60
                      ]
                    ]
                  }
                ]
              },
              "content": "vtOrderId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1427058,
                        60
                      ]
                    ]
                  }
                ]
              },
              "content": "VTORDAADD1122FF"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1427120,
                        246
                      ]
                    ]
                  }
                ]
              },
              "content": "Current order status. Note that we use the 'NBN_' prefix for any status derived from an NBN order state, which means that some status values will begin with 'NBN_NBN_'. These are listed roughly in chronological order."
            }
          },
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1427120,
                        246
                      ]
                    ]
                  }
                ]
              },
              "content": "status"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1427120,
                        246
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1427390,
                                158
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "The order is fully complete and has been processed into the VT billing system. This is the final state for a successful order"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1427390,
                            158
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "VT_ORDER_COMPLETED"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1427558,
                                59
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "The order has been successfully submitted"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1427558,
                            59
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NEW"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1427627,
                                93
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "The order has been submitted to NBN and is awaiting validation"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1427627,
                            93
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NBN_ACKNOWLEDGED"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1427730,
                                134
                              ]
                            ]
                   
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1464239,
                    27
                  ]
                ]
              }
            ]
          },
          "content": "ServiceResult"
        }
      },
      "content": [
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464268,
                        36
                      ]
                    ]
                  }
                ]
              },
              "content": "vtServiceId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464268,
                        36
                      ]
                    ]
                  }
                ]
              },
              "content": "VT0000123"
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464306,
                        48
                      ]
                    ]
                  }
                ]
              },
              "content": "supplierServiceId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464306,
                        48
                      ]
                    ]
                  }
                ]
              },
              "content": "PRI000000001234"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464356,
                        114
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN AVC ID. Only included if there's an AVC ID associated with this service"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464356,
                        114
                      ]
                    ]
                  }
                ]
              },
              "content": "avcId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464356,
                        114
                      ]
                    ]
                  }
                ]
              },
              "content": "AVC000000001234"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464472,
                        123
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN location ID. Only included if there's a LOC ID associated with this service"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464472,
                        123
                      ]
                    ]
                  }
                ]
              },
              "content": "locationId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464472,
                        123
                      ]
                    ]
                  }
                ]
              },
              "content": "LOC000000000001"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464597,
                        135
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN Copper Pair or NTD ID. Only included if there's a CPI/NTD ID associated with this service"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464597,
                        135
                      ]
                    ]
                  }
                ]
              },
              "content": "cpiNtdId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464597,
                        135
                      ]
                    ]
                  }
                ]
              },
              "content": "NTD000000000001"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1464734,
                        118
                      ]
                    ]
                  }
                ]
              },
              "content": "NBN Uni-D Port ID. Only included if there's a Uni-D Port ID associated with this service"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourc
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1595785,
                    41
                  ]
                ]
              }
            ]
          },
          "content": "NBNResponseFTTNServiceHealthComplete"
        }
      },
      "content": [
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595828,
                        30
                      ]
                    ]
                  }
                ]
              },
              "content": "status"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595828,
                        30
                      ]
                    ]
                  }
                ]
              },
              "content": "Complete"
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595860,
                        43
                      ]
                    ]
                  }
                ]
              },
              "content": "externalId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595860,
                        43
                      ]
                    ]
                  }
                ]
              },
              "content": "VTTST000000000001"
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595905,
                        35
                      ]
                    ]
                  }
                ]
              },
              "content": "id"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595905,
                        35
                      ]
                    ]
                  }
                ]
              },
              "content": "VTTST000000000001"
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595942,
                        36
                      ]
                    ]
                  }
                ]
              },
              "content": "avcId"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595942,
                        36
                      ]
                    ]
                  }
                ]
              },
              "content": "AVC002000206240"
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595980,
                        28
                      ]
                    ]
                  }
                ]
              },
              "content": "currentCondition"
            },
            "value": {
              "element": "object",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1595980,
                        28
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "member",
                  "content": {
                    "key": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1596014,
                                28
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "code"
                    },
                    "value": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1596014,
                                28
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "STA10008"
                    }
                  }
                },
                {
                  "element": "member",
                  "content": {
                    "key": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1596048,
                                25
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "status"
                    },
                    "value": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1596048,
                                25
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Red"
                    }

```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1914654,
                    26
                  ]
                ]
              }
            ]
          },
          "content": "OOBNContactGetEndUser"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914682,
                        54
                      ]
                    ]
                  }
                ]
              },
              "content": "Contact ID"
            }
          },
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914682,
                        54
                      ]
                    ]
                  }
                ]
              },
              "content": "id"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914682,
                        54
                      ]
                    ]
                  }
                ]
              },
              "content": "OBC0000000001"
            }
          }
        },
        {
          "element": "member",
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914738,
                        31
                      ]
                    ]
                  }
                ]
              },
              "content": "contactType"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914738,
                        31
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1914775,
                                71
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "These receive notifications for a single service"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1914775,
                            71
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "end_user"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1914852,
                                76
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "These receive notifications for every service the RSP owns"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1914852,
                            76
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "rsp"
                }
              ]
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914930,
                        160
                      ]
                    ]
                  }
                ]
              },
              "content": "The end user's name to use in messages sent to them, (e.g. 'Hi <toName>, ...'). Blank string when `contactType` is 'rsp'."
            }
          },
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914930,
                        160
                      ]
                    ]
                  }
                ]
              },
              "content": "toName"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1914930,
                        160
                      ]
                    ]
                  }
                ]
              },
              "content": "John"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1915092,
                        211
                      ]
                    ]
                  }
                ]
              },
              "content": "Your company's name to use in messages sent to an end user, (e.g. '...Regards, Pebcak Internet Solutions'). Blank string when `contactType` is 'rsp'."
            }
          },
          "attributes": {
 
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1916496,
                    22
                  ]
                ]
              }
            ]
          },
          "content": "OOBNContactGetRSP"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916520,
                        54
                      ]
                    ]
                  }
                ]
              },
              "content": "Contact ID"
            }
          },
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916520,
                        54
                      ]
                    ]
                  }
                ]
              },
              "content": "id"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916520,
                        54
                      ]
                    ]
                  }
                ]
              },
              "content": "OBC0000000002"
            }
          }
        },
        {
          "element": "member",
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916576,
                        31
                      ]
                    ]
                  }
                ]
              },
              "content": "contactType"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916576,
                        31
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1916613,
                                76
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "These receive notifications for every service the RSP owns"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1916613,
                            76
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "rsp"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1916695,
                                71
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "These receive notifications for a single service"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1916695,
                            71
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "end_user"
                }
              ]
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916768,
                        151
                      ]
                    ]
                  }
                ]
              },
              "content": "The end user's name to use in messages sent to them (e.g. 'Hi <toName>, ...'). Blank string when `contactType` is 'rsp'."
            }
          },
          "attributes": {
            "typeAttributes": [
              "required"
            ]
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916768,
                        151
                      ]
                    ]
                  }
                ]
              },
              "content": "toName"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916768,
                        151
                      ]
                    ]
                  }
                ]
              }
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1916921,
                        182
                      ]
                    ]
                  }
                ]
              },
              "content": "Your company's name to use in messages sent to an end user, (e.g. '...Regards, Pebcak Internet Solutions'). Blank string when `contactType` is 'rsp'."
            }
          },
          "attributes": {
            "typeAttributes": [
       
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1922044,
                    28
                  ]
                ]
              }
            ]
          },
          "content": "SummaryInvoice"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922074,
                        71
                      ]
                    ]
                  }
                ]
              },
              "content": "Invoice UUID"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922074,
                        71
                      ]
                    ]
                  }
                ]
              },
              "content": "uuid"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922074,
                        71
                      ]
                    ]
                  }
                ]
              },
              "content": "e2b8fe03-827a-4057-baaa-110bda293d41"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922147,
                        69
                      ]
                    ]
                  }
                ]
              },
              "content": "Human-readable invoice number"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922147,
                        69
                      ]
                    ]
                  }
                ]
              },
              "content": "invoiceNumber"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922147,
                        69
                      ]
                    ]
                  }
                ]
              },
              "content": "INV-1234"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922218,
                        62
                      ]
                    ]
                  }
                ]
              },
              "content": "Where the invoice was issued"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922218,
                        62
                      ]
                    ]
                  }
                ]
              },
              "content": "invoiceCountry"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922218,
                        62
                      ]
                    ]
                  }
                ],
                "samples": [
                  [
                    {
                      "element": "string",
                      "content": "AUS"
                    }
                  ]
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1922286,
                                18
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Australia"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1922286,
                            18
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "AUS"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1922310,
                                19
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "New Zealand"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1922310,
                            19
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NZ"
                }
              ]
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1922331,
                        69
                      ]
                    ]
                  }
                ]
        
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1923329,
                    35
                  ]
                ]
              }
            ]
          },
          "content": "LineOnDetailedInvoice"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923366,
                        136
                      ]
                    ]
                  }
                ]
              },
              "content": "Charge description"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923366,
                        136
                      ]
                    ]
                  }
                ]
              },
              "content": "description"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923366,
                        136
                      ]
                    ]
                  }
                ]
              },
              "content": "nbn Layer 2 Aggregated 50/20 - Alice Smith (PRI000000000001) Period: 01/04/23 - 30/04/23"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923504,
                        68
                      ]
                    ]
                  }
                ]
              },
              "content": "How many units this line represents"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923504,
                        68
                      ]
                    ]
                  }
                ]
              },
              "content": "quantity"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923504,
                        68
                      ]
                    ]
                  }
                ]
              },
              "content": "1.0000"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923574,
                        61
                      ]
                    ]
                  }
                ]
              },
              "content": "How much each unit costs"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923574,
                        61
                      ]
                    ]
                  }
                ]
              },
              "content": "unitAmount"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923574,
                        61
                      ]
                    ]
                  }
                ]
              },
              "content": "112.2300"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923637,
                        57
                      ]
                    ]
                  }
                ]
              },
              "content": "Total tax for the line"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923637,
                        57
                      ]
                    ]
                  }
                ]
              },
              "content": "taxAmount"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923637,
                        57
                      ]
                    ]
                  }
                ]
              },
              "content": "11.2200"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923696,
                        182
                      ]
                    ]
                  }
                ]
              },
              "content": "Total for the line. Note that this can either be tax inclusive or exclusive, depending on the value of 'lineTotalsType' at the root of the invoice"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923696,
                        182
                      ]
                    ]
                  }
                ]
              },
              "content": "lineTotal"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923696,

```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1923880,
                    29
                  ]
                ]
              }
            ]
          },
          "content": "DetailedInvoice"
        }
      },
      "content": [
        {
          "element": "ref",
          "content": {
            "href": "SummaryInvoice",
            "path": "content"
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923936,
                        37
                      ]
                    ]
                  }
                ]
              },
              "content": "lineTotalsType"
            },
            "value": {
              "element": "enum",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1923936,
                        37
                      ]
                    ]
                  }
                ],
                "samples": [
                  [
                    {
                      "element": "string",
                      "content": "Exclusive"
                    }
                  ]
                ]
              },
              "content": [
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1923979,
                                54
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Line totals do not include tax"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1923979,
                            54
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "Exclusive"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1924039,
                                47
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Line totals include tax"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1924039,
                            47
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "Inclusive"
                },
                {
                  "element": "string",
                  "meta": {
                    "description": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1924092,
                                37
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "Lines have no tax"
                    }
                  },
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1924092,
                            37
                          ]
                        ]
                      }
                    ]
                  },
                  "content": "NoTax"
                }
              ]
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1924131,
                        16
                      ]
                    ]
                  }
                ]
              },
              "content": "lines"
            },
            "value": {
              "element": "array",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1924131,
                        16
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "LineOnDetailedInvoice",
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1924153,
                            24
                          ]
                        ]
                      }
                    ]
                  }
                },
                {
                  "element": "LineOnDetailedInvoice",
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1924183,
                            24
                          ]
                        ]
                      }
                    ]
                  }
                },
                {
                  "element": "LineOnDetailedInvoice",
                  "attributes": {
                    "sourceMap": [
                      {
                        "element": "sourceMap",
                        "content": [
                          [
                            1924213,
                            24
                          ]
                        ]
                      }
                    ]
                  }
                }
              ]
            }
          }
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945406,
                    64
                  ]
                ]
              }
            ]
          },
          "content": "Note that these don't render correctly within One Of Blocks"
        }
      }
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945470,
                    17
                  ]
                ]
              }
            ]
          },
          "content": "Custom Enums"
        }
      }
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945487,
                    30
                  ]
                ]
              }
            ]
          },
          "content": "EnumnbnProductType"
        }
      },
      "content": [
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945519,
                    7
                  ]
                ]
              }
            ]
          },
          "content": "NCAS"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945528,
                    7
                  ]
                ]
              }
            ]
          },
          "content": "NFAS"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945537,
                    7
                  ]
                ]
              }
            ]
          },
          "content": "NHAS"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945546,
                    7
                  ]
                ]
              }
            ]
          },
          "content": "NSAS"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945555,
                    7
                  ]
                ]
              }
            ]
          },
          "content": "NWAS"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945564,
                    30
                  ]
                ]
              }
            ]
          },
          "content": "EnumsipProductType"
        }
      },
      "content": [
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945596,
                    8
                  ]
                ]
              }
            ]
          },
          "content": "trunk"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945606,
                    6
                  ]
                ]
              }
            ]
          },
          "content": "cts"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945614,
                    12
                  ]
                ]
              }
            ]
          },
          "content": "fax2email"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945628,
                    16
                  ]
                ]
              }
            ]
          },
          "content": "General Use"
        }
      }
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1945644,
                    31
                  ]
                ]
              }
            ]
          },
          "content": "VTResponseSuccess"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945677,
                        133
                      ]
                    ]
                  }
                ]
              },
              "content": "Whether or not the request was successful. `true` for all HTTP2xx response codes, `false` otherwise"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945677,
                        133
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_success"
            },
            "value": {
              "element": "boolean",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945677,
                        133
                      ]
                    ]
                  }
                ]
              },
              "content": true
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945812,
                        115
                      ]
                    ]
                  }
                ]
              },
              "content": "Short, machine-readable error description, (or a blank string for successful requests)"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945812,
                        115
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_short_error"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945812,
                        115
                      ]
                    ]
                  }
                ]
              }
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945929,
                        111
                      ]
                    ]
                  }
                ]
              },
              "content": "Long, human-readable error description, (or a blank string for successful requests)"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945929,
                        111
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_error_desc"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1945929,
                        111
                      ]
                    ]
                  }
                ]
              }
            }
          }
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1946041,
                    29
                  ]
                ]
              }
            ]
          },
          "content": "VTResponseError"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946072,
                        134
                      ]
                    ]
                  }
                ]
              },
              "content": "Whether or not the request was successful. `true` for all HTTP2xx response codes, `false` otherwise"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946072,
                        134
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_success"
            },
            "value": {
              "element": "boolean",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946072,
                        134
                      ]
                    ]
                  }
                ]
              },
              "content": false
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946208,
                        132
                      ]
                    ]
                  }
                ]
              },
              "content": "Short, machine-readable error description, (or a blank string for successful requests)"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946208,
                        132
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_short_error"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946208,
                        132
                      ]
                    ]
                  }
                ]
              },
              "content": "example_error"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946342,
                        156
                      ]
                    ]
                  }
                ]
              },
              "content": "Long, human-readable error description, (or a blank string for successful requests)"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946342,
                        156
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_error_desc"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946342,
                        156
                      ]
                    ]
                  }
                ]
              },
              "content": "This is a longer description of the error"
            }
          }
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1946499,
                    35
                  ]
                ]
              }
            ]
          },
          "content": "VTResponse404NotFound"
        }
      },
      "content": [
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946536,
                        132
                      ]
                    ]
                  }
                ]
              },
              "content": "Whether or not the request was successful. `true` for all HTTP2xx response codes, `false` otherwise"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946536,
                        132
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_success"
            },
            "value": {
              "element": "boolean",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946536,
                        132
                      ]
                    ]
                  }
                ]
              },
              "content": false
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946670,
                        128
                      ]
                    ]
                  }
                ]
              },
              "content": "Short, machine-readable error description, (or a blank string for successful requests)"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946670,
                        128
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_short_error"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946670,
                        128
                      ]
                    ]
                  }
                ]
              },
              "content": "not_found"
            }
          }
        },
        {
          "element": "member",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946800,
                        151
                      ]
                    ]
                  }
                ]
              },
              "content": "Long, human-readable error description, (or a blank string for successful requests)"
            }
          },
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946800,
                        151
                      ]
                    ]
                  }
                ]
              },
              "content": "vt_error_desc"
            },
            "value": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1946800,
                        151
                      ]
                    ]
                  }
                ]
              },
              "content": "The requested resource was not found"
            }
          }
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1946952,
                    30
                  ]
                ]
              }
            ]
          },
          "content": "EnumPriorityAssist"
        }
      },
      "content": [
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1946984,
                    5
                  ]
                ]
              }
            ]
          },
          "content": "No"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1946991,
                    6
                  ]
                ]
              }
            ]
          },
          "content": "Yes"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1946998,
                    30
                  ]
                ]
              }
            ]
          },
          "content": "EnumAppointmentSLA"
        }
      },
      "content": [
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947030,
                    11
                  ]
                ]
              }
            ]
          },
          "content": "Standard"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947043,
                    14
                  ]
                ]
              }
            ]
          },
          "content": "Accelerated"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947059,
                    20
                  ]
                ]
              }
            ]
          },
          "content": "Reduced Lead Time"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947080,
                    35
                  ]
                ]
              }
            ]
          },
          "content": "EnumAppointmentSlotType"
        }
      },
      "content": [
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947117,
                        15
                      ]
                    ]
                  }
                ]
              },
              "content": "Morning"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947117,
                    15
                  ]
                ]
              }
            ]
          },
          "content": "AM"
        },
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947134,
                        17
                      ]
                    ]
                  }
                ]
              },
              "content": "Afternoon"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947134,
                    17
                  ]
                ]
              }
            ]
          },
          "content": "PM"
        },
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947153,
                        20
                      ]
                    ]
                  }
                ]
              },
              "content": "After hours"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947153,
                    20
                  ]
                ]
              }
            ]
          },
          "content": "AHA"
        },
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947175,
                        32
                      ]
                    ]
                  }
                ]
              },
              "content": "Co-ordinated appointment"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947175,
                    32
                  ]
                ]
              }
            ]
          },
          "content": "CA"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947208,
                    27
                  ]
                ]
              }
            ]
          },
          "content": "EnumEndUserType"
        }
      },
      "content": [
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947237,
                    14
                  ]
                ]
              }
            ]
          },
          "content": "Residential"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947253,
                    11
                  ]
                ]
              }
            ]
          },
          "content": "Business"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947265,
                    26
                  ]
                ]
              }
            ]
          },
          "content": "EnumDemandType"
        }
      },
      "content": [
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947293,
                        50
                      ]
                    ]
                  }
                ]
              },
              "content": "Applies to most appointments"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947293,
                    50
                  ]
                ]
              }
            ]
          },
          "content": "Standard Install"
        },
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947345,
                        88
                      ]
                    ]
                  }
                ]
              },
              "content": "Used for FTTN Service Class 12 with Copper Pair Status 'Active'"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947345,
                    88
                  ]
                ]
              }
            ]
          },
          "content": "Non EU Jumper Only"
        },
        {
          "element": "string",
          "meta": {
            "description": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947435,
                        90
                      ]
                    ]
                  }
                ]
              },
              "content": "Used for Fixed Wireless NTD upgrades (to support high speed tiers)"
            }
          },
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947435,
                    90
                  ]
                ]
              }
            ]
          },
          "content": "Additional Install"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947527,
                    22
                  ]
                ]
              }
            ]
          },
          "content": "Service Restoration"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947551,
                    22
                  ]
                ]
              }
            ]
          },
          "content": "Install - Deinstall"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "enum",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947574,
                    37
                  ]
                ]
              }
            ]
          },
          "content": "EnumServiceRestorationSLA"
        }
      },
      "content": [
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947613,
                    11
                  ]
                ]
              }
            ]
          },
          "content": "Standard"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947626,
                    16
                  ]
                ]
              }
            ]
          },
          "content": "Enhanced - 12"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947644,
                    23
                  ]
                ]
              }
            ]
          },
          "content": "Enhanced - 12 (24/7)"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947669,
                    15
                  ]
                ]
              }
            ]
          },
          "content": "Enhanced - 8"
        },
        {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947686,
                    22
                  ]
                ]
              }
            ]
          },
          "content": "Enhanced - 8 (24/7)"
        }
      ]
    }
  ]
}
```

### 

```json
{
  "element": "dataStructure",
  "content": [
    {
      "element": "object",
      "meta": {
        "id": {
          "element": "string",
          "attributes": {
            "sourceMap": [
              {
                "element": "sourceMap",
                "content": [
                  [
                    1947709,
                    29
                  ]
                ]
              }
            ]
          },
          "content": "TimeslotDetails"
        }
      },
      "content": [
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947740,
                        46
                      ]
                    ]
                  }
                ]
              },
              "content": "appointmentSlotType"
            },
            "value": {
              "element": "EnumAppointmentSlotType",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947740,
                        46
                      ]
                    ]
                  }
                ]
              }
            }
          }
        },
        {
          "element": "member",
          "content": {
            "key": {
              "element": "string",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947788,
                        18
                      ]
                    ]
                  }
                ]
              },
              "content": "validFor"
            },
            "value": {
              "element": "object",
              "attributes": {
                "sourceMap": [
                  {
                    "element": "sourceMap",
                    "content": [
                      [
                        1947788,
                        18
                      ]
                    ]
                  }
                ]
              },
              "content": [
                {
                  "element": "member",
                  "content": {
                    "key": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1947812,
                                54
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "startDateTime"
                    },
                    "value": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1947812,
                                54
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "2019-08-23T08:00:00+10:00"
                    }
                  }
                },
                {
                  "element": "member",
                  "content": {
                    "key": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1947872,
                                52
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "endDateTime"
                    },
                    "value": {
                      "element": "string",
                      "attributes": {
                        "sourceMap": [
                          {
                            "element": "sourceMap",
                            "content": [
                              [
                                1947872,
                                52
                              ]
                            ]
                          }
                        ]
                      },
                      "content": "2019-08-23T12:00:00+10:00"
                    }
                  }
                }
              ]
            }
          }
        }
      ]
    }
  ]
}
```