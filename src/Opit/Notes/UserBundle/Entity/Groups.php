<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Roles
 *
 * @author OPIT\kaufmann
 * 
 * @ORM\Table(name="notes_groups")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\GroupsRepository")
 */
class Groups implements RoleInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(name="role", type="string", length=20, unique=true)
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @see RoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }
    
    public function setRole($role)
    {
        $this->role = $role;
        
        return $this;
    }
    
    /**
     * 
     * @see RoleInterface
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 
     * @see RoleInterface
     */    
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * 
     * @see RoleInterface
     */     
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add users
     *
     * @param \Opit\Notes\UserBundle\Entity\User $users
     * @return Roles
     */
    public function addUser(\Opit\Notes\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Opit\Notes\UserBundle\Entity\User $users
     */
    public function removeUser(\Opit\Notes\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }
}