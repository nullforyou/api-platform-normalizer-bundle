api-platform-normalizer-bundle
---

api-platform中使用`jsonld`格式时，抛出异常，会规范化为自定义的`Error`;

如果定义异常的code,会在`hydra:statusCode`中体现；

如：
```php

throw new \Exception('异常', 400000);

```
jsonld:

```json
{
    "@context": "/goods/contexts/Error",
    "@type": "hydra:Error",
    "hydra:title": "An error occurred",
    "hydra:description": "异常",
    "hydra:statusCode": 400000
}
```
