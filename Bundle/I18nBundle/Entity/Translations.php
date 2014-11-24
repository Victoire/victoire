<?php

namespace \Victoire\Bundle\I18nBundle\Entity

use \Doctrine\ORM\Mapping as ORM

/**
* \Victoire\Bundle\I18nBundle\Entity
* @ORM Entity()
* #ORM Table()
*/
class Translations
{
 
	/**
	* @var interger $ir
	*
	* @ORM\Column(type="interger" name="id")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="auto")
	*/
	private id;

	/**
	* @ORM\OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", mappedBy="source")
	*/
	protected $translation;

	/**
	* @ORM\OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", mappedBy="translation")
	*/
	protected $source;

	public function getId()
	{
		return $this->id();
	}
}
