# Zepgram JsonSchema

A Magento implementation for validating JSON Structures against a given Schema with support for Schemas of Draft-3 or Draft-4.<br>
Based on https://github.com/justinrainbow/json-schema json schema.<br>
For more information about json schema: http://json-schema.org/

## Installation
```
composer require zepgram/module-json-schema
bin/magento module:enable Zepgram_JsonSchema
bin/magento setup:upgrade
```

## Guideline

1. Add the formatted json-schema in your module, where webservice will be implemented under `/etc/schema` directory.
2. Create a Virtual Class of type `Zepgram\JsonSchema\Model\Validator` and adapt `fileName` and `moduleName` parameters.

## Example

**/etc/di.xml**
```xml
<virtualType name="HelloWorldValidator" type="Zepgram\JsonSchema\Model\Validator">
    <arguments>
        <argument name="fileName" xsi:type="string">schema/hello-world-service.json</argument>
        <argument name="moduleName" xsi:type="string">Zepgram_HelloWorld</argument>
    </arguments>
</virtualType>
```

**/etc/schema/hello-world-service.json**
```json
{
  "type":"array",
  "items":{
    "type":"object",
    "required":[
      "helloId",
      "helloContact"
    ],
    "properties":{
      "helloId":{
        "type":"string",
        "description":"Hello Id"
      },
      "helloContact":{
        "type":"array",
        "items":{
          "type":"object",
          "required":[
            "age",
            "fullName"
          ],
          "properties":{
            "age":{
              "type":"integer",
              "format":"int32",
              "description":"contact age",
              "minimum":-2147483648,
              "maximum":2147483647
            },
            "fullName":{
              "type":"object",
              "required":[
                "firstname",
                "lastname"
              ],
              "properties":{
                "firstname":{
                  "type":"string",
                  "description":"contact firstname"
                }, 
                "lastname":{
                  "type":"string",
                  "description":"contact lastname"
                }
              }
            }
          }
        }
      }
    }
  },
  "$schema":"http://json-schema.org/draft-04/schema#"
}
```

## Open API V3

If your architecture is using open API-V3 to describe web service usage you can convert it to json schema.
1. Install the node utility: https://github.com/mikunn/openapi2schema
2. Convert the open api yaml file into JSON:<br>
  `openapi2schema -i my-custom-service.yaml | python -m json.tool | jq '."%end_point%".post.body' > my-custom-service.json`<br>
where `%endpoint%` must be replaced by your api endpoint described in yaml file (e.g.: /v1/customEndPoint)

## Issues

If you encountered an issue during installation or with usage, please report it on this github repository.
