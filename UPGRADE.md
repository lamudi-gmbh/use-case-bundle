UPGRADE
=======

## From 0.2 to 0.3

* The feature to configure multiple Input and Response Processors has been introduced, which changed the configuration
format for Processor options.

    The old format:
    
    ```
    @UseCase(
        "another_use_case_name",
        input={
            "type"="http",
            "priority"="GPC"
        },
        response={
            "type"="twig",
            "template"="MyBundle:default:index.html.twig"
        }
    )
    ```
    
    The new format:
    
    ```
    @UseCase(
        "another_use_case_name",
        input={
            "http"={"priority"="GPC"}
        },
        response={
            "twig"={"template"="MyBundle:default:index.html.twig"}
        }
    )
    ```

The YAML and array structure has been changed in the same way. See chapter (Use Case Contexts)[docs/03-use-case-contexts.md]
for details.

* `Lamudi\UseCaseBundle\Exception\UseCaseException` has been renamed to 
`Lamudi\UseCaseBundle\Exception\AlternativeCourseException`.
* The class names of all Processor except the default ones have been parametrized, allowing to use custom implementations
of bundles Processors without the need to register them with new aliases.