<?php
namespace Jimphle\Messaging\Plugin\Authorization;

class ConstraintCollection implements \Jimphle\Messaging\Plugin\Authorization\Constraint
{
    /**
     * @var \Jimphle\Messaging\Plugin\Authorization\Constraint[]
     */
    private $constraints;
    private $lastErrorMessage;

    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function validate(\Jimphle\DataStructure\Map $value)
    {
        $this->lastErrorMessage = null;
        foreach ($this->constraints as $constraint) {
            if (!$constraint->validate($value)) {
                $this->lastErrorMessage = $constraint->getErrorMessage();
                return false;
            }
        }
        return true;
    }

    public function getErrorMessage()
    {
        return $this->lastErrorMessage;
    }
}
