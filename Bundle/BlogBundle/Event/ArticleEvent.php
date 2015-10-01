<?php

namespace Victoire\Bundle\BlogBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\BlogBundle\Entity\Article;

class ArticleEvent extends Event
{
    private $response;
    private $article;

    /**
     * Constructor.
     *
     * @param Article $article
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Get the article.
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Get response.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response.
     *
     * @param string $response
     *
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }
}
