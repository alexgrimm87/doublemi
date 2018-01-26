<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * IndividualBouquetRequest
 *
 * @ORM\Table(name="individual_bouquet_reqoust")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\IndividualBouquetRequestRepository")
 */
class IndividualBouquetRequest
{
    const CASH_PAYMENT = 'cash';
    const ONLINE_PAYMENT = 'online';

    const NEW_STATUS = 'new';
    const PAID_STATUS = 'paid';
    const IN_WORK_STATUS = 'in_work';
    const DONE_STATUS = 'done';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="negative_flower", type="text", nullable=true)
     */
    private $negativeFlower;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="occasion", type="string", length=2048, nullable=true)
     */
    private $occasion;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=2048, nullable=true)
     */
    private $target;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var array
     * @ORM\Column(name="params", type="array", nullable=true)
     */
    private $params;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="phone_sender", type="string", length=255, nullable=true)
     */
    private $phoneSender;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="email", type="string", length=512, nullable=true)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_date", type="datetime", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var string
     *
     * @ORM\Column(name="payment", type="string", length=2048, nullable=true)
     */
    private $payment;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=2048, nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_recipient", type="string", length=255, nullable=true)
     */
    private $phoneRecipient;


    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->payment = self::CASH_PAYMENT;
        $this->status = self::NEW_STATUS;
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return IndividualBouquetRequest
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
     * @return string
     */
    public function getNegativeFlower()
    {
        return $this->negativeFlower;
    }

    /**
     * @param string $negativeFlower
     */
    public function setNegativeFlower($negativeFlower)
    {
        $this->negativeFlower = $negativeFlower;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getOccasion()
    {
        return $this->occasion;
    }

    /**
     * @param string $occasion
     */
    public function setOccasion($occasion)
    {
        $this->occasion = $occasion;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function addParamElement($title, $markup=0){
        if(!$this->params)
            $this->params = [];

        $this->params[] = [
            'title'=>$title,
            'markup'=>$markup
        ];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getPhoneSender()
    {
        return $this->phoneSender;
    }

    /**
     * @param string $phone
     */
    public function setPhoneSender($phone)
    {
        $this->phoneSender = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return string
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param string $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getPhoneRecipient()
    {
        return $this->phoneRecipient;
    }

    /**
     * @param string $phoneRecipient
     */
    public function setPhoneRecipient($phoneRecipient)
    {
        $this->phoneRecipient = $phoneRecipient;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }
}

