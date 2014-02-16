<?php
namespace Jimphle\Messaging\Plugin\Validation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class MessagePropertyAnnotation
{
    public $prop;
    public $constraint;

    public function __construct($options = null)
    {
        if (isset($options['value'])) {
            $this->prop = $options['value'][0];
            $this->constraint = $options['value'][1];
        } else {
            $this->prop = $options['prop'];
            $this->constraint = $options['constraint'];
        }
    }
}
