# Suspensions - Beta

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
