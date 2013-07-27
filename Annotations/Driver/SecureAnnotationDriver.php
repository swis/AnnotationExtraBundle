<?php

namespace Swis\Bundle\AnnotationExtraBundle\Annotations\Driver;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Swis\Bundle\AnnotationExtraBundle\Annotations;

/**
 * @DI\Service
 */
class SecureAnnotationDriver
{

    private $reader;
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "reader" = @DI\Inject("annotation_reader"),
     *     "securityContext" = @DI\Inject("security.context")
     * })
     */
    public function __construct($reader, $securityContext)
    {
        $this->reader = $reader;
        $this->securityContext = $securityContext;
    }

    /**
     * @DI\Observe("kernel.controller", priority = 120)
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!\is_array($controller = $event->getController())) {
            return; // @codeCoverageIgnore
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Annotations\Secure) {
                $accessGranted = false;

                foreach ($configuration->roles as $role) {
                    if ($this->securityContext->isGranted($role)) {
                        $accessGranted = true;
                        break;
                    }
                }

                if (!$accessGranted) {
                    throw new AccessDeniedException('Token does not have the required roles.');
                }
            }
        }
    }
}
