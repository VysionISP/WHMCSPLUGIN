# CVC Entitlements

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
