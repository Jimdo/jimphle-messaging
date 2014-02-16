<?php
namespace Jimphle\Messaging\Plugin\Authorization;

use Jimphle\DataStructure\Map;

interface Constraint
{
    public function validate(Map $value);

    public function getErrorMessage();
}