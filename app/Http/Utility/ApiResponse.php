<?php
namespace App\Http\Utility;

class ApiResponse {
    private $message;
    private $code;
    private $content;

    public function __construct($message, $code, $content=[]) {
        $this->message = $message;
        $this->code = $code;
        $this->content = $content;
    }

    public function message() {
        return $this->message;
    }
    public function setMessage($newMessage) {
        $message = $newMessage;
    }

    public function code() {
        return $this->code;
    }

    public function setCode($newCode) {
        $this->code = $newCode;
    }

    public function content() {
        return $this->content;
    }

    public function setContent($newContent) {
        $this->ontent = $newContent;
    }
    
    public function toArray() {
        return ['message' => $this->message(), 'code' => $this->code(), 'content' => $this->content()];
    }
}