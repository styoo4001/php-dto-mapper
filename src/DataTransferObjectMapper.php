<?php


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
            if ($convertType === self::CONVERT_NONE) {
                $convertType = $this->selectKeyConventionConvertType($reflectionClass);
            }
            $convertedParameters = $this->convertParameterWithConvertType($parameters, $convertType);
            $this->setIfClassHasOnlySingleProperty($reflectionClass, $parameters, $convertType);
            $this->setPropertyWithConvertedParameters($reflectionClass, $convertedParameters, $convertType);

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

    private function convertSnakeToCamel(string $str): string
    {
        $str = str_replace('_', '', ucwords($str, '_'));
        $str[0] = strtolower($str[0]);
        return (string)$str;
    }

    private function convertCamelToSnake(string $str): string
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
                    $this->setValue($this->escapeStringForNumberConverting($value));
                    break;
                case "array" :
                    $this->setValue($this->makeValueInArrayType($value, $convertType));
                    break;
                default :
                    //dto property 타입이 객체이면,
                    $class = $propertyType->getName();
                    if (!class_exists($class)) {
                        //error 존재하지 않는 클래스
                        $this->errors[] = "{$class} is not exists";
                        break;
                    }
                    $this->setValue($this->makeValueInObjectType($class, $value, $convertType));
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
     */
    private function checkAllPropertyInit($instancedClass)
    {
        try {
            foreach ((new ReflectionClass($instancedClass))->getProperties() as $reflectionProperty) {
                $reflectionProperty->setAccessible(true);
                if (!$reflectionProperty->isInitialized($instancedClass)) {
                    $this->errors[] = get_class($this->class) . "->{$reflectionProperty->getName()} is not initialized";
                }
            }
        } catch (ReflectionException $e) {
            $this->errors[] = get_class($this->class) . "->{$e->getMessage()}";
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

    private function escapeStringForNumberConverting($value)
    {
        return str_replace(",", "", $value);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return int
     */
    private function selectKeyConventionConvertType(ReflectionClass $reflectionClass): int
    {
        $convertType = self::CONVERT_NONE;
        if (!empty($reflectionClass->getDocComment() &&
            preg_match('/@namingConvention\s+([^\s]+)/', $reflectionClass->getDocComment(), $match))) {
            [, $type] = $match;
            switch (strtoupper(trim($type))) {
                case "CAMEL" :
                    $convertType = self::CONVERT_SNAKE_TO_CAMEL;
                    break;
                case "SNAKE" :
                    $convertType = self::CONVERT_CAMEL_TO_SNAKE;
                    break;
                default :
                    $convertType = self::CONVERT_NONE;
            }
        }
        return $convertType;
    }

    private function convertParameterWithConvertType(array $parameters, int $convertType): array
    {
        $convertedParameters = [];
        foreach ($parameters as $key => $value) {
            $key = trim($key);
            if ($convertType === self::CONVERT_SNAKE_TO_CAMEL) {
                $convertedParameters[$this->convertSnakeToCamel($key)] = $value;
            } elseif ($convertType === self::CONVERT_CAMEL_TO_SNAKE) {
                $convertedParameters[$this->convertCamelToSnake($key)] = $value;
            } else {
                $convertedParameters[$key] = $value;
            }
        }
        return $convertedParameters;

    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param array $parameters
     * @param int $convertType
     * @throws Exception
     */
    private function setIfClassHasOnlySingleProperty(ReflectionClass $reflectionClass, array $parameters, int $convertType): void
    {
        if (count($parameters) === 1 && count($reflectionClass->getProperties()) === 1 && array_keys($parameters)[0] === 0) {
            $this->reflectionProperty = $reflectionClass->getProperties()[0];
            $this->reflectionProperty->setAccessible(true);
            $this->setProperty($parameters[0], $convertType);
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param array $convertedParameters
     * @param int $convertType
     * @throws Exception
     */
    private function setPropertyWithConvertedParameters(ReflectionClass $reflectionClass, array $convertedParameters, int $convertType): void
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $this->reflectionProperty = $reflectionProperty;
            $property = $reflectionProperty->getName();
            $reflectionProperty->setAccessible(true);

            if ($reflectionProperty->hasType() && $this->reflectionProperty->getType()->allowsNull()) {
                $this->setProperty(null);
            }

            if (array_key_exists($property, $convertedParameters)) {
                $this->setProperty($convertedParameters[$property], $convertType);
            }
            if (!$reflectionProperty->isInitialized($this->class)) {
                $this->errors[] = get_class($this->class) . "->{$reflectionProperty->getName()} is not initialized";
            }
        }

    }

    /**
     * @param string $docCommentKey
     * @return string
     */
    private function getPropertyDocComment(string $docCommentKey): string
    {
        $match = null;
        $docComment = '';
        if (preg_match("/{$docCommentKey}\s+([^\s]+)/", $this->reflectionProperty->getDocComment(), $match)) {
            [, $type] = $match;
            $docComment = strlen(trim($type)) > 0 ? trim($type) : '';
        }
        return $docComment;
    }

    /**
     * @param $value
     * @param int $convertType
     * @return array
     */
    private function makeValueInArrayType($value, int $convertType): array
    {
        //property 주석에 @namespace가 있으면 클래스로 인스턴스화 하여 할당한다.
        $propertyDocNamespace = $this->getPropertyDocComment("@namespace");
        //@namespace 주석에 classPath가 적혀있지 않거나, 존재하지 않는 클래스면 value를 직접 할당함.
        if (empty($propertyDocNamespace)) {
            if (is_array($value)) {
                return $value;
            }
            return [$value];
        }
        if (!class_exists($propertyDocNamespace)) {
            $this->errors[] = "{$this->reflectionProperty->getName()} documentComment class is not exist";
            return [];
        }

        if (!is_iterable($value)) {
            return [$value];
        }

        return array_map(function ($data) use ($propertyDocNamespace, $convertType) {
            $reflectionClass = new ReflectionClass($propertyDocNamespace);

            if (is_array($data)) {
                if ($this->isNotUsingConstructor($reflectionClass)) {
                    return $this->recursiveMapping($data, new $propertyDocNamespace(), $convertType);
                }
                if ($this->constructorHasOnlySingleArgument($reflectionClass)) {
                    $instanceClass = new $propertyDocNamespace($data);
                    $this->checkAllPropertyInit($instanceClass);
                    return $instanceClass;
                }
            }
            if (is_object($data)) {
                $this->checkAllPropertyInit($data);
                return $data;
            }
            if ($this->isNotUsingConstructor($reflectionClass)) {
                return $this->recursiveMapping([$data], new $propertyDocNamespace(), $convertType);
            }

            if ($this->constructorHasOnlySingleArgument($reflectionClass)) {
                $reflectionParameter = $reflectionClass->getConstructor()->getParameters()[0];
                $instanceClass = new $propertyDocNamespace($this->forceCastingByDefaultType($reflectionParameter, $data));
                $this->checkAllPropertyInit($instanceClass);
                return $instanceClass;
            }
//            if
            return $data;
        }, $value);
    }

    /**
     * @param $class
     * @param $value
     * @param int $convertType
     * @return mixed|void
     * @throws Exception
     */
    private function makeValueInObjectType($class, $value, int $convertType)
    {
        // value가 객체이면 그대로 set
        if (is_object($value) && get_class($value) === $class) {
            return $value;
        }

        // value가 array이면 property 타입으로 인스턴스
        if (is_array($value)) {
            $reflectionClass = new ReflectionClass($class);
            if ($this->isNotUsingConstructor($reflectionClass)) {
                return $this->recursiveMapping($value, new $class(), $convertType);

            }
            if ($this->constructorHasOnlySingleArgument($reflectionClass)) {
                $reflectionParameter = $reflectionClass->getConstructor()->getParameters()[0];
                if (!$reflectionParameter->hasType()) {
                    $instanceClass = new $class($value);
                    $this->checkAllPropertyInit($instanceClass);
                    return $instanceClass;
                }
                $propertyType = $reflectionParameter->getType();
                if ($propertyType instanceof ReflectionNamedType && $propertyType->getName() === 'array') {
                    $instanceClass = new $class($value);
                    $this->checkAllPropertyInit($instanceClass);
                    return $instanceClass;
                }
            }

            if ($this->constructorHasManyArgument($reflectionClass)) {
                if ($this->isAssoc($value)) {
                    $args = [];
                    foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
                        if ($parameter->isDefaultValueAvailable()) {
                            if (array_key_exists($parameter->getName(), $value)) {
                                $args[] = $value[$parameter->getName()];
                            } else {
                                $args[] = $parameter->getDefaultValue();
                            }
                        } else {
                            $args[] = $value[$parameter->getName()] ?? null;
                        }
                    }

                } else {
                    $args = [];
                    foreach ($reflectionClass->getConstructor()->getParameters() as $index => $parameter) {
                        if ($parameter->isDefaultValueAvailable()) {
                            if (array_key_exists($index, $value)) {
                                $args[] = $value[$index];
                            } else {
                                $args[] = $parameter->getDefaultValue();
                            }
                        } else {
                            $args[] = $value[$index] ?? null;
                        }
                    }
                }

                switch ($reflectionClass->getConstructor()->getNumberOfParameters()) {
                    case 2:
                        $instanceClass = new $class($args[0], $args[1]);
                        break;
                    case 3:
                        $instanceClass = new $class($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4]);
                        break;
                    case 5:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4], $args[5]);
                        break;
                    case 6:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4], $args[5], $args[6]);
                        break;
                    case 7:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4], $args[5], $args[6], $args[7]);
                        break;
                    case 8:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
                        break;
                    case 9:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]);
                        break;
                    case 10:
                        $instanceClass = new $class($args[0], $args[1], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10]);
                        break;
                    default :
                        throw new Exception('Auto constructor argument value is only possible up to 10');
                }
                $this->checkAllPropertyInit($instanceClass);
                return $instanceClass;
            }
        }
        if ($this->isDefaultType($value)) {
            // value 가 일반 기본값이면 객체 생성자 arg 로 생성 ex) value object
            $reflectionClass = new ReflectionClass($class);
            if ($this->isNotUsingConstructor($reflectionClass)) {
                return $this->recursiveMapping([$value], new $class(), $convertType);
            }

            if ($this->constructorHasOnlySingleArgument($reflectionClass)) {
                $reflectionParameter = $reflectionClass->getConstructor()->getParameters()[0];
                $instanceClass = new $class($this->forceCastingByDefaultType($reflectionParameter, $value));
                $this->checkAllPropertyInit($instanceClass);
                return $instanceClass;
            }
        }
        $notDefinedType = gettype($value);
        $this->errors[] = "{$this->reflectionProperty->getName()} set fail, value is not define type ({$notDefinedType}))";
        return $value;
    }

    private function forceCastingByDefaultType(ReflectionParameter $reflectionParameter, $value)
    {
        if (!$reflectionParameter->hasType()) {
            return $value;
        }
        $propertyType = $reflectionParameter->getType();
        if (!$propertyType instanceof ReflectionNamedType) {
            return $value;
        }
        if ($propertyType->getName() === gettype($value)) {
            return $value;
        }
        switch ($propertyType->getName()) {
            case "string" :
                $value = (string)$value;
                break;
            case "bool" :
                $value = (bool)$value;
                break;
            case "int" :
                $value = (int)$this->escapeStringForNumberConverting($value);
                break;
            case "float" :
                $value = (float)$this->escapeStringForNumberConverting($value);
                break;
        }
        return $value;
    }

    /**
     * @param $value
     * @param $class
     * @param int $convertType
     * @return mixed
     */
    private function recursiveMapping($value, $class, int $convertType)
    {
        $mapper = new self();
        $mapper->mapping($value, $class, $convertType);
        if ($mapper->hasErrors()) {
            $this->errors = array_merge($this->errors, $mapper->getErrors());
        }
        return $mapper->getClass();
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    private function isNotUsingConstructor(ReflectionClass $reflectionClass): bool
    {
        return (is_null($reflectionClass->getConstructor()) || $reflectionClass->getConstructor()->getNumberOfParameters() === 0);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    private function constructorHasOnlySingleArgument(ReflectionClass $reflectionClass): bool
    {
        return (!is_null($reflectionClass->getConstructor()) && $reflectionClass->getConstructor()->getNumberOfParameters() === 1);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    private function constructorHasManyArgument(ReflectionClass $reflectionClass): bool
    {
        return (!is_null($reflectionClass->getConstructor()) && $reflectionClass->getConstructor()->getNumberOfParameters() > 1);
    }

    private function isDefaultType($value): bool
    {
        return is_int($value) || is_float($value) || is_string($value) || is_bool($value);
    }

    /**
     * @param array $arr
     * @return bool
     */
    private function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}