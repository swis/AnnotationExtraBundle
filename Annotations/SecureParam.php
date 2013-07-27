<?php

namespace Swis\Bundle\AnnotationExtraBundle\Annotations;

/**
 * @Annotation
 */
class SecureParam
{

    public $name = null;
    public $permissions = array();

    public function __construct(array $data)
    {
        $this->name = \trim($data['name']);
        foreach (\explode(',', $data['permissions']) as $role) {
            $this->permissions[] = \trim($role);
        }
    }
}
