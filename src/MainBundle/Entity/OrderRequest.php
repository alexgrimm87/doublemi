<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * OrderRequest
 *
 * @ORM\Table(name="order_request")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\OrderRequestRepository")
 */
class OrderRequest
{
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_PAYED = 3;
    const STATUS_DELIVER = 4;
    const STATUS_CLOSE = 5;
    const STATUS_CANCEL = 6;

    const DELIVERY_SIMPLE = 1;
    const DELIVERY_SELF_LIFT = 2;

    public static $status_list = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_IN_PROGRESS => 'В процессе',
        self::STATUS_PAYED => 'Оплачен',
        self::STATUS_CLOSE => 'Выполнен',
        self::STATUS_CANCEL => 'Отменен'
    ];

    public static $delivery_list = [
        self::DELIVERY_SIMPLE => 'Доставка',
        self::DELIVERY_SELF_LIFT => 'Самовывоз'
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean
     * @ORM\Column(name="one_click_order", type="boolean", nullable=true)
     */
    private $oneClickOrder;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="delivery", type="string", length=255, nullable=true)
     */
    private $delivery;

    /**
     * @var CatalogPayment
     * @ORM\ManyToOne(targetEntity="\MainBundle\Entity\CatalogPayment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="catalog_payment", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $catalogPayment;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=512, nullable=true)
     */
    private $email;

    /**
     * @var boolean
     * @ORM\Column(name="self_receive", type="boolean", nullable=true)
     */
    private $selfReceive;

    /**
     * @var boolean
     * @ORM\Column(name="incognito", type="boolean", nullable=true)
     */
    private $incognito;

    /**
     * @var boolean
     * @ORM\Column(name="free_card", type="boolean", nullable=true)
     */
    private $freeCard;

    /**
     * @var string
     *
     * @ORM\Column(name="card_text", type="text", nullable=true)
     */
    private $cardText;

    /**
     * @var string
     *
     * @ORM\Column(name="card_sign", type="text", nullable=true)
     */
    private $cardSign;

    /**
     * @var boolean
     * @ORM\Column(name="call_for_address", type="boolean", nullable=true)
     */
    private $callForAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_date", type="datetime", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_city", type="string", length=512, nullable=true)
     */
    private $deliveryCity;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_street", type="string", length=512, nullable=true)
     */
    private $deliveryStreet;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \UserBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $user;

    /**
     * @var array
     * @Assert\NotBlank()
     * @ORM\Column(name="objects", type="array", nullable=true)
     */
    private $objects;


    /**
     * @var int
     * @ORM\Column(name="delivery_price", type="integer", nullable=true)
     */
    private $deliveryPrice;

    /**
     * @var int
     * @Assert\NotBlank()
     * @ORM\Column(name="summ", type="integer")
     */
    private $summ;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_info", type="text", nullable=true)
     */
    private $paymentInfo;


    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="inn", type="string", length=2048, nullable=true)
     */
    private $inn;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="org_name", type="text", nullable=true)
     */
    private $orgName;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="uri_address", type="text", nullable=true)
     */
    private $uriAddress;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="fact_address", type="text",  nullable=true)
     */
    private $factAddress;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="uri_phone", type="text", nullable=true)
     */
    private $uriPhone;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="bank_name", type="text", nullable=true)
     */
    private $bankName;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="bank_checking_index", type="text", nullable=true)
     */
    private $bankCheckingIndex;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="bank_correspondent_index", type="text",  nullable=true)
     */
    private $bankCorrespondentIndex;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="bank_index", type="text", nullable=true)
     */
    private $bankIndex;

    /**
     * @var string
     *
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="delivery_name", type="text", nullable=true)
     */
    private $deliveryName;


    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="delivery_phone", type="text", nullable=true)
     */
    private $deliveryPhone;

    /**
     * OrderRequest constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->deliveryPrice = 0;
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
     * @return OrderRequest
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
     * Set status
     *
     * @param string $status
     *
     * @return OrderRequest
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param string $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return CatalogPayment
     */
    public function getCatalogPayment()
    {
        return $this->catalogPayment;
    }

    /**
     * @param CatalogPayment $catalogPayment
     */
    public function setCatalogPayment($catalogPayment)
    {
        $this->catalogPayment = $catalogPayment;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return OrderRequest
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return OrderRequest
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }


    /**
     * Set email
     *
     * @param string $email
     *
     * @return OrderRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function isSelfReceive()
    {
        return $this->selfReceive;
    }

    /**
     * @param boolean $selfReceive
     */
    public function setSelfReceive($selfReceive)
    {
        $this->selfReceive = $selfReceive;
    }

    /**
     * @return boolean
     */
    public function isIncognito()
    {
        return $this->incognito;
    }

    /**
     * @param boolean $incognito
     */
    public function setIncognito($incognito)
    {
        $this->incognito = $incognito;
    }

    /**
     * @return boolean
     */
    public function isFreeCard()
    {
        return $this->freeCard;
    }

    /**
     * @param boolean $freeCard
     */
    public function setFreeCard($freeCard)
    {
        $this->freeCard = $freeCard;
    }

    /**
     * @return string
     */
    public function getCardText()
    {
        return $this->cardText;
    }

    /**
     * @param string $cardText
     */
    public function setCardText($cardText)
    {
        $this->cardText = $cardText;
    }

    /**
     * @return string
     */
    public function getCardSign()
    {
        return $this->cardSign;
    }

    /**
     * @param string $cardSign
     */
    public function setCardSign($cardSign)
    {
        $this->cardSign = $cardSign;
    }

    /**
     * @return boolean
     */
    public function isCallForAddress()
    {
        return $this->callForAddress;
    }

    /**
     * @param boolean $callForAddress
     */
    public function setCallForAddress($callForAddress)
    {
        $this->callForAddress = $callForAddress;
    }

    /**
     * @return mixed
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @param mixed $deliveryDate
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return string
     */
    public function getDeliveryCity()
    {
        return $this->deliveryCity;
    }

    /**
     * @param string $deliveryCity
     */
    public function setDeliveryCity($deliveryCity)
    {
        $this->deliveryCity = $deliveryCity;
    }

    /**
     * @return string
     */
    public function getDeliveryStreet()
    {
        return $this->deliveryStreet;
    }

    /**
     * @param string $deliveryStreet
     */
    public function setDeliveryStreet($deliveryStreet)
    {
        $this->deliveryStreet = $deliveryStreet;
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
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \UserBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param array $objects
     */
    public function setObjects($objects)
    {
        $this->objects = $objects;
    }

    /**
     * @return int
     */
    public function getSumm()
    {
        return $this->summ;
    }

    /**
     * @param int $summ
     */
    public function setSumm($summ)
    {
        $this->summ = $summ;
    }

    /**
     * @return boolean
     */
    public function isOneClickOrder()
    {
        return $this->oneClickOrder;
    }

    /**
     * @param boolean $oneClickOrder
     */
    public function setOneClickOrder($oneClickOrder)
    {
        $this->oneClickOrder = $oneClickOrder;
    }

    /**
     * @return int
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * @param int $deliveryPrice
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;
    }

    /**
     * @return string
     */
    public function getPaymentInfo()
    {
        return $this->paymentInfo;
    }

    /**
     * @param string $paymentInfo
     */
    public function setPaymentInfo($paymentInfo)
    {
        $this->paymentInfo = $paymentInfo;
    }

    /**
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param string $inn
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
    }

    /**
     * @return string
     */
    public function getOrgName()
    {
        return $this->orgName;
    }

    /**
     * @param string $orgName
     */
    public function setOrgName($orgName)
    {
        $this->orgName = $orgName;
    }

    /**
     * @return string
     */
    public function getUriAddress()
    {
        return $this->uriAddress;
    }

    /**
     * @param string $uriAddress
     */
    public function setUriAddress($uriAddress)
    {
        $this->uriAddress = $uriAddress;
    }

    /**
     * @return string
     */
    public function getFactAddress()
    {
        return $this->factAddress;
    }

    /**
     * @param string $factAddress
     */
    public function setFactAddress($factAddress)
    {
        $this->factAddress = $factAddress;
    }

    /**
     * @return string
     */
    public function getUriPhone()
    {
        return $this->uriPhone;
    }

    /**
     * @param string $uriPhone
     */
    public function setUriPhone($uriPhone)
    {
        $this->uriPhone = $uriPhone;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBankCheckingIndex()
    {
        return $this->bankCheckingIndex;
    }

    /**
     * @param string $bankCheckingIndex
     */
    public function setBankCheckingIndex($bankCheckingIndex)
    {
        $this->bankCheckingIndex = $bankCheckingIndex;
    }

    /**
     * @return string
     */
    public function getBankCorrespondentIndex()
    {
        return $this->bankCorrespondentIndex;
    }

    /**
     * @param string $bankCorrespondentIndex
     */
    public function setBankCorrespondentIndex($bankCorrespondentIndex)
    {
        $this->bankCorrespondentIndex = $bankCorrespondentIndex;
    }

    /**
     * @return string
     */
    public function getBankIndex()
    {
        return $this->bankIndex;
    }

    /**
     * @param string $bankIndex
     */
    public function setBankIndex($bankIndex)
    {
        $this->bankIndex = $bankIndex;
    }

    /**
     * @return string
     */
    public function getDeliveryName()
    {
        return $this->deliveryName;
    }

    /**
     * @param string $deliveryName
     */
    public function setDeliveryName($deliveryName)
    {
        $this->deliveryName = $deliveryName;
    }

    /**
     * @return string
     */
    public function getDeliveryPhone()
    {
        return $this->deliveryPhone;
    }

    /**
     * @param string $deliveryPhone
     */
    public function setDeliveryPhone($deliveryPhone)
    {
        $this->deliveryPhone = $deliveryPhone;
    }


    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if($this->isOneClickOrder()){

        } else {
            if(!$this->getEmail()){
                $context->buildViolation('Это поле не должно быть пустым.')
                    ->atPath('email')
                    ->addViolation()
                ;
            }
        }
    }
}