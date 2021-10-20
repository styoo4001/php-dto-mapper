<?php

interface CommandObjectValidator
{
    public function getValidator();

    public function setRules(array $rules): void;

    public function setData(array $data): void;

    public function run(): bool;

    public function getValidatedData(): array;

    public function getValidError(): array;
}