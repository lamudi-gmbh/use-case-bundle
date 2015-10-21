<?php

namespace Lamudi\UseCaseBundle\Factory;

use Lamudi\UseCaseBundle\Request\Request;

class RequestInitializer
{
    /**
     * @param Request $useCaseRequest
     * @param array $requestData
     */
    public function initialize($useCaseRequest, $requestData)
    {
        foreach ($useCaseRequest as $field => &$value) {
            if ($key = $this->findKey($field, $requestData)) {
                $value = $key;
            }
        }

        return $useCaseRequest;
    }

    /**
     * @param string $keySearch
     * @param array $array
     * @return bool
     */
    private function findKey($keySearch, $array)
    {
        foreach ($array as $key => $item) {
            if ($key == $keySearch) {
                return $item;
            }
            else {
                if (is_array($item) && $this->findKey($keySearch, $item)) {
                    return $item[$keySearch];
                }
            }
        }

        return false;
    }
}