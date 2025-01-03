<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 */

namespace Laminas\Server\Method;

use Laminas\Server\Method\Parameter;

use function array_key_exists;
use function count;
use function is_array;
use function is_numeric;
use function is_string;
use function method_exists;
use function ucfirst;

/**
 * Method prototype metadata
 */
class Prototype
{
    /** @var string Return type */
    protected $returnType = 'void';

    /** @var array Map parameter names to parameter index */
    protected $parameterNameMap = [];

    /** @var array Method parameters */
    protected $parameters = [];

    /**
     * Constructor
     *
     * @param  null|array $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set return value
     *
     * @param  string $returnType
     * @return Prototype
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * Retrieve return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Add a parameter
     *
     * @param  string|Parameter $parameter
     * @return Prototype
     */
    public function addParameter($parameter)
    {
        if ($parameter instanceof Parameter) {
            $this->parameters[]            = $parameter;
            $name                          = $parameter->getName();
            $this->parameterNameMap[$name] = count($this->parameters) - 1;
        } else {
            $parameter          = new Parameter([
                'type' => $parameter,
            ]);
            $this->parameters[] = $parameter;
        }
        return $this;
    }

    /**
     * Add parameters
     *
     * @return Prototype
     */
    public function addParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
        return $this;
    }

    /**
     * Set parameters
     *
     * @return Prototype
     */
    public function setParameters(array $parameters)
    {
        $this->parameters       = [];
        $this->parameterNameMap = [];
        $this->addParameters($parameters);
        return $this;
    }

    /**
     * Retrieve parameters as list of types
     *
     * @return array
     */
    public function getParameters()
    {
        $types = [];
        foreach ($this->parameters as $parameter) {
            $types[] = $parameter->getType();
        }
        return $types;
    }

    /**
     * Get parameter objects
     *
     * @return array
     */
    public function getParameterObjects()
    {
        return $this->parameters;
    }

    /**
     * Retrieve a single parameter by name or index
     *
     * @param  string|int $index
     * @return null|Parameter
     */
    public function getParameter($index)
    {
        if (! is_string($index) && ! is_numeric($index)) {
            return null;
        }
        if (array_key_exists($index, $this->parameterNameMap)) {
            $index = $this->parameterNameMap[$index];
        }
        if (array_key_exists($index, $this->parameters)) {
            return $this->parameters[$index];
        }
        return null;
    }

    /**
     * Set object state from array
     *
     * @return Prototype
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Serialize to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'returnType' => $this->getReturnType(),
            'parameters' => $this->getParameters(),
        ];
    }
}
