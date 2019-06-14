<?php

namespace Notion\Records;

class User extends Record
{
    public function toString()
    {
        return sprintf('%s %s', $this->get('family_name'), $this->get('given_name'));
    }
}
