<?php

namespace App\Admin;

use App\Entity\Author;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class BookAdmin extends AbstractAdmin
{
    private ContainerInterface $container;

    public function __construct($code, $class, $baseControllerName, $container)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->container = $container;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
        ->add('title', TextType::class)
        ->add('description', TextareaType::class)
        ->add('cover', FileType::class, $this->configureFileField())
        ->add('publishYear', IntegerType::class)
        ->add(
            'authors',
            EntityType::class,
            [
                'class' => Author::class,
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false
            ]
        );
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('id')
        ->add('title')
        ->add('description')
        ->add('publishYear')
        ->add('authors');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id')
        ->add('title', FieldDescriptionInterface::TYPE_STRING, ['editable' => true])
        ->add('description', FieldDescriptionInterface::TYPE_TEXTAREA, ['editable' => true])
        ->add('cover', null, ['template' => 'admin/book/book_cover.html.twig'])
        ->add('publishYear', FieldDescriptionInterface::TYPE_STRING, ['editable' => true])
        ->add('authors', FieldDescriptionInterface::TYPE_MANY_TO_MANY, ['editable' => true]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id')
        ->add('title')
        ->add('description')
        ->add('cover')
        ->add('publishYear')
        ->add('authors');
    }

    public function prePersist(object $book): void
    {
        $this->manageImageUpload($book);
    }

    public function preUpdate(object $book): void
    {
        if ($book->getCover()) {
            $this->manageImageUpload($book);
        }
    }

    private function configureFileField() : array
    {
        $book = $this->getSubject();
        $fileFormOptions = [
            'label' => 'cover (image file)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid jpeg or png image',
                ])
            ],
        ];

        if ($book && ($webPath = $book->getCover())) {
            $request = $this->getRequest();
            $fullPath = $request->getBasePath().'/uploads/covers/'.$webPath;
            $fileFormOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview"/>';
            $fileFormOptions['help_html'] = true;
            $fileFormOptions['data_class'] = null;
        }

        return $fileFormOptions;
    }

    private function manageImageUpload($book) : void
    {
        $fileUploadService = $this->container->get('App\Service\FileUploadService');
        $book->setCover($fileUploadService->uploadAndReturnPath($this->getForm()->get('cover')->getData()));
    }
}
