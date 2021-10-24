<?php

interface RequestCommand
{
    public function validation(CommandObjectValidator $validator);

    public function hasErrors(): bool;

    public function getErrors(): array;

    public function setErrors(string ...$errors): void;
}