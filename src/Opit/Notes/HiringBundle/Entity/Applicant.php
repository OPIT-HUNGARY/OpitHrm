<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * JobApplicant
 *
 * @ORM\Table(name="notes_applicants")
 * @ORM\Entity()
 */
class Applicant
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
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string")
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string")
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="string")
     */
    protected $keywords;

    /**
     * @Assert\File(maxSize="1000000")
     */
    protected $cvFile;

    /**
     * @var string
     *
     * @ORM\Column(name="cv", type="string")
     */
    protected $cvPath;

    /**
     * @var date
     *
     * @ORM\Column(name="applicationDate", type="date")
     */
    protected $applicationDate;

    /**
     * @ORM\ManyToMany(targetEntity="JobPosition", mappedBy="applicants")
     */
    protected $jobPositions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobPositions = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return JobApplicant
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
     * Set email
     *
     * @param string $email
     * @return JobApplicant
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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return JobApplicant
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return JobApplicant
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set cvPath
     *
     * @param string $cvPath
     * @return JobApplicant
     */
    public function setCvPath($cvPath)
    {
        $this->cvPath = $cvPath;

        return $this;
    }

    /**
     * Get cvPath
     *
     * @return string
     */
    public function getCvPath()
    {
        return $this->cvPath;
    }

    /**
     * Set cvFile
     *
     * @param string $cvFile
     * @return JobApplicant
     */
    public function setCvFile(UploadedFile $cvFile = null)
    {
        $this->cvFile = $cvFile;

        return $this;
    }

    /**
     * Get cvFile
     *
     * @return string
     */
    public function getCvFile()
    {
        return $this->cvFile;
    }

    /**
     * Set applicationDate
     *
     * @param \DateTime $applicationDate
     * @return JobApplicant
     */
    public function setApplicationDate($applicationDate)
    {
        $this->applicationDate = $applicationDate;

        return $this;
    }

    /**
     * Get applicationDate
     *
     * @return \DateTime
     */
    public function getApplicationDate()
    {
        return $this->applicationDate;
    }

    /**
     * Add jobPositions
     *
     * @param \Opit\Notes\HiringBundle\Entity\JobPosition $jobPositions
     * @return JobApplicant
     */
    public function addJobPosition(\Opit\Notes\HiringBundle\Entity\JobPosition $jobPositions)
    {
        $this->jobPositions[] = $jobPositions;

        return $this;
    }

    /**
     * Remove jobPositions
     *
     * @param \Opit\Notes\HiringBundle\Entity\JobPosition $jobPositions
     */
    public function removeJobPosition(\Opit\Notes\HiringBundle\Entity\JobPosition $jobPositions)
    {
        $this->jobPositions->removeElement($jobPositions);
    }

    /**
     * Get jobPositions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobPositions()
    {
        return $this->jobPositions;
    }

    /**
     * Returns the absolute path to a file
     *
     * @return type
     */
    public function getAbsolutePath()
    {
        return null === $this->cvPath ? null : $this->getUploadRootDir().'/'.$this->cvPath;
    }

    /**
     * The absolute directory path where the CVs should be saved
     *
     * @return type
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    /**
     * Return web path which can be used in a template to link to the file
     *
     * @return type
     */
    public function getWebPath()
    {
        return null === $this->cvPath ? null : $this->getUploadDir().'/'.$this->cvPath;
    }

    /**
     * Gets the files upload directory
     *
     * @return string
     */
    protected function getUploadDir()
    {
        return 'docs/cv';
    }

    /**
     * Uploads the cv file
     *
     * @return type
     */
    public function uploadCV()
    {
        if (null === $this->getCvFile()) {
            return;
        }

        $originalCVFileName = $this->getCvFile()->getClientOriginalName();

        $this->getCvFile()->move(
            $this->getUploadRootDir(),
            $originalCVFileName
        );

        $this->cvPath = $originalCVFileName;

        $this->cvFile = null;
    }
}
