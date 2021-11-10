<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookFilterDto
{
    /**
     * @Assert\Type(
     *     type="integer",
     *     message="Id value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\PositiveOrZero
     */
    public $id;

    /**
     * @Assert\Type("string")
     */
    public $title;

    /**
     * @Assert\Type("string")
     */
    public $description;

    /**
     * @Assert\Type("integer")
     * @Assert\GreaterThan(-30000)
     * @Assert\LessThanOrEqual(2021)
     */
    public $publishYear;

    /**
     * @Assert\Type("integer")
     * @Assert\PositiveOrZero
     */
    public $author;

    public $errors;

    private ValidatorInterface $validator;
    
    private Request $request;

    public function __construct(ValidatorInterface $validator, RequestStack $requestStack)
    {
        $this->validator = $validator;
        $this->request = $requestStack->getCurrentRequest();
    }
   
    /**
     * Undocumented function
     *
     * @return BookFilterDto | Response
     */
    public function fromRequest()
    {
        $this->id = (int) $this->request->query->get('id');
        $this->title = (string) $this->request->query->get('title');
        $this->description = (string) $this->request->query->get('description');
        $this->publishYear = (int) $this->request->query->get('publishYear');
        $this->author = (int) $this->request->query->get('author');

        $errors = $this->validator->validate($this);
        if (count($errors) > 0) {
            $this->errors = $errors;
        }

        return $this;
    }
}
