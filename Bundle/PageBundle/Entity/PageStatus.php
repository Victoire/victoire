<?php

namespace Victoire\Bundle\PageBundle\Entity;

/**
 * Page status
 * This class exists in order to be able to get page status in one single place.
 * The status is brought to view with the WebViewTrait so it could be a correct container for it but a Trait cannot have constant.
 * The alternative was to declare those constants each time we were adding WebViewTrait but it brought content duplication and complexity.
 */
class PageStatus
{
    const DRAFT = 'draft';
    const PUBLISHED = 'published';
    const UNPUBLISHED = 'unpublished';
    const SCHEDULED = 'scheduled';
    const DELETED = 'deleted';
}
