<?php

use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Throwable;


class DataTransferObjectMapper
{

    public const CONVERT_SNAKE_TO_CAMEL = 1;
    public const CONVERT_CAMEL_TO_SNAKE = 2;

    public const CONVERT_NONE = 0;

    private array $errors;

    private ReflectionProperty $reflectionProperty;
    /**
     * @var mixed
     */
    private $class;


    /**
     * @param array $parameters
     * @param $className
     * @param int $convertType
     * @return DataTransferObjectMapper
     */
    public function mapping(array $parameters, $className, int $convertType = self::CONVERT_NONE): self
    {
        //error 초기화
        $this->errors = [];
        try {
            if (gettype($className) === 'string' && !class_exists($className)) {
                throw new Exception("not exists class => {$className}");
            }
            if (is_object($className)) {
                $this->class = $className;
            } else {
                $this->class = new $className();
            }
            $reflectionClass = new ReflectionClass($this->class);

            if ($convertType === self::CONVERT_NONE && !empty($reflectionClass->getDocComment())) {
                if (preg_match('/@namingConvention\s+([^\s]+)/', $reflectionClass->getDocComment(), $match)) {
                    [, $type] = $match;
                    switch (strtoupper(trim($type))) {
                        case "CAMEL" :
                            $convertType = self::CONVERT_SNAKE_TO_CAMEL;
                            break;
                        case "SNAKE" :
                            $convertType = self::CONVERT_CAMEL_TO_SNAKE;
                        default :
                    }
                }
            }

            $convertedParameters = [];
            foreach ($parameters as $key => $value) {
                if ($convertType === self::CONVERT_SNAKE_TO_CAMEL) {
                    $convertedParameters[$this->convertSnakeToCamel(strtolower($key))] = $value;
                } elseif ($convertType === self::CONVERT_CAMEL_TO_SNAKE) {
                    $convertedParameters[$this->convertCamelToSnake(strtolower($key))] = $value;
                } else {
                    $convertedParameters[$key] = $value;
                }
            }

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $this->reflectionProperty = $reflectionProperty;
                $property = $reflectionProperty->getName();
                $reflectionProperty->setAccessible(true);

                if (array_key_exists($property, $convertedParameters)) {
                    $this->setProperty($convertedParameters[$property], $convertType);
                }
                if (!$reflectionProperty->isInitialized($this->class)) {
                    $this->errors[] = get_class($this->class) . "->{$reflectionProperty->getName()} is not initialized";
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }


    private function convertSnakeToCamel(string $str)
    {
        $str = str_replace('_', '', ucwords($str, '_'));
        $str[0] = strtolower($str[0]);
        return $str;
    }

    private function convertCamelToSnake(string $str)
    {
        $pattern = '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!';
        preg_match_all($pattern, $str, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ?
                strtolower($match) :
                lcfirst($match);
        }
        return implode('_', $ret);
    }


    /**
     * @param $value
     * @param int $convertType
     * @throws Exception
     */
    private function setProperty($value, int $convertType = self::CONVERT_NONE)
    {
        try {

            //property 키로 $parameter값이 존재하면 타입캐스팅 하여 저장
            if (is_null($value)) {
                $this->setValue($value);
                return;
            }
            // 속성에 타입이 없으면
            if (!$this->reflectionProperty->hasType()) {
                $this->setValue($value);
                return;
            }

            $propertyType = $this->reflectionProperty->getType();
            if (!$propertyType instanceof ReflectionNamedType) {
                return;
            }

            switch ($propertyType->getName()) {
                case "string" :
                case "bool" :
                    $this->setValue($value);
                    break;
                case "int" :
                case "float" :
                    $this->setValue(str_replace(",", "", $value));
                    break;
                case "array" :
                    $match = null;
                    //property 주석에 @namespace가 있으면 클래스로 인스턴스화 하여 할당한다.
                    $propertyDocNamespace = '';
                    if (preg_match('/@namespace\s+([^\s]+)/', $this->reflectionProperty->getDocComment(), $match)) {
                        [, $type] = $match;
                        $propertyDocNamespace = strlen(trim($type)) > 0 ? trim($type) : '';
                    }
                    //@namespace 주석에 classPath가 적혀있지 않거나, 존재하지 않는 클래스면 value를 직접 할당함.
                    if (empty($propertyDocNamespace) || !class_exists($propertyDocNamespace)) {
                        $this->setValue($value);
                        break;
                    }
                    if (!is_iterable($value)) {
                        $this->errors[] = "{$this->reflectionProperty->getName()} value is not iterable";
                        break;
                    }
                    $this->setValue(array_map(function ($data) use ($propertyDocNamespace, $convertType) {
                        if (gettype($data) === "array") {
                            return $this->mapping($data, new $propertyDocNamespace(), $convertType);
                        } elseif (is_object($data)) {
                            $this->checkAllPropertyInit($data);
                            return $data;
                        } else {
                            $instanceClass = new $propertyDocNamespace($data);
                            $this->checkAllPropertyInit($instanceClass);
                            return $instanceClass;
                        }
                    }, $value));
                    break;
                default :
                    //dto property 타입이 객체이면,
                    $class = $propertyType->getName();
                    if (!class_exists($class)) {
                        //error 존재하지 않는 클래스
                        $this->errors[] = "{$class} is not exists";
                        break;
                    }
                    // value가 객체이면 그대로 set
                    if (is_object($value) && get_class($value) === $class) {
                        $this->setValue($value);
                    } elseif (is_array($value)) {
                        // value가 array이면 property 타입으로 인스턴스
                        $this->setValue($this->mapping($value, new $class(), $convertType));
                    } elseif (is_int($value) || is_float($value) || is_string($value) || is_bool($value)) {
                        // value 가 일반 기본값이면 객체 생성자 arg 로 생성 ex) value object
                        $instanceClass = new $class($value);
                        $this->checkAllPropertyInit($instanceClass);
                        $this->setValue($instanceClass);
                    } else {
                        //그 외의 경우는 에러로 취급한다.
                        $notDefinedType = gettype($value);
                        $this->errors[] = "{$this->reflectionProperty->getName()} set fail, value is not define type ({$notDefinedType}))";
                    }
            }
        } catch (Throwable $e) {
            $this->errors[] = get_class($this->class) . "->{$this->reflectionProperty->getName()} property error occurred. getMessage() => {$e->getMessage()}";
        }
    }

    private function setValue($value)
    {
        try {
            $this->reflectionProperty->setValue($this->class, $value);
        } catch (Throwable $e) {
            $this->errors[] = get_class($this->class) . "->{$this->reflectionProperty->getName()} set fail => {$e->getMessage()}";
        }
    }

    /**
     * @param $instancedClass
     * @return mixed
     * @throws Exception
     */
    private function checkAllPropertyInit($instancedClass)
    {
        foreach ((new ReflectionClass($instancedClass))->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            if (!$reflectionProperty->isInitialized($instancedClass)) {
                $this->errors[] = get_class($this->class) . "->{$reflectionProperty->getName()} is not initialized";
            }
        }
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}