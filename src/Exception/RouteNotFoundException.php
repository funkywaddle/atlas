<?php

namespace Atlas\Exception;

/**
 * Exception thrown when a requested route is not found.
 *
 * This may occur during matching if no route corresponds to the request,
 * or during URL generation if the requested route name is not registered.
 *
 * @extends \RuntimeException
 */
class RouteNotFoundException extends \RuntimeException
{
}
