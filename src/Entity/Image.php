<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Image {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png" })
     * @var mixed
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $thumbnail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $md5;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="images")
     * @var User
     */
    private $author;

    /**
     * @ManyToMany(targetEntity="App\Entity\Tag", cascade={"persist"}, inversedBy="images", fetch="EAGER")
     * @var ArrayCollection
     */
    private $tags;

    /**
     * @ORM\Column(type="datetime")
     * @var
     */
    private $created;

    /**
     * @return mixed
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getThumbnail(): string {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     */
    public function setThumbnail(string $thumbnail): void {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return string
     */
    public function getSize(): string {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize(string $size): void {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getMd5(): string {
        return $this->md5;
    }

    /**
     * @param string $md5
     */
    public function setMd5(string $md5): void {
        $this->md5 = $md5;
    }

    /**
     * @return User
     */
    public function getAuthor(): User {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): void {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags): void {
        $this->tags = $tags;
    }

    public function addTag(Tag $tag) {
        if($this->tags->contains($tag)) {
            return;
        }
        $this->tags[] = $tag;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        $this->created = new \DateTime("now");
    }

    public function __construct() {
        $this->tags = new ArrayCollection();
    }
}
