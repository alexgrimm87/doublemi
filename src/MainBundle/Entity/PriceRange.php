<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * PriceRange
 *
 * @ORM\Table(name="price_range")
 * @UniqueEntity("code")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\PriceRangeRepository")
 */
class PriceRange extends Inheritance
{
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
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^[\w\d_]*$/",
     *     match=true,
     * )
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var int
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Не может принимать отрицательное значение"
     * )
     * @ORM\Column(name="min_price", type="integer", nullable=true)
     */
    private $minPrice;

    /**
     * @var int
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Не может принимать значение меньше за {{ limit }}"
     * )
     * @ORM\Column(name="max_price", type="integer", nullable=true)
     */
    private $maxPrice;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return PriceRange
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return PriceRange
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
     * Set minPrice
     *
     * @param integer $price
     * @return PriceRange
     */
    public function setMinPrice($price)
    {
        $this->minPrice = $price;

        return $this;
    }

    /**
     * Get minPrice
     *
     * @return integer
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * Set maxPrice
     *
     * @param integer $price
     * @return PriceRange
     */
    public function setMaxPrice($price)
    {
        $this->maxPrice = $price;

        return $this;
    }

    /**
     * Get maxPrice
     *
     * @return integer
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->maxPrice && $this->maxPrice < $this->minPrice) {
            $context->buildViolation('Недопустимое значение')
                ->atPath('maxPrice')
                ->addViolation();
        }
    }
}
