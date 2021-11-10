<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

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
   
    public function createFromQueryParams($queryParams)
    {
        $this->id = (int) $queryParams['id'];
        $this->title = (string) $queryParams['title'];
        $this->description = (string) $queryParams['description'];
        $this->publishYear = (int) $queryParams['publishYear'];
        $this->author = (int) $queryParams['author'];

        return $this;
    }
}
