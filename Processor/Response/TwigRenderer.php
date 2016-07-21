<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Exception\AlternativeCourseException;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TwigRenderer implements ResponseProcessorInterface
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param EngineInterface      $templating
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EngineInterface $templating = null, FormFactoryInterface $formFactory)
    {
        $this->templating = $templating;
        $this->formFactory = $formFactory;
    }

    /**
     * Renders a Symfony HTTP response using Symfony Twig engine. The response is cast to array and provided as
     * template parameters.
     * Available options:
     * - template - required. The name of the template to be rendered.
     * - forms - optional. This option must be an associative array. The keys are names of template variable names
     *     that will contain the form views. The values can be either strings with form names or arrays with options:
     *         - name - The name of the form.
     *         - data_field - The name of the field in the Use Case Response that contains form data.
     *
     * @param object $response
     * @param array  $options
     *
     * @return HttpFoundation\Response
     * @throws \Exception
     */
    public function processResponse($response, $options = [])
    {
        if (!$this->templating) {
            throw new \Exception('The templating engine has not been provided.');
        }

        if (!isset($options['template'])) {
            throw new \InvalidArgumentException(sprintf('Missing required option "%s"', 'template'));
        }

        $templateParams = (array)$response;

        if (isset($options['forms'])) {
            foreach ($options['forms'] as $formVariable => $formConfig) {
                if (is_string($formConfig)) {
                    $formConfig = ['name' => $formConfig];
                }

                $form = $this->formFactory->create($formConfig['name']);

                if (isset($formConfig['data_field'])) {
                    $fieldName = $formConfig['data_field'];
                    $form->setData($response->$fieldName);
                }

                $templateParams[$formVariable] = $form->createView();
            }
        }

        return $this->templating->renderResponse($options['template'], $templateParams);
    }

    /**
     * If a Use Case Exception is thrown, it throws a Symfony NotFoundHttpException. Otherwise, the exception is rethrown.
     *
     * @param \Exception $exception
     * @param array      $options
     *
     * @return mixed
     * @throws \Exception
     */
    public function handleException(\Exception $exception, $options = [])
    {
        try {
            throw $exception;
        } catch (AlternativeCourseException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
