# Demographics

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
