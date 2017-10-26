<?php

namespace Wax\Shop\Exceptions;

use Illuminate\Contracts\Support\MessageProvider;

class ValidationException extends \Illuminate\Validation\ValidationException
{
    /**
     * The validator instance.
     *
     * @var MessageProvider
     */
    public $validator;

    /**
     * Create a new exception instance.
     *
     * @param  MessageProvider  $validator
     */
    public function __construct(MessageProvider $validator)
    {
        parent::__construct('The given data failed to pass validation.');

        $this->validator = $validator;
    }

    public function messages()
    {
        return $this->validator->getMessageBag();
    }
}
