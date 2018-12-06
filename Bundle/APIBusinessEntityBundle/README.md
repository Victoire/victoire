# API Mode

## BusinessEntity
This Bundle is the brother of the [BusinessEntityBundle](https://github.com/Victoire/victoire/blob/3.0/Bundle/BusinessEntityBundle/README.md)
It adds the support of BusinessEntities that are not base on Doctrine entities but on any API.
To acheive this, you must add an entry in the vic_business_entity with the following:
- 'type' should be defined to 'apibusinessentity'
- 'name' will be the name of the BusinessEntity, used in some forms
- 'availableWidgets' is the same as for an ORM BusinessEntity
- 'endpoint_id' is a relation to an API endpoint, dedailed later
- 'resource' is the name of the resource fetched on the endpoint
- 'getMethod' is the url used to get a resource through the api
- 'listMethod' is the url used to get all resources
- 'pagerParameter' is the argument added to the listMethod to paginate

## API Endpoint

An API Endpoint is a external (or internal) API you want to fetch for your Victoire website.
It is pretty simple to configure, you just have to register the following in the vic_api_endpoint table:
- 'name' is the arbitrary name of your api
- 'host' is the absolute url of the api you want to fetch
- 'token' is the value of your API token
- 'tokenType' is the tocken strategy used for authentication, supported values are 'get_parameter' and 'header'
 - 'get_parameter' will add the token as a url parameter
 - 'header' will pass the token on "Authorization" request header

