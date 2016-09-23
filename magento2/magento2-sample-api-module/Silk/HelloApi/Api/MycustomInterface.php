<?php

namespace Silk\HelloApi\Api;

/**
 * Defines the service contract for simple functions.
 */
interface MycustomInterface {
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function name($name);
}

