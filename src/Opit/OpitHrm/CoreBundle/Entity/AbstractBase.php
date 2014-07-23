<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * AbstractBase class is sharing properties equal for all entities
 *
 * There is a problem for the mapped superclass not considering properties which are not set as
 * "private" members. Ensure all superclass properties are set as private!
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CoreBundle
 *
 * @Serializer\ExclusionPolicy("all")
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractBase
{
    /**
     * @Serializer\Expose
     * @Serializer\XmlAttribute
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     * @ORM\JoinColumn(name="created_user_id", referencedColumnName="id")
     * @Gedmo\Blameable(on="create")
     */
    private $createdUser;

    /**
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     * @ORM\JoinColumn(name="updated_user_id", referencedColumnName="id")
     * @Gedmo\Blameable(on="update")
     */
    private $updatedUser;

     /**
      * @ORM\Column(type="boolean", options={"default" = false})
      */
    private $system;

    public function __construct()
    {
        $this->setSystem(false);
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return AbstractBase
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return AbstractBase
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set createdUser
     *
     * @param object $createdUser
     * @return object
     */
    public function setCreatedUser(UserInterface $createdUser = null)
    {
        $this->createdUser = $createdUser;

        return $this;
    }

    /**
     * Get createdUser
     *
     * @return object User
     */
    public function getCreatedUser()
    {
        return $this->createdUser;
    }

    /**
     * Set updatedUser
     *
     * @param object $updatedUser
     * @return object
     */
    public function setUpdatedUser(UserInterface $updatedUser = null)
    {
        $this->updatedUser = $updatedUser;

        return $this;
    }

    /**
     * Get updatedUser
     *
     * @return object User
     */
    public function getUpdatedUser()
    {
        return $this->updatedUser;
    }

    /**
     * Set system
     *
     * @param boolean $system
     * @return AbstractBase
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return boolean
     */
    public function getSystem()
    {
        return $this->system;
    }
}
