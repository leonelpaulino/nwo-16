<?php

namespace App\Model\Traits;

trait Errors
{
    protected $error = false;
    protected $errorMsg = '';
    protected $errorCode = null;
    /**
     * @return bool
     */
    public function success(): bool
    {
        return ($this->error) ? false : true;
    }
    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }
    /**
     * Shortcut for declaring an error occurred during the task.
     *
     * @param null $errorMsg
     * @param string $code
     *
     * @return $this
     */
    public function setError($errorMsg = null, $code = 'E001')
    {
        $this->error = true;
        if ($errorMsg) {
            $this->setErrorMsg($errorMsg);
        }
        if ($code) {
            $this->setErrorCode($code);
        }
        return $this;
    }
    /**
     * Returns the full error details.
     *
     * @return array
     */
    public function getError(): array
    {
        return [
            'error' => $this->error,
            'code' => $this->errorCode,
            'msg' => $this->errorMsg,
        ];
    }
    /**
     * @param mixed $errorMsg
     *
     * @return $this
     */
    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }
    /**
     * @param mixed $errorCode
     *
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }
    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}