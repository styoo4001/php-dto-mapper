<?php

namespace Styoo4001\PhpDtoMapper;

use JsonSerializable;
use ReflectionClass;

/**
 * Class CommandObject
 */
abstract class CommandObject implements JsonSerializable, RequestCommand
{
    protected array $commandObjectErrors = [];

    abstract protected function rule(): array;

    public function validation(CommandObjectValidator $validator)
    {
        $validator->setRules($this->rule());
        if (! $validator->run()) {
            $this->commandObjectErrors = array_merge($this->commandObjectErrors, $validator->getValidError());
        }
    }

    public function hasErrors(): bool
    {
        return ! empty($this->commandObjectErrors);
    }

    /**
     * @return false|mixed
     */
    public function getError(string $key)
    {
        return $this->commandObjectErrors[$key] ?: false;
    }

    public function getErrors(): array
    {
        return $this->commandObjectErrors;
    }

    public function setErrors(string ...$errors): void
    {
        foreach ($errors as $key => $error) {
            $this->commandObjectErrors[$key] = $error;
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4
     */
    public function jsonSerialize(): array
    {
        $reflectionChildClass = new ReflectionClass($this);
        $properties = $reflectionChildClass->getProperties();

        $json = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if ($property->isInitialized($this)) {
                $json[$property->getName()] = $property->getValue($this);
            }
        }
        unset($json['commandObjectErrors']);

        return $json;
    }
}
