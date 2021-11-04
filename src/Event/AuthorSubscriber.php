<?php

namespace App\Event;

use App\Entity\Author;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class AuthorSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $authorEntity = $args->getEntity();
        
        if (!($authorEntity instanceof Author)) {
            return;
        }

        $authorEntity->setBooksAmount($authorEntity->getBooks()->count());
    }
}
