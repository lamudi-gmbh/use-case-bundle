# Toolkit

## Input Processors

### ArrayInputProcessor
* Alias: ```array```
* Accepted input: PHP arrays

Rewrites data from an associative array to the Request object, matching array keys to object fields by name.

Configuration options:

* ```map``` - optional. Allows to specify custom mapping from array keys to the fields in the use case request. Use an 
associative array with input array keys as keys and Use Case Request field names as values.

### HttpInputProcessor

* Alias: ```http```
* Accepted input: ```Symfony\Component\HttpFoundation\Request```

Populates the fields of the Request object with data from Symfony HTTP Request. It looks for matching values in GET 
parameters, POST parameters, submitted files, cookies, server settings, sent headers and request attributes - in this 
order. If a field is matched more than once, the later value overrides the previous one (e.g. POST parameters override 
GET, files override POST and GET, and attributes override all).

Configuration options:

* ```order``` - optional, default: ```GPFCSHA``` (Get, Post, Files, Cookies, Server, Headers, Attributes). Use this 
option to modify the order in which the parameter sources are read. The letters correspond to the first letters in the 
aforementioned sources. It is allowed to omit one or more of them.
* ```map``` - optional. Allows to specify custom mapping from parameter names to the fields in the use case request. 
Use an associative array with input parameter names as keys and Use Case Request field names as values.

#### Example:
Given a POST request with ```?foo=bar&name=John``` query parameters and ```foo=baz``` POST parameter, an HTTP Request 
object ```$httpRequest``` initialized by and a Use Case Request object ```$useCaseRequest``` defined as follows:

```
class MyRequest extends \Lamudi\UseCaseBundle\Request\Request
{
    public $foo;
    public $name;
    public $age = 26;
    public $address;
}
```

when the ```initializeRequest($useCaseRequest, $httpRequest)``` is invoked, the value of ```MyRequest::$foo``` will be 
```baz``` (POST taking precedence over GET) and the value of ```MyRequest::$name``` will be ```John```. Because no matching 
parameters exist for fields ```$age``` and ```$address```, the value of ```$age``` will remain ```26``` and ```$address``` 
will be ```null```.

### FormInputConverter
* Alias: ```form```
* Accepted input: ```Symfony\Component\HttpFoundation\Request```

Creates a form by given name, then handles the HTTP request using this form. If ```data_field``` configuration option is 
set, the data resulting from handling the request are dumped into the specified field of the Use Case Request object. 
Otherwise, the Use Case Request object itself is populated.

Configuration options:

* ```name``` - required. The name of the form type to use.
* ```data_field``` - optional.


### JsonBodyInputProcessor
Alias: ```json```
Accepted input: ```Symfony\Component\HttpFoundation\Request```

Reads the body content of the HTTP request, decodes it as JSON and populates the Use Case Request fields with the values 
from the JSON.

## Response Processors

### IdentityResponseProcessor
* Alias: ```identity```

A default Processor that just forwards whatever has been returned by the Use Case ```execute()``` method. Useful if you 
are interested in receiving an unprocessed Use Case Response. If the Use Case throws an exception, it is simply re-thrown.

### TwigRenderer
* Alias: ```twig```

This processor will use the Use Case Response data as parameters to a Twig template, the name of which is specified in 
the required template parameter. It also provides the possibility to easily render Symfony forms in the views. See 
description of ```forms``` configuration option for details.

If the Use Case throws a ```AlternativeCourseException```, Symfony's ```NotFoundHttpException``` will be thrown with the same 
message as in the original exception. Any other exceptions are re-thrown.

Configuration options:

* ```template``` - required. The name of the Twig template to be rendered.
* ```forms``` - optional. An array of forms to be displayed in the template. The keys of the array specify the name of 
the variable that will contain the form view. The value can be either a string with the form name or an array of options:
    * ```name``` - the form name
    * ```data_field``` - the name of the field in the Response object that contains form data. These data will be set 
    in the form before the form view is created.

#### Example
Given Twig Renderer configured with these options:

```
template: AppBundle:default:index.html.twig
forms:
    contactForm: contact_form
    searchForm:
        name: advanced_search_form
        data_field: searchFormFilters
```

When the Use Case is executed with following request data:

```
'searchFormFilters' => ['foo' => 'bar', 'baz' => 1]
```

and the execution is successful, the Twig template ```AppBundle:default:index.html.twig``` will be rendered with 
following parameters:

```
contactForm: view of contact_form
searchForm: view of advanced_search_form with 'bar' as a value of field foo and 1 as a value of field baz
```

### JsonRenderer
* Alias: ```json```

This processor will serialize the returned response as JSON, then return it as content of Symfony's JsonResponse. If 
a Use Case Exception is thrown, the resulting JSON contains fields code and message with respective values from the 
exception. Any other exceptions are re-thrown.

Configuration options:

* ```append_on_success``` - optional. A list of key-value pairs that will be appended to the output JSON when the Use 
Case is executed successfully. These fields will be overridden by fields from the Response object in case of name collision.
* ```append_on_error``` - optional. Same as above, except when the Use Case throws an exception during execution.

#### Example - success
Given a successfully executed Use Case that returns a Response object containing fields:

```
status: active
code: 200
```

and a configuration for the Response Processor for this Use Case:

```
append_on_success:
    code: 123
    success: true
```

When ```JsonRenderer``` processes the Response, it returns an instance of ```Symfony\Component\HttpFoundation\JsonResponse``` 
with the following content:

```
{"status":"active","code":200,"success":true}
```

#### Example - error
Given a use case that throws an exception with message "General Protection Fault" and code 500 and following configuration:

```
append_on_error:
    success: false
```

When the Processor handles the exception, it returns an instance of ```Symfony\Component\HttpFoundation\JsonResponse``` 
with the following content:

```
{"success":false,"code":500,"message":"General Protection Fault"}
```
