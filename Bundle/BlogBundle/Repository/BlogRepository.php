<?php

namespace Victoire\Bundle\BlogBundle\Repository;

use Victoire\Bundle\PageBundle\Repository\BasePageRepository;

/**
 * The Blog repository.
 */
class BlogRepository extends BasePageRepository
{
    /**
     * Return true if at least one Blog has multiple translations.
     *
     * @return bool
     */
    public function hasMultipleBlog()
    {
        $queryBuilder = $this->createQueryBuilder('blog')
            ->select('b_translation.id')
            ->join('blog.translations', 'b_translation');

        return count($queryBuilder->getQuery()->getResult()) >= 1;
    }

    /**
     * Get all locales used by Blogs.
     *
     * @return array
     */
    public function getUsedLocales()
    {
        $queryBuilder = $this->createQueryBuilder('blog')
            ->select('DISTINCT(b_translation.locale) AS locale')
            ->join('blog.translations', 'b_translation');

        $locales = [];
        foreach ($queryBuilder->getQuery()->getResult() as $locale) {
            $locales[] = $locale['locale'];
        }

        return $locales;
    }

    /**
     * Get all Blogs for a given locale.
     *
     * @param $locale
     *
     * @return array
     */
    public function getBlogsForLocale($locale)
    {
        $blogs = $this->joinTranslations($locale)->getInstance()->getQuery()->getResult();

        $this->clearInstance();

        return $blogs;
    }
}
