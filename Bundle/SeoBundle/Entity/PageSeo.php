<?php

namespace Victoire\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * PageSeo.
 *
 * @ORM\Table("vic_page_seo")
 * @ORM\Entity()
 */
class PageSeo
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;
    use Translatable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", nullable=true)
     * @Assert\Length(max = 60)
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     * @Assert\Length(max = 155)
     */
    protected $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="rel_author", type="string", length=255, nullable=true)
     */
    protected $relAuthor;

    /**
     * @var string
     *
     * @ORM\Column(name="rel_publisher", type="string", length=255, nullable=true)
     */
    protected $relPublisher;

    /**
     * @var string
     *
     * @ORM\Column(name="ogTitle", type="string", length=255, nullable=true)
     */
    protected $ogTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="ogType", type="string", length=255, nullable=true)
     */
    protected $ogType;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="ogImage_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $ogImage;

    /**
     * @var string
     *
     * @ORM\Column(name="ogUrl", type="string", length=255, nullable=true)
     */
    protected $ogUrl;

    /**
     * @var text
     *
     * @ORM\Column(name="ogDescription", type="text", nullable=true)
     */
    protected $ogDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="fbAdmins", type="string", length=255, nullable=true)
     */
    protected $fbAdmins;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterCard", type="string", length=255, nullable=true)
     */
    protected $twitterCard = 'summary';

    /**
     * @var string
     *
     * @ORM\Column(name="twitterUrl", type="string", length=255, nullable=true)
     * @Assert\Length(max = 15)
     */
    protected $twitterUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterCreator", type="string", length=255, nullable=true)
     * @Assert\Length(max = 15)
     */
    protected $twitterCreator;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterTitle", type="string", length=255, nullable=true)
     * @Assert\Length(max = 70)
     */
    protected $twitterTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterDescription", type="string", length=255, nullable=true)
     * @Assert\Length(max = 200)
     */
    protected $twitterDescription;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="twitterImage_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $twitterImage;

    /**
     * @var string
     *
     * @ORM\Column(name="schemaPageType", type="string", length=255, nullable=true)
     */
    protected $schemaPageType;

    /**
     * @var string
     *
     * @ORM\Column(name="schemaName", type="string", length=255, nullable=true)
     */
    protected $schemaName;

    /**
     * @var string
     *
     * @ORM\Column(name="schemaDescription", type="string", length=255, nullable=true)
     */
    protected $schemaDescription;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="schemaImage_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $schemaImage;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_robots_index", type="string", length=255, nullable=true)
     */
    protected $metaRobotsIndex;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_robots_follow", type="string", length=255, nullable=true)
     */
    protected $metaRobotsFollow;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_robots_advanced", type="string", length=255, nullable=true)
     */
    protected $metaRobotsAdvanced;

    /**
     * @var bool
     *
     * @ORM\Column(name="sitemap_indexed", type="boolean", nullable=true, options={"default" = true})
     */
    protected $sitemapIndexed = true;

    /**
     * @var float
     *
     * @ORM\Column(name="sitemap_priority", type="float", nullable=true, options={"default" = "0.8"})
     */
    protected $sitemapPriority = 0.8;

    /**
     * @var string
     *
     * @ORM\Column(name="sitemap_changeFreq", type="string", length=20, nullable=true, options={"default" = "monthly"})
     */
    protected $sitemapChangeFreq = 'monthly';

    /**
     * @var string
     *
     * @ORM\Column(name="rel_canonical", type="string", length=255, nullable=true)
     */
    protected $relCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", length=255, nullable=true)
     */
    protected $keyword;

    /**
     * @var string
     *
     * @ORM\ManyToOne(
     *     targetEntity="\Victoire\Bundle\PageBundle\Entity\Page",
     *     inversedBy="referers",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="redirect_to", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $redirectTo;

    /**
     * Set redirectTo w/ proxy.
     *
     * @param View $redirectTo
     *
     * @return PageSeo
     */
    public function setRedirectTo(View $redirectTo, $locale = null)
    {
        $this->translate($locale, false)->setRedirectTo($redirectTo);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get redirectTo w/ proxy.
     *
     * @return string
     */
    public function getRedirectTo()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getRedirectTo');
    }

    /**
     * Set metaTitle w/ proxy.
     *
     * @param string $metaTitle
     *
     * @return PageSeo
     */
    public function setMetaTitle($metaTitle, $locale = null)
    {
        $this->translate($locale, false)->setMetaTitle($metaTitle);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get metaTitle w/ proxy.
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getMetaTitle');
    }

    /**
     * Set metaDescription w/ proxy.
     *
     * @param string $metaDescription
     *
     * @return PageSeo
     */
    public function setMetaDescription($metaDescription, $locale = null)
    {
        $this->translate($locale, false)->setMetaDescription($metaDescription);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get metaDescription w/ proxy.
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getMetaDescription');
    }

    /**
     * Set relAuthor w/ proxy.
     *
     * @param string $relAuthor
     *
     * @return PageSeo
     */
    public function setRelAuthor($relAuthor, $locale = null)
    {
        $this->translate($locale, false)->setRelAuthor($relAuthor);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get relAuthor w/ proxy.
     *
     * @return string
     */
    public function getRelAuthor()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getRelAuthor');
    }

    /**
     * Set relPublisher w/ proxy.
     *
     * @param string $relPublisher
     *
     * @return PageSeo
     */
    public function setRelPublisher($relPublisher, $locale = null)
    {
        $this->translate($locale, false)->setRelPublisher($relPublisher);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get relPublisher w/ proxy.
     *
     * @return string
     */
    public function getRelPublisher()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getRelPublisher');
    }

    /**
     * Set ogTitle w/ proxy.
     *
     * @param string $ogTitle
     *
     * @return PageSeo
     */
    public function setOgTitle($ogTitle, $locale = null)
    {
        $this->translate($locale, false)->setOgTitle($ogTitle);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get ogTitle w/ proxy.
     *
     * @return string
     */
    public function getOgTitle()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getOgTitle');
    }

    /**
     * Set ogType w/ proxy.
     *
     * @param string $ogType
     *
     * @return PageSeo
     */
    public function setOgType($ogType, $locale = null)
    {
        $this->translate($locale, false)->setOgType($ogType);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get ogType w/ proxy.
     *
     * @return string
     */
    public function getOgType()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getOgType');
    }

    /**
     * Set ogImage w/ proxy.
     *
     * @param Image $ogImage
     *
     * @return PageSeo
     */
    public function setOgImage($ogImage, $locale = null)
    {
        $this->translate($locale, false)->setOgImage($ogImage);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get ogImage w/ proxy.
     *
     * @return string
     */
    public function getOgImage()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getOgImage');
    }

    /**
     * Set ogUrl w/ proxy.
     *
     * @param string $ogUrl
     *
     * @return PageSeo
     */
    public function setOgUrl($ogUrl, $locale = null)
    {
        $this->translate($locale, false)->setOgUrl($ogUrl);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get ogUrl w/ proxy.
     *
     * @return string
     */
    public function getOgUrl()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getOgUrl');
    }

    /**
     * Set ogDescription w/ proxy.
     *
     * @param string $ogDescription
     *
     * @return PageSeo
     */
    public function setOgDescription($ogDescription, $locale = null)
    {
        $this->translate($locale, false)->setOgDescription($ogDescription);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get ogDescription w/ proxy.
     *
     * @return string
     */
    public function getOgDescription()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getOgDescription');
    }

    /**
     * Set fbAdmins w/ proxy.
     *
     * @param string $fbAdmins
     *
     * @return PageSeo
     */
    public function setFbAdmins($fbAdmins, $locale = null)
    {
        $this->translate($locale, false)->setFbAdmins($fbAdmins);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get fbAdmins w/ proxy.
     *
     * @return string
     */
    public function getFbAdmins()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getFbAdmins');
    }

    /**
     * Set twitterCard w/ proxy.
     *
     * @param string $twitterCard
     *
     * @return PageSeo
     */
    public function setTwitterCard($twitterCard, $locale = null)
    {
        $this->translate($locale, false)->setTwitterCard($twitterCard);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get twitterCard w/ proxy.
     *
     * @return string
     */
    public function getTwitterCard()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getTwitterCard');
    }

    /**
     * Set twitterUrl w/ proxy.
     *
     * @param string $twitterUrl
     *
     * @return PageSeo
     */
    public function setTwitterUrl($twitterUrl, $locale = null)
    {
        $this->translate($locale, false)->setTwitterUrl($twitterUrl);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get twitterUrl w/ proxy.
     *
     * @return string
     */
    public function getTwitterUrl()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getTwitterUrl');
    }

    /**
     * Set twitterCreator w/ proxy.
     *
     * @param string $twitterCreator
     *
     * @return PageSeo
     */
    public function setTwitterCreator($twitterCreator, $locale = null)
    {
        $this->translate($locale, false)->setTwitterCreator($twitterCreator);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get twitterCreator w/ proxy.
     *
     * @return string
     */
    public function getTwitterCreator()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getTwitterCreator');
    }

    /**
     * Set twitterTitle w/ proxy.
     *
     * @param string $twitterTitle
     *
     * @return PageSeo
     */
    public function setTwitterTitle($twitterTitle, $locale = null)
    {
        $this->translate($locale, false)->setTwitterTitle($twitterTitle);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get twitterTitle w/ proxy.
     *
     * @return string
     */
    public function getTwitterTitle()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getTwitterTitle');
    }

    /**
     * Set twitterDescription w/ proxy.
     *
     * @param string $twitterDescription
     *
     * @return PageSeo
     */
    public function setTwitterDescription($twitterDescription, $locale = null)
    {
        $this->translate($locale, false)->setTwitterDescription($twitterDescription);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get twitterDescription w/ proxy.
     *
     * @return string
     */
    public function getTwitterDescription()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getTwitterDescription');
    }

    /**
     * Set twitterImage w/ proxy.
     *
     * @param Image $twitterImage
     *
     * @return PageSeo
     */
    public function setTwitterImage($twitterImage, $locale = null)
    {
        $this->translate($locale, false)->setTwitterImage($twitterImage);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get twitterImage w/ proxy.
     *
     * @return string
     */
    public function getTwitterImage()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getTwitterImage');
    }

    /**
     * Set schemaPageType w/ proxy.
     *
     * @param string $schemaPageType
     *
     * @return PageSeo
     */
    public function setSchemaPageType($schemaPageType, $locale = null)
    {
        $this->translate($locale, false)->setSchemaPageType($schemaPageType);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get schemaPageType w/ proxy.
     *
     * @return string
     */
    public function getSchemaPageType()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSchemaPageType');
    }

    /**
     * Set schemaName w/ proxy.
     *
     * @param string $schemaName
     *
     * @return PageSeo
     */
    public function setSchemaName($schemaName, $locale = null)
    {
        $this->translate($locale, false)->setSchemaName($schemaName);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get schemaName w/ proxy.
     *
     * @return string
     */
    public function getSchemaName()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSchemaName');
    }

    /**
     * Set schemaDescription w/ proxy.
     *
     * @param string $schemaDescription
     *
     * @return PageSeo
     */
    public function setSchemaDescription($schemaDescription, $locale = null)
    {
        $this->translate($locale, false)->setSchemaDescription($schemaDescription);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get schemaDescription w/ proxy.
     *
     * @return string
     */
    public function getSchemaDescription()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSchemaDescription');
    }

    /**
     * Set schemaImage w/ proxy.
     *
     * @param Image $schemaImage
     *
     * @return PageSeo
     */
    public function setSchemaImage($schemaImage, $locale = null)
    {
        $this->translate($locale, false)->setSchemaImage($schemaImage);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get schemaImage w/ proxy.
     *
     * @return string
     */
    public function getSchemaImage()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSchemaImage');
    }

    /**
     * Set metaRobotsIndex w/ proxy.
     *
     * @param string $metaRobotsIndex
     *
     * @return PageSeo
     */
    public function setMetaRobotsIndex($metaRobotsIndex, $locale = null)
    {
        $this->translate($locale, false)->setMetaRobotsIndex($metaRobotsIndex);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get metaRobotsIndex w/ proxy.
     *
     * @return string
     */
    public function getMetaRobotsIndex()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getMetaRobotsIndex');
    }

    /**
     * Set metaRobotsFollow w/ proxy.
     *
     * @param string $metaRobotsFollow
     *
     * @return PageSeo
     */
    public function setMetaRobotsFollow($metaRobotsFollow, $locale = null)
    {
        $this->translate($locale, false)->setMetaRobotsFollow($metaRobotsFollow);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get metaRobotsFollow w/ proxy.
     *
     * @return string
     */
    public function getMetaRobotsFollow()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getMetaRobotsFollow');
    }

    /**
     * Set metaRobotsAdvanced w/ proxy.
     *
     * @param string $metaRobotsAdvanced
     *
     * @return PageSeo
     */
    public function setMetaRobotsAdvanced($metaRobotsAdvanced, $locale = null)
    {
        $this->translate($locale, false)->setMetaRobotsAdvanced($metaRobotsAdvanced);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get metaRobotsAdvanced w/ proxy.
     *
     * @return string
     */
    public function getMetaRobotsAdvanced()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getMetaRobotsAdvanced');
    }

    /**
     * Set sitemapIndexed w/ proxy.
     *
     * @param bool $sitemapIndexed
     *
     * @return PageSeo
     */
    public function setSitemapIndexed($sitemapIndexed, $locale = null)
    {
        $this->translate($locale, false)->setSitemapIndexed($sitemapIndexed);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get sitemapIndexed.
     *
     * @return bool
     */
    public function isSitemapIndexed()
    {
        return $this->sitemapIndexed;
    }

    /**
     * Set sitemapPriority w/ proxy.
     *
     * @param float $sitemapPriority
     *
     * @return PageSeo
     */
    public function setSitemapPriority($sitemapPriority, $locale = null)
    {
        $this->translate($locale, false)->setSitemapPriority($sitemapPriority);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get sitemapPriority w/ proxy.
     *
     * @return float
     */
    public function getSitemapPriority()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSitemapPriority');
    }

    /**
     * Set sitemapChangeFreq w/ proxy.
     *
     * @param float $sitemapChangeFreq
     *
     * @return PageSeo
     */
    public function setSitemapChangeFreq($sitemapChangeFreq, $locale = null)
    {
        $this->translate($locale, false)->setSitemapChangeFreq($sitemapChangeFreq);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get sitemapChangeFreq w/ proxy.
     *
     * @return float
     */
    public function getSitemapChangeFreq()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSitemapChangeFreq');
    }

    /**
     * Set relCanonical w/ proxy.
     *
     * @param string $relCanonical
     *
     * @return PageSeo
     */
    public function setRelCanonical($relCanonical, $locale = null)
    {
        $this->translate($locale, false)->setRelCanonical($relCanonical);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get relCanonical w/ proxy.
     *
     * @return string
     */
    public function getRelCanonical()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getRelCanonical');
    }

    /**
     * Set keyword w/ proxy.
     *
     * @param string $keyword
     * @param null   $locale
     *
     * @return PageSeo
     */
    public function setKeyword($keyword, $locale = null)
    {
        $this->translate($locale, false)->setKeyword($keyword);
        $this->mergeNewTranslations();

        return $this;
    }

    /**
     * Get keyword w/ proxy.
     *
     * @return string
     */
    public function getKeyword()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getKeyword');
    }
}
