<?php

namespace App\Admin;

use App\Entity\Book;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class AuthorAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class)
        ->add(
            'books',
            EntityType::class,
            [
                'class' => Book::class,
                'multiple' => true,
                'expanded' => true
            ]
        );
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('name')
        ->add('booksAmount');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name');
    }
}
