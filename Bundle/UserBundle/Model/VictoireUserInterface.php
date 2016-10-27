<?php

namespace Victoire\Bundle\UserBundle\Model;

use FOS\UserBundle\Model\UserInterface;

/**
 * @author Leny BERNARD <leny@troopers.email>
 */
interface VictoireUserInterface extends UserInterface
{
    /**
     * Set the firstname.
     *
     * @param string $firstname
     *
     * @return self
     */
    public function setFirstname($firstname);

    /**
     * Get the firstname.
     *
     *
     * @return self
     */
    public function getFirstname();

    /**
     * Set the lastname.
     *
     * @param string $lastname
     *
     * @return self
     */
    public function setLastname($lastname);

    /**
     * Get the lastname.
     *
     *
     * @return self
     */
    public function getLastname();

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale);

    /**
     * Get the locale.
     *
     *
     * @return self
     */
    public function getLocale();
}
