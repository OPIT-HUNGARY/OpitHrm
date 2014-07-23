<?php

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * LeaveRequest
 *
 * @ORM\Table(name="opithrm_leaves")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\LeaveBundle\Entity\LeaveRepository")
 */
class Leave
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @Assert\NotBlank(message="Start date can not be empty.")
     * @Assert\Type("\DateTime")
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date")
     * @Assert\NotBlank(message="End date can not be empty.")
     * @Assert\Type("\DateTime")
     */
    protected $endDate;

    /**
     * @var \Text
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="LeaveCategory", inversedBy="requests")
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="LeaveRequest", inversedBy="leaves")
     */
    protected $leaveRequest;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_days", type="integer")
     */
    protected $numberOfDays;
    
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Leave
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Leave
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set description
     *
     * @param \Text $description
     * @return Leave
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return \Text $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set category
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveCategory $category
     * @return Leave
     */
    public function setCategory(\Opit\OpitHrm\LeaveBundle\Entity\LeaveCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set leaveRequest
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return Leave
     */
    public function setLeaveRequest(\Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest = null)
    {
        $this->leaveRequest = $leaveRequest;

        return $this;
    }

    /**
     * Get leaveRequest
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest
     */
    public function getLeaveRequest()
    {
        return $this->leaveRequest;
    }

    /**
     * Set numberOfDays
     *
     * @param integer $numberOfDays
     * @return Leave
     */
    public function setNumberOfDays($numberOfDays)
    {
        $this->numberOfDays = $numberOfDays;

        return $this;
    }

    /**
     * Get numberOfDays
     *
     * @return integer
     */
    public function getNumberOfDays()
    {
        return $this->numberOfDays;
    }

    /**
     * If LR is MLR check if leave date is in the past
     *
     * @Assert\Callback
     */
    public function validatePastLeaveDate(ExecutionContextInterface $context)
    {
        // Check if LR is MLR or request is not created by a GM
        if (true === $this->getLeaveRequest()->getIsMassLeaveRequest() || false === $this->getLeaveRequest()->getIsCreatedByGM()) {
            if (null !== $this && $this->getStartDate()->format('Y-m-d') < date('Y-m-d')) {
                $context->addViolationAt(
                    'startDate',
                    sprintf('Start date can not be in the past.')
                );
            }
        }
    }

    /**
     * Check if start date is bigger than end date
     *
     * @Assert\Callback
     */
    public function validateStartDate(ExecutionContextInterface $context)
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if (null !== $startDate && null !== $endDate && $startDate->format('Y-m-d') > $endDate->format('Y-m-d')) {
            $context->addViolationAt(
                'startDate',
                sprintf('Start date can not be bigger than end date.')
            );
        }
    }
}
