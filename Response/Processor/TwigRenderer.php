<?php

namespace Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Response\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Processes the successful outcome of a use case execution. Returns any object that
     * satisfies the environment in which the use case is executed.
     *
     * @param Response $response
     * @param array $options
     * @return mixed
     */
    public function processResponse($response, $options = array())
    {
        if (!isset($options['template'])) {
            throw new \InvalidArgumentException(sprintf('Missing required option "%s"', 'template'));
        }

        $templateParams = (array)$response;

        if (isset($options['forms']) && $this->formFactory) {
            foreach ($options['forms'] as $formVariable => $formConfig) {
                if (is_string($formConfig)) {
                    $formConfig = array('name' => $formConfig);
                }

                $form = $this->formFactory->create($formConfig['name']);

                if (isset($formConfig['data_field'])) {
                    $fieldName = $formConfig['data_field'];
                    $form->setData($response->$fieldName);
                    unset($templateParams[$fieldName]);
                }

                $templateParams[$formVariable] = $form->createView();
            }
        }

        return $this->templating->renderResponse($options['template'], $templateParams);
    }

    /**
     * When an exception is thrown during use case execution, this method is invoked
     *
     * @param \Exception $exception
     * @param array $options
     * @return mixed
     */
    public function handleException($exception, $options = array())
    {
        try {
            throw $exception;
        } catch (UseCaseException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
    }
}