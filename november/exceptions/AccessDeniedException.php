<?php
class AccessDeniedException extends Exception
{
    public function __construct()
    {
        parent::__construct("You do not have permission to that item");
    }
}
?>