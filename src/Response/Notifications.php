<?php

namespace IDCT\Wykop\Response;

use IDCT\Wykop\Entity\Notification;
use Iterator;

/**
 * Notifications iterator.
 *
 * Extends GenericResponse therefore allows access to raw data and prev / next pages handles.
 */
class Notifications extends GenericResponse implements Iterator
{
    public function rewind()
    {
        return reset($this->data);
    }
    public function current()
    {
        return new Notification(current($this->data));
    }
    public function key()
    {
        return key($this->data);
    }
    public function next()
    {
        return next($this->data);
    }
    public function valid()
    {
        return key($this->data) !== null;
    }
}
