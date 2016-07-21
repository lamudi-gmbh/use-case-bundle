# Concept

Every line of code ever written was written to serve some purpose. Some are purely training exercises, others contest 
entries, or projects created just for fun- but the most visible software serves the purpose of satisfying the daily needs 
of its users. Whenever you read news online, check the weather on a smartphone, or send e-mails, you use software designed 
for this purpose. You expect devices to perform how you need it, regardless of whether it is a desktop program, smartphone 
app, or website.

The developers of applications face the difficult task of writing code that not only satisfies these needs, but also 
prepares for future enhancements and exhibits the least possible amount of undesired side effects. A lot has been written 
about how to keep your code clean and bug-free but does your code also say out loud why it was written in the first place?

Often times when we develop software, we describe it with the language understood primarily to  developers. Open a Symfony 
bundle and you will see Controllers, Views, Configuration, Services, Entities. It is not always easy to find where the 
business logic, so important to our clients, is actually implemented. You will spend time going through the Controllers 
or the Views as the first potential suspects, then go deeper into Entities or Services until finally you discover the 
line that embodies the crucial business rule, that the stakeholders have asked you to change.

## Introducing Use Cases

Whoever visits your blog or wants to buy stuff on your webshop does not care one bit about HTML, or sending forms with 
POST requests, or AJAX. There are people who want us to believe they care about cookies, but that's another story. All 
this information is only relevant to developers... and maybe hackers. What users really want is a blog with interesting 
content and a webshop that offers good, cheap products and delivers them quickly. In order to achieve this, they will 
perform various actions on these websites. These actions are called Use Cases. Every Use Case is a specific scenario in 
which a user can use an application. Use Cases are often described with a simple sentence, such as “List all products” 
or “As a customer, I want to be able to add a product to the shopping cart”.

Let's list a few Use Cases for an imaginary webshop:

* List products in category
* View product page
* Add product to shopping cart
* Place order

Each of these Use Cases needs certain input from the user. For example, you cannot list products in category if you don't 
know which category is of interest to the user. The data structure that contains all of the information required by the 
Use Case is called **Use Case Request**. Let's try to identify this information for our example Use Cases:

* List products in category
    * Category identifier (for example, numeric ID or an SEO-friendly slug)
* View product page
    * Product identifier
* Add product to shopping cart
    * Product identifier
    * Quantity
* Place order
    * Delivery address
    * Payment method
    * Discount voucher
 
Every Use Case has one primary course of action and likely has several alternative courses. When the execution follows 
the primary course, it means everything went well. However, different things can go wrong, in which case a Use Case 
follows an alternative path. Let's identify courses for the above Use Cases:

* List products in category
    * Primary course: Products were successfully retrieved
    * Alternative courses:
        * Category was not found
        * Failed to retrieve the products, for example due to database failure
* View product page
    * Primary course: Product was successfully retrieved
    * Alternative courses:
        * Product was not found
        * Failed to retrieve the product
* Add product to shopping cart
    * Primary course: Product was successfully added to the cart
    * Alternative courses:
        * Product does not exist
        * Product is out of stock
        * Failed to retrieve the product
        * Failed to save the shopping cart
* Place order
    * Primary course: Order was successfully placed
    * Alternative courses:
        * Invalid input from the user (malformed address, invalid payment method)
        * Invalid or expired discount voucher
        * Failed to create the order in system

Whatever the result of Use Case execution is, it is returned as a **Use Case Response**. In case of successful execution, 
the Response contains all the information that we display to the user. For example, the Response of “List products in 
category” Use Case contains all the products in desired category, or a chosen number of them if the result is too big 
to be displayed on one page. Alternatively, if something goes wrong, the Response contains the information that will be 
used to identify the failure, such as an error message and code.

## Implementation

To implement a Use Case, a class that represents the Use Case must be created. Let's demonstrate basic implementation 
using the “List products in category” Use Case as an example.

```
<?php
// src/MyBundle/UseCase/ListProductsInCategory_stub.php

namespace MyBundle\UseCase;

use Lamudi\UseCaseBundle\Annotation\UseCase;

/**
 * @UseCase()
 */
class ListProductsInCategory
{
}
```

The Use Case must also be registered as a service in order to be picked by the Use Case Container.
 
```
# app/config/services.yml

my_app.use_case.list_products_in_category:
    class: MyBundle\UseCase\ListProductsInCategory
```

Now we must create objects that represent the Use Case Request. When a Use Case is executed, it must receive its Request 
object as an argument to the ```execute()``` method. In the Use Case class, this argument must be type hinted in order 
to help the Bundle resolve the right request class for your Use Case. Let's now create the Request class for “List products 
in category” Use Case:

```
<?php
// src/MyBundle/UseCase/ListProductsInCategoryRequest.php

namespace MyBundle\UseCase;

class ListProductsInCategoryRequest
{
    /**
     * @var int
     */
    public $categoryId;
}
```

An object that represents a Use Case Request is a simple data structure. It might as well be just an associative array. 
There is, however, one big advantage of defining a class for the Request: it clearly exposes the requirements of your 
Use Case. You can see all the fields that constitute the Request right away when you open the class.

The details of the ```execute()``` method implementation are not important right now. What matters is the Use Case 
Response that will contain the execution result. Similarly to a Request object, the Use Case Response is a data structure 
that contains all the data that will be presented to the user. The Response class for “List products in category” Use 
Case might looks as follows:

```
<?php
// src/MyBundle/UseCase/ListProductsInCategoryResponse.php

namespace MyBundle\UseCase;

use MyBundle\Entity\Product;

class ListProductsInCategoryResponse
{
    /**
     * @var string
     */
    public $categoryName;

    /**
     * @var Product[]
     */
    public $products = [];
}

```

The ```execute()``` method should create an instance of this class and populate its fields with the right data. You can 
create different Response objects for different courses of action. However, it is recommended that alternative courses be 
communicated by throwing exceptions. It will allow you to handle the failures and other alternatives in a more structured 
way, especially if you define your own exceptions specific to results of the alternative courses. In our example, we can 
create the following exceptions:

```
<?php
// src/MyBundle/UseCase/CategoryNotFoundException.php

namespace MyBundle\UseCase;

use Lamudi\UseCaseBundle\Exception\AlternativeCourseException;

class CategoryNotFoundException extends AlternativeCourseException
{
}

```

```
<?php
// src/MyBundle/UseCase/RetrievalFailureException.php

namespace MyBundle\UseCase;

use Lamudi\UseCaseBundle\Exception\AlternativeCourseException;

class RetrievalFailureException extends AlternativeCourseException
{
}

```

It's recommended for your exceptions to extend the ```AlternativeCourseException``` that's supplied with Use Case Bundle. It will 
distinct the exception thrown as a consequence of your business logic from other kinds of failures. It is also used by 
the tools provided by Use Case Bundle - you will find more on that topic in chapter [Use Cases in Symfony](02-use-cases-in-symfony.md).

Let's now write a skeleton implementation of the ```execute()``` method that will give you a gist of the Use Case flow:

```
<?php
// src/MyBundle/UseCase/ListProductsInCategory

namespace MyBundle\UseCase;

use Lamudi\UseCaseBundle\Annotation\UseCase;

/**
 * @UseCase()
 */
class ListProductsInCategory
{
    public function execute(ListProductsInCategoryRequest $request)
    {
        $response = new ListProductsInCategoryResponse();

        try {
            $category = $this->findCategory($request->categoryId);
            if (!$category) {
                throw new CategoryNotFoundException();
            }

            $response->categoryName = $category->getName();
            $response->products = $this->findProductsInCategory($category);
        } catch (\Some\Database\Exception $e) {
            throw new RetrievalFailureException();
        }

        return $response;
    }

    private function findCategory($categoryId)
    {
        // find the category, for example with Doctrine
    }

    private function findProductsInCategory($category)
    {
        // find the products
    }
}
```

You can see how the code of the ```execute()``` method embodies the entire logic of the Use Case. First an attempt to 
retrieve the category is made. If it is not found, the execution is interrupted and the appropriate error is communicated 
by throwing an exception. Then the fields of the Use Case Response object are filled with data that will be eventually 
displayed to the user. If at any point the database fails, the database-specific exception is intercepted and replaced 
by a more generic one, which simply informs the user about what went wrong and does not provide unnecessary or potentially 
sensitive information. Eventually, if everything goes well, the Response object is returned.

Finally, it's time to execute the Use Case from the controller. The code in the Symfony controller will look similar to this:

```
<?php
// src/MyBundle/Controller/MyController.php

namespace MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class MyController extends Controller
{
    /**
     * @Route("/category/{categoryId}")
     */
    public function myAction($categoryId)
    {
        $input = ['categoryId' => $categoryId];
        $response = $this->get('lamudi_use_case.executor')->execute('list_products_in_category', $input);
        return $this->render('MyBundle:products:category.html.twig', (array)$response);
    }
}
```

First, we need to access the Use Case Executor. We can take it directly from Symfony container or inject it into our 
controller, if it's registered as a service. 

Second, collect the data from the HTTP request into an array. This array will be later used to populate your Use Case Request.

Now the executor is used to execute the Use Case. Internally, it resolves the Use Case Request, populates it with data 
from the specified input, passes it to the ```execute()``` method of your Use Case and returns the Response back to you. 

Finally, with the Use Case Response returned to us, we are ready to send the output to the user. In this example, we cast 
the Response object to array and pass it to the templating engine as template variables.

## Conclusion

In this chapter, we have introduced the concept of Use Cases. We explained how they define the behavior of your application
and why they should stay independent from how this behavior is exposed to the users.

In [the next chapter](02-use-cases-in-symfony.md) you will learn how to efficiently utilize the Use Cases in Symfony controllers.
