<?php

namespace Victoire\Bundle\BlogBundle;

/**
 * Contains all events thrown in the VictoireBlogBundle.
 */
final class VictoireBlogEvents
{
    /**
     * The CREATE_ARTICLE event occurs when the article is create.
     *
     * This event allows you to modify the response and get the article.
     * The event listener method receives a Victoire\Bundle\BlogBundle\Event\ArticleEvent instance.
     */
    const CREATE_ARTICLE = 'victoire_blog.create.article';
}
