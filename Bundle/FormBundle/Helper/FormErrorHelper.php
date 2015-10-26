<?php

namespace Victoire\Bundle\FormBundle\Helper;

/**
 * A service that give errors from a form as a string.
 *
 * service: victoire_form.error_helper
 */
class FormErrorHelper
{
    protected $translator = null;

    /**
     * The constructor.
     *
     * @param Translator $translator The translator service
     * @param booleand   $debug      Is debug mode enabled ? It will be verbose then.
     */
    public function __construct($translator, $debug)
    {
        $this->translator = $translator;
        $this->debug = $debug;
    }

    /**
     * Returns a string representation of all form errors (including children errors).
     *
     * This method should only be used in ajax calls.
     *
     * @param Form $form         The form to parse
     * @param bool $withChildren Do we parse the embedded forms
     *
     * @return string A string representation of all errors
     */
    public function getRecursiveReadableErrors($form, $withChildren = true, $translationDomain = null, $level = 0)
    {
        $errors = '';
        $translationDomain = $translationDomain ? $translationDomain : $form->getConfig()->getOption('translation_domain');

        //the errors of the fields
        foreach ($form->getErrors() as $error) {
            //the view contains the label identifier
            $view = $form->createView();
            $labelId = $view->vars['label'];

            //get the translated label
            if ($labelId !== null) {
                $label = $this->translator->trans(/* @Ignore */$labelId, [], $translationDomain).' : ';
            } else {
                $label = '';
            }

            //in case of dev mode, we display the item that is a problem
            //getCause cames in Symfony 2.5 version, this is just a fallback to avoid BC with previous versions
            if ($this->debug && method_exists($error, 'getCause')) {
                $cause = $error->getCause();
                if ($cause !== null) {
                    $causePropertyPath = $cause->getPropertyPath();
                    $errors .= ' '.$causePropertyPath;
                }
            }

            //add the error
            $errors .= $label.$this->translator->trans(/* @Ignore */$error->getMessage(), [], $translationDomain)."\n";
        }

        //do we parse the children
        if ($withChildren) {
            //we parse the children
            foreach ($form->getIterator() as $child) {
                $level++;
                if ($err = $this->getRecursiveReadableErrors($child, $withChildren, $translationDomain, $level)) {
                    $errors .= $err;
                }
            }
        }

        return $errors;
    }
}
