<?php

namespace App\Event;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BookSubscriber implements EventSubscriber
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
            Events::prePersist,
            Events::onFlush,
            // Events::postLoad
        ];
    }

    // public function postLoad(LifecycleEventArgs $args)
    // {
    //     $bookEntity = $args->getEntity();
        
    //     if (!($bookEntity instanceof Book)) {
    //         return;
    //     }

    //     $this->
    // }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            dd($col->getOwner()->getAuthors());
        }

        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            // dd($col);
            if ($col->getOwner() instanceof Book) {
                foreach ($col->getInsertDiff() as $author) {
                    $author->setBooksAmount($author->getBooksAmount() + 1);
                }

                foreach ($col->getDeleteDiff() as $author) {
                    $author->setBooksAmount($author->getBooksAmount() - 1);
                }
                $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($author)), $author);
            }

            if ($col->getOwner() instanceof Author) {
                $author = $col->getOwner();
                if (!empty($col->getInsertDiff())) {
                    $author->setBooksAmount($author->getBooksAmount() + count($col->getInsertDiff()));
                }
                if (!empty($col->getDeleteDiff())) {
                    $author->setBooksAmount($author->getBooksAmount() - count($col->getDeleteDiff()));
                }

                $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($author)), $author);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $bookEntity = $args->getEntity();
        
        if (!($bookEntity instanceof Book)) {
            return;
        }

        foreach ($bookEntity->getAuthors() as $author) {
            $author->setBooksAmount($author->getBooksAmount() + 1);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $bookEntity = $args->getEntity();
        
        if (!($bookEntity instanceof Book)) {
            return;
        }

        foreach ($bookEntity->getAuthors() as $author) {
            $author->setBooksAmount($author->getBooksAmount() - 1);
        }

        $cover = $bookEntity->getCover();
        $filesystem = new Filesystem();
        $filesystem->remove($this->params->get('bookcovers_directory').$cover);
    }
}
