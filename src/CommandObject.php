<?php

/**
 *
 * Class CommandObject
 */
abstract class CommandObject implements RequestCommand, JsonSerializable
{
    protected array $commandObjectErrors = [];
    abstract protected function rule(): array;

    /**
     * @param CommandObjectValidator $validator
     */
    public function validation(CommandObjectValidator $validator)
    {
        $validator->setRules($this->rule());
        if (!$validator->run()) {
            $this->commandObjectErrors = array_merge($this->commandObjectErrors, $validator->getValidError());
        }
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return !empty($this->commandObjectErrors);
    }

    /**
     * @param string $key
     * @return false|mixed
     */
    public function getError(string $key)
    {
        return $this->commandObjectErrors[$key] ?: false;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->commandObjectErrors;
    }

    /**
     * @param string ...$errors
     */
    public function setErrors(string ...$errors): void
    {
        foreach ($errors as $key => $error) {
            $this->commandObjectErrors[$key] = $error;
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
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