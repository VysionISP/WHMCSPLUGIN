# Callbacks

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
