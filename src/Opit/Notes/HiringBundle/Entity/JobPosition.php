<?php

namespace Opit\Notes\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\Notes\CoreBundle\Entity\AbstractBase;
use Symfony\Component\Validator\ExecutionContextInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * JobPosition
 *
 * @ORM\Table(name="notes_job_position")
 * @ORM\Entity(repositoryClass="Opit\Notes\HiringBundle\Entity\JobPositionRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class JobPosition extends AbstractBase
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var text
     * @ORM\Column(name="job_position_id", type="string", length=11, nullable=true)
     */
    protected $jobPositionId;

    /**
     * @var \Text
     *
     * @ORM\Column(name="job_title", type="text")
     * @Assert\NotBlank(message="Job title can not be empty.")
     */
    protected $jobTitle;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_positions", type="integer")
     * @Assert\NotBlank(message="Number of positions can not be empty.")
     */
    protected $numberOfPositions;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\User", inversedBy="hmJobPositions")
     */
    protected $hiringManager;

    /**
     * @var \Text
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank(message="Description can not be empty.")
     */
    protected $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive;

    /**
     * @ORM\OneToMany(targetEntity="JPNotification", mappedBy="jobPosition", cascade={"remove"})
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="Applicant", mappedBy="jobPosition")
     **/
    protected $applicants;

    /**
     * @var text
     * @ORM\Column(name="external_token", type="string", nullable=true)
     */
    protected $externalToken;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->applicants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isActive = false;
    }

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
     * Get deleted at
     *
     * @return type
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deleted at
     *
     * @return type
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Set jobPositionId
     *
     * @param string $jobPositionId
     * @return JobPosition
     */
    public function setJobPositionId($jobPositionId)
    {
        $this->jobPositionId = $jobPositionId;

        return $this;
    }

    /**
     * Get jobPositionId
     *
     * @return string
     */
    public function getJobPositionId()
    {
        return $this->jobPositionId;
    }

    /**
     * Set jobTitle
     *
     * @param string $jobTitle
     * @return JobPosition
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Get jobTitle
     *
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * Set numberOfPositions
     *
     * @param integer $numberOfPositions
     * @return JobPosition
     */
    public function setNumberOfPositions($numberOfPositions)
    {
        $this->numberOfPositions = $numberOfPositions;

        return $this;
    }

    /**
     * Get numberOfPositions
     *
     * @return integer
     */
    public function getNumberOfPositions()
    {
        return $this->numberOfPositions;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return JobPosition
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return JobPosition
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set hiringManager
     *
     * @param \Opit\Notes\UserBundle\Entity\User $hiringManager
     * @return JobPosition
     */
    public function setHiringManager(\Opit\Notes\UserBundle\Entity\User $hiringManager = null)
    {
        $this->hiringManager = $hiringManager;

        return $this;
    }

    /**
     * Get hiringManager
     *
     * @return \Opit\Notes\UserBundle\Entity\User
     */
    public function getHiringManager()
    {
        return $this->hiringManager;
    }

    /**
     * Add notifications
     *
     * @param \Opit\Notes\HiringBundle\Entity\JPNotification $notifications
     * @return JobPosition
     */
    public function addNotification(\Opit\Notes\HiringBundle\Entity\JPNotification $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Opit\Notes\HiringBundle\Entity\JPNotification $notifications
     */
    public function removeNotification(\Opit\Notes\HiringBundle\Entity\JPNotification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add applicants
     *
     * @param \Opit\Notes\HiringBundle\Entity\Applicant $applicants
     * @return JobPosition
     */
    public function addApplicant(\Opit\Notes\HiringBundle\Entity\Applicant $applicants)
    {
        $this->applicants[] = $applicants;

        return $this;
    }

    /**
     * Remove applicants
     *
     * @param \Opit\Notes\HiringBundle\Entity\Applicant $applicants
     */
    public function removeApplicant(\Opit\Notes\HiringBundle\Entity\Applicant $applicants)
    {
        $this->applicants->removeElement($applicants);
    }

    /**
     * Get applicants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApplicants()
    {
        return $this->applicants;
    }

    /**
     * Get external token
     *
     * @return type
     */
    public function getExternalToken()
    {
        return $this->externalToken;
    }

    /**
     * Set external token
     *
     * @param type $externalToken
     * @return \Opit\Notes\HiringBundle\Entity\JobPosition
     */
    public function setExternalToken($externalToken)
    {
        $this->externalToken = $externalToken;

        return $this;
    }

    /**
     * Validate if a job position's no. of positions is bigger then 0.
     *
     * @Assert\Callback
     */
    public function validatePastLeaveDate(ExecutionContextInterface $context)
    {
        if ($this->getNumberOfPositions() <= 0) {
            $context->addViolationAt(
                'numberOfPositions',
                sprintf('Number of positions can not be smaller equal to 0.')
            );
        }
    }

    /**
     * Upload CV for applicant and remove old CV if there was one
     *
     * @ORM\PostPersist()
     */
    public function setJpExternalToken(LifecycleEventArgs $eventArgs)
    {

        $entityManager = $eventArgs->getEntityManager();
        if (null === $this->getCvFile()) {
            return;
        }

        if (null !== $this->getId()){
            unlink($this->getUploadRootDir(). '/' . $this->getCV());
        }

        $now = new \DateTime();
        $originalCVFileName = explode('.', $this->getCvFile()->getClientOriginalName());
        $originalCVFileName[count($originalCVFileName) - 2] = $originalCVFileName[count($originalCVFileName) - 2] . '_' . $now->getTimestamp();
        $originalCVFileName = implode('.', $originalCVFileName);

        $this->getCvFile()->move(
            $this->getUploadRootDir(),
            $originalCVFileName
        );

        $this->cv = $originalCVFileName;
        $this->cvFile = null;
    }
}
