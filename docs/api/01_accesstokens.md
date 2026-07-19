# Access Tokens

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
