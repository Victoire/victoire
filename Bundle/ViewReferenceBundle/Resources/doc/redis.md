# Redis to manage View References

## About

This bundle integrate [SncRedisBundle](https://github.com/snc/SncRedisBundle) and [Predis](https://github.com/nrk/predis)

## Why use Redis ?

[Redis](http://redis.io/) give an easy way to access to an on-disk persistence and a key/value database that is really high speed for simple data like our views references.
The old system that was using xml to save views references, required us to rewrite the file everytime a view reference is modified and didnt the possibility of easy update or remove a view reference.

## How it works ?

Basically we have 5 class that are used for this:
- ViewReferenceRedisDriver
- ViewReferenceRedisManager
- ViewReferenceRedisRepository
- ViewReferenceRedisTool
- UrlManager


_The ViewReferenceRedisTool :_

This class contain some methods to help for redis.
Have a look to redislize and unredislize in particular, this methods transform objects, array, strings, ... to be able to save them in redis.

Class Methods:
- redislize($data)
- unredislize($data)
- redislizeArray(array $data)
- unredislizeArray(array $data)
- generateKey($alias, $id)


_The ViewReferenceRedisManager :_

This class contains methods to create, edit, remove references on redis.

Class Methods:
- create(array $data)
- update($id, array $data)
- remove($id)
- index($id, $values)
- addChild($parentId, $childId)
- reset()


_The ViewReferenceRedisRepository :_

This class contains methos to get, find references on redis.

Class Methods:
- getAll()
- getAllBy(array $criteria, $type = "AND")
- getResults(array $data)
- findById($id)
- findValueForId($value, $id)
- getChildren($id)


_The UrlManager :_

This class contain the specific logic to work with urls.

Class Methods:
- buildUrl(ViewReference $viewReference)
- setUrl($refId, $url, $locale = "fr")
- findRefIdByUrl($url ="", $locale = "fr")
- removeUrl($url, $locale)
- removeUrlForViewReference(ViewReference $viewReference)


_The ViewReferenceRedisDriver :_

This class is the most important for redis. To work with redis and view references, this is the only service you have to use.
This service contains all the methods you need.

Class Methods:

- findReferenceByView(View $view)
- saveReferences(array $viewReferences, $parentId = null, $reset = true)
- saveReference(ViewReference $viewReference, $parentId = null)
- removeReference(ViewReference $viewReference)
- getReferenceByUrl($url, $locale)
- getReferencesByParameters($parameters, $transform = true, $keepChildren = false)
- getOneReferenceByParameters($parameters, $transform = true, $keepChildren = false)
- hasReference()
- getChoices($refId = null, $depth = 0)