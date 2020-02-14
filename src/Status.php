<?php

namespace UKFast\HealthCheck;

class Status
{
    const PROBLEM = 'PROBLEM';

    const OKAY = 'OK';

    /** @var string */
    protected $status = null;

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $message = '';

    /**
     * Marks the status as a problem
     * 
     * @param string? $message Problem message
     * @return self
     */
    public function problem($message = '')
    {
        $this->status = Status::PROBLEM;
        $this->message = $message;
        return $this;
    }

    /**
     * Marks status as okay
     * 
     * @return self
     */
    public function okay()
    {
        $this->status = Status::OKAY;
        return $this;
    }

    /**
     * Returns if the status is a problem
     * 
     * @return bool
     */
    public function isProblem()
    {
        return $this->status == Status::PROBLEM;
    }

    /**
     * Returns if the status is okay
     * 
     * @return bool
     */
    public function isOkay()
    {
        return $this->status == Status::OKAY;
    }

    /**
     * Sets the context for the status
     * @param $context array
     * @return self
     */
    public function withContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Returns the status context, an array of arbitrary key/value
     * pairs to help with debugging. So long as the array can be
     * json encoded, it'll be outputted
     * 
     * @return array
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Sets the display name for the status, so when it's
     * being outputted in the healthcheck endpoint, this
     * is the key that the status and context will fall
     * under
     * 
     * @param string $name
     * @return self
     */
    public function withName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the display name
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    public function message()
    {
        return $this->message;
    }
}
