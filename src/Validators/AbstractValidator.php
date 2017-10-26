<?php

namespace Wax\Shop\Validators;

use Wax\Shop\Exceptions\ValidationException;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Support\MessageBag;

abstract class AbstractValidator implements MessageProvider
{
    /* @var MessageBag */
    protected $messages;

    /**
     * Your validation logic should go here. Make sure you set up a MessageBag on $this->messages.
     *
     * @return bool
     */
    abstract public function passes() : bool;


    /**
     * Run the validator's rules against its data.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function validate()
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }
    }

    /**
     * Get the message container for the validator.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function messages()
    {
        if (! $this->messages) {
            $this->passes();
        }

        return $this->messages;
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails()
    {
        return ! $this->passes();
    }

    /**
     * Get the messages for the instance.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getMessageBag()
    {
        return $this->messages();
    }

    /**
     * An alternative more semantic shortcut to the message container.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function errors()
    {
        return $this->messages();
    }
}
