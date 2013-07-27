<?php

namespace Swis\Bundle\AnnotationsExtraBundle\Annotations\Driver;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Swis\Bundle\AnnotationsExtraBundle\Annotations;

/**
 * @DI\Service
 */
class SecureParamAnnotationDriver
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
     * @DI\Observe("kernel.controller", priority = -1)
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
            if ($configuration instanceof Annotations\SecureParam) {
                foreach ($configuration->permissions as $permission) {
                    $object = $event->getRequest()->attributes->get($configuration->name);
                    if (!$this->securityContext->isGranted($permission, $object)) {
                        throw new AccessDeniedException();
                    }
                }
            }
        }
    }
}
