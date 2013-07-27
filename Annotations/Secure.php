<?php

namespace Swis\Bundle\AnnotationExtraBundle\Annotations;

/**
 * @Annotation
 */
class Secure
{

    public $roles = array();

    public function __construct(array $data)
    {
        foreach (\explode(',', $data['roles']) as $role) {
            $this->roles[] = \trim($role);
        }
    }
}
