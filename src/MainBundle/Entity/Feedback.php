<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Feedback
 *
 * @ORM\Table(name="feedback")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\FeedbackRepository")
 */
class Feedback extends Inheritance
{
    const FILE_FOLDER= 'feedback_images';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=2048, nullable=true)
     */
    private $title;

    /**
     * @var int
     *
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      minMessage = "Не может принимать значение меньше за {{ limit }}",
     *      maxMessage = "Не может принимать значение больше за {{ limit }}"
     * )
     * @ORM\Column(name="score", type="integer", nullable=true)
     */
    private $score;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=2048, nullable=true)
     */
    private $picture;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @Assert\File(maxSize="10000000")
     */
    private $pictureFile;

    /**
     * @var \UserBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $user;

    /**
     * @var boolean
     * @ORM\Column(name="on_subscribe", type="boolean", nullable=true)
     */
    private $showOnSubscribePage;

    public function __construct()
    {
        parent::__construct();
        $this->date = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Feedback
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set score
     *
     * @param integer $score
     *
     * @return Feedback
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Feedback
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return Feedback
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Feedback
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * @param mixed $pictureFile
     * @return Feedback
     */
    public function setPictureFile($pictureFile)
    {
        $this->pictureFile = $pictureFile;
        return $this;
    }


    /**
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \UserBundle\Entity\User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowOnSubscribePage()
    {
        return $this->showOnSubscribePage;
    }

    /**
     * @param boolean $showOnSubscribePage
     */
    public function setShowOnSubscribePage($showOnSubscribePage)
    {
        $this->showOnSubscribePage = $showOnSubscribePage;
    }
}

