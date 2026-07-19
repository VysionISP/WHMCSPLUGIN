# Invoices - Beta

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
