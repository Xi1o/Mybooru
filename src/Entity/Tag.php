<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagsRepository")
 * @UniqueEntity(fields="title", message="error.tag_already_exists")
 */
class Tag {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $title;

    /**
     * @ManyToMany(targetEntity="App\Entity\Image", mappedBy="tags")
     * @var
     */
    private $images;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getImages() {
        return $this->images;
    }

    /**
     * @param mixed $images
     */
    public function setImages($images): void {
        $this->images = $images;
    }

    public function __construct() {
        $this->images = new ArrayCollection();
    }
}
