<?php
namespace UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    const FIZ_TYPE = 'fiz';
    const URI_TYPE = 'uri';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="firstname", type="string", length=2048, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="lastname", type="string", length=2048, nullable=true)
     */
    private $lastname;

    /**
     * @var string
     */
    private $useterms;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="phone", type="string", length=2048, nullable=true)
     */
    private $phone;

    /**
     * @var string
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="inn", type="string", length=2048, nullable=true)
     */
    private $inn;

    /**
     * @var string
     * @Assert\Email()
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="dop_email", type="string", length=2048, nullable=true)
     */
    private $dopEmail;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length( max = 2048 )
     * @ORM\Column(name="type", type="string", length=2048, nullable=true)
     */
    private $type;

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


    public function __construct()
    {
        parent::__construct();
        $this->addRole('ROLE_USER');
        $this->type = self::FIZ_TYPE;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getUseterms()
    {
        return $this->useterms;
    }

    /**
     * @param string $useterms
     *
     * @return User
     */
    public function setUseterms($useterms)
    {
        $this->useterms = $useterms;

        return $this;
    }

    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
    public function getDopEmail()
    {
        return $this->dopEmail;
    }

    /**
     * @param string $dopEmail
     */
    public function setDopEmail($dopEmail)
    {
        $this->dopEmail = $dopEmail;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
}