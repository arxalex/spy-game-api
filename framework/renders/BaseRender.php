<?php

namespace framework\renders;

class BaseRender
{
    private array $params;

    /**
     * Create object of block
     */
    public function __construct()
    {
        $this->params = array();
    }

    /**
     * Declare default params keys
     */
    public function defaultParamsKeys()
    {
        $result = [];
        foreach ($this->params as $key => $value) {
            array_push($result, $key);
        }
        return $result;
    }

    /**
     * Declare default params values
     */
    public function defaultParams()
    {
        $result = [];
        foreach ($this->params as $key => $value) {
            $result[$key] = null;
        }
        return $result;
    }

    /**
     * Add params to block
     */
    public function addParams(array $params)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $this->params[$key] = array_merge(is_array($this->getParam($key)) ? $this->getParam($key) : [], $value);
            } else {
                $this->params[$key] = $value;
            }
        }
    }

    /**
     * Return param by $key
     */
    public function getParam($key)
    {
        return array_key_exists($key, $this->params) ? $this->params[$key] : null;
    }

    /**
     * Return externalParams
     */
    public function getExternalParams()
    {
        return [];
    }

    /**
     * Build block
     */
    public function build()
    {
        return "<div>Unknown</div>";
    }

    /**
     * Return rendered block
     */
    public function render($externalParams)
    {
        $defaultParams = $this->defaultParams();
        $customParams = $this->params;

        $this->addParams($defaultParams);

        $externalParamsToAdd = [];
        foreach ($defaultParams as $key => $value) {
            if ($externalParams !== null && array_key_exists($key, $externalParams) && $externalParams[$key] != null) {
                $externalParamsToAdd[$key] = $externalParams[$key];
            }
        }

        $this->addParams($externalParamsToAdd);
        $this->addParams($customParams);
        echo $this->build();
    }
}
