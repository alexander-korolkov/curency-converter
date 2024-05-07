# Description
The project is a currency converter implemented as an API. The OpenAPI contract is available at `/app/contract/api.yaml`
The recommended provider **currate.ru** was chosen to provide the courses. 
In case of currency pair is not available in currate.ru the system converts through one (or two) acceptable currencies for conversion: USD, EUR, RUB.

**PLEASE NOTE:** Unfortunately the currate.ru provides only the old data for the rates that were actual in 2018-2019.

# Installation
- Please go to the project root directory and run `docker compose up -d`
- Please go inside container with this command: `docker exec -it php bash`
- In the container run `composer up`
After that, project should be available at http://localhost:8080/


# Possible ways to improve 
It is possible to add other currency providers, and it would be nice to add a “fallback” provider in case of base provider is not response. 
Also, additional providers would be helpful in case if we don't have the direct conversion for the currency pair or in order to provide best or average rates to the clients.

Another important thing is security. In real project it would be better to keep the provider key as "secret" value in the "vault". 
And add ssl for the https. 

# Test
Please take a look at tests in the "/app/tests" directory.

# Cache
This app store some cache data in Redis. 
The App doesn't use Redis for the 'test' environment in order to exclude cache affects.  

# Methods
**GET /currencies-list**

**Description:** Retrieve a list of available currencies. This endpoint provides a simple way to fetch all supported currency codes from the system.

**Parameters**
None required. This endpoint does not take any parameters and simply returns the full list of currencies.

**Example of request:**
`GET http://localhost:8080/api/v1/currencies-list`

**Example of successful response:**
```json
[
  "BCH",
  "EUR",
  "GBP",
  "JPY",
  "RUB",
  "USD",
  "XRP",
  "BTC",
  "BTG",
  "BYN",
  "CAD",
  "CHF",
  "CNY",
  "ETH",
  "AED",
  "AMD",
  "BGN",
  "KZT",
  "TRY",
  "AUD",
  "GEL",
  "IDR",
  "AZN",
  "LKR",
  "MDL",
  "MMK",
  "RSD",
  "MYR",
  "NZD",
  "SGD",
  "UAH",
  "THB",
  "ILS",
  "KGS",
  "VND",
  "ZEC"
]
```

**Example of the error:**
```json
{
  "error": "Bad request error"
}
```

**GET /convert**

**Description:** Convert specified amount from one currency to another. This endpoint allows clients to find out the equivalent amount in a target currency.

**Parameters**
- **from** (string, required): The currency code to convert from. For example, 'USD', 'EUR', etc.
- **to** (string, required): The currency code to convert to. For example, 'JPY', 'GBP', etc.
- **amount** (number, required): The amount in the source currency that needs to be converted.

**Example of request:**
`GET http://localhost:8080/api/v1/convert?from=ETH&to=BTC&amount=3456`

**Example of successful response:**
```json
{
  "from": "ETH",
  "to": "BTC",
  "amount": 3456,
  "convertedAmount": "92.17831342464",
  "rate": "0.02667196569"
}
```

**Example of the error:**
```json
{
  "error": "Bad request error"
}
```

**Error Handling**
- **400 Bad Request:** This error occurs if the query parameters are missing, empty, or formatted incorrectly. Ensure all required parameters are included in the request.
- **404 Not Found:** This might occur if the endpoint is incorrectly specified.
- **500 Internal Server Error:** Indicates a server-side error. This might happen due to issues with backend services necessary for the conversion process.

# Error logging
Logging organized with Monolog. The API logs all errors to the /var/log directory. This log contains the date and time the error occurred, as well as a description of the error.