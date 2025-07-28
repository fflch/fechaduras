<?php

namespace App\Exceptions;

use Exception;

class ConnectionFailureException extends Exception
{
    protected $message;
     /**

     * Report the exception.

     */
    public function report(): void
    {
        // ...
    }

    /**

     * Render the exception as an HTTP response.

     */
    public function render()
    {
        return view('errors.connection', [
            'ip' => $this->message,
        ]);
    }
}
