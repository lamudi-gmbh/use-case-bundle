<?php


namespace Lamudi\UseCaseBundle\Request\Processor;


use Lamudi\UseCaseBundle\Request\Request;

class ArrayInputProcessor implements InputProcessorInterface
{
    /**
     * Initializes a use case request by copying the data from the given array.
     * Available options:
     * - map - optional. This option allows to specify custom mapping from the fields found in the array
     *     to the fields in the use case request. Use an associative array with input array keys as keys
     *     and use case request field names as values.
     *
     * @param Request $request The use case request object to be initialized.
     * @param array $input The array of data.
     * @param array $options An array of options used to populate the request object.
     * @return Request returned for testability purposes
     */
    public function initializeRequest($request, $input, $options = [])
    {
        if (isset($options['map'])) {
            $map = array_flip($options['map']);
        } else {
            $map = [];
        }

        foreach ($request as $field => &$value) {
            if (isset($map[$field])) {
                $field = $map[$field];
            }

            if (isset($input[$field])) {
                $value = $input[$field];
            }
        }

        return $request;
    }
}
