<?php
namespace App\Model\Contracts;
/**
 * Interface Errors
 *
 * For consistence in our code, we implement this on any class
 * that may potentially throw an error.
 *
 * @package Helpers\Contracts
 */
interface Errors
{
    public function success(): bool;
    public function isError(): bool;
    public function getError(): array;
    public function setError($errorMsg = null, $code = 'E001');
    public function getErrorCode(): string;
    public function setErrorMsg($errorMsg);
}