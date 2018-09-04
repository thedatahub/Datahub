<?php

namespace DataHub\UserBundle\Doctrine;

use DataHub\UserBundle\Document\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class HashPasswordListener implements EventSubscriber
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate'];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof User) {
            return;
        }

        $this->encodePassword($entity);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof User) {
            return;
        }

        $this->encodePassword($document);

        // necessary to force the update to see the change
        $documentManager = $args->getDocumentManager();
        $meta = $documentManager->getClassMetadata(get_class($document));
        $documentManager->getUnitOfWork()->recomputeSingleDocumentChangeSet($meta, $document);
    }
    
    private function encodePassword(User $entity)
    {
        if (!$entity->getPlainPassword()) {
            return;
        }
        
        $encoded = $this->passwordEncoder->encodePassword(
            $entity,
            $entity->getPlainPassword()
        );

        $entity->setPassword($encoded);
    }
}
