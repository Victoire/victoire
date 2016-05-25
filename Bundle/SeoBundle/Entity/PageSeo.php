<?php

namespace Victoire\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
     * @Gedmo\Translatable
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     * @Assert\Length(max = 155)
     * @Gedmo\Translatable
     */
    protected $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="rel_author", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $relAuthor;

    /**
     * @var string
     *
     * @ORM\Column(name="rel_publisher", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $relPublisher;

    /**
     * @var string
     *
     * @ORM\Column(name="ogTitle", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $ogTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="ogType", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
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
     * @Gedmo\Translatable
     */
    protected $ogUrl;

    /**
     * @var text
     *
     * @ORM\Column(name="ogDescription", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $ogDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="fbAdmins", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $fbAdmins;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterCard", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $twitterCard = 'summary';

    /**
     * @var string
     *
     * @ORM\Column(name="twitterUrl", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     * @Assert\Length(max = 15)
     */
    protected $twitterUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterCreator", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     * @Assert\Length(max = 15)
     */
    protected $twitterCreator;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterTitle", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     * @Assert\Length(max = 70)
     */
    protected $twitterTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterDescription", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
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
     * @Gedmo\Translatable
     */
    protected $schemaPageType;

    /**
     * @var string
     *
     * @ORM\Column(name="schemaName", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $schemaName;

    /**
     * @var string
     *
     * @ORM\Column(name="schemaDescription", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
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
     * @Gedmo\Translatable
     */
    protected $metaRobotsIndex;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_robots_follow", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaRobotsFollow;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_robots_advanced", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaRobotsAdvanced;

    /**
     * @var bool
     *
     * @ORM\Column(name="sitemap_indexed", type="boolean", nullable=true, options={"default" = true})
     * @Gedmo\Translatable
     */
    protected $sitemapIndexed = true;

    /**
     * @var float
     *
     * @ORM\Column(name="sitemap_priority", type="float", nullable=true, options={"default" = "0.8"})
     * @Gedmo\Translatable
     */
    protected $sitemapPriority = 0.8;

    /**
     * @var string
     *
     * @ORM\Column(name="sitemap_changeFreq", type="string", length=20, nullable=true, options={"default" = "monthly"})
     * @Gedmo\Translatable
     */
    protected $sitemapChangeFreq = 'monthly';

    /**
     * @var string
     *
     * @ORM\Column(name="rel_canonical", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $relCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $keyword;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\PageBundle\Entity\Page", inversedBy="referers", cascade={"persist"})
     * @ORM\JoinColumn(name="redirect_to", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $redirectTo;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    protected $locale;

    /**
     * contructor.
     **/
    public function __construct()
    {
        $this->createdAt = $this->createdAt ? $this->createdAt : new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set redirectTo.
     *
     * @param View $redirectTo
     *
     * @return PageSeo
     */
    public function setRedirectTo(View $redirectTo)
    {
        $this->redirectTo = $redirectTo;

        return $this;
    }

    /**
     * Get redirectTo.
     *
     * @return string
     */
    public function getRedirectTo()
    {
        return $this->redirectTo;
    }

    /**
     * Set metaTitle.
     *
     * @param string $metaTitle
     *
     * @return PageSeo
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get metaTitle.
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set metaDescription.
     *
     * @param string $metaDescription
     *
     * @return PageSeo
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription.
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set relAuthor.
     *
     * @param string $relAuthor
     *
     * @return PageSeo
     */
    public function setRelAuthor($relAuthor)
    {
        $this->relAuthor = $relAuthor;

        return $this;
    }

    /**
     * Get relAuthor.
     *
     * @return string
     */
    public function getRelAuthor()
    {
        return $this->relAuthor;
    }

    /**
     * Set relPublisher.
     *
     * @param string $relPublisher
     *
     * @return PageSeo
     */
    public function setRelPublisher($relPublisher)
    {
        $this->relPublisher = $relPublisher;

        return $this;
    }

    /**
     * Get relPublisher.
     *
     * @return string
     */
    public function getRelPublisher()
    {
        return $this->relPublisher;
    }

    /**
     * Set ogTitle.
     *
     * @param string $ogTitle
     *
     * @return PageSeo
     */
    public function setOgTitle($ogTitle)
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    /**
     * Get ogTitle.
     *
     * @return string
     */
    public function getOgTitle()
    {
        return $this->ogTitle;
    }

    /**
     * Set ogType.
     *
     * @param string $ogType
     *
     * @return PageSeo
     */
    public function setOgType($ogType)
    {
        $this->ogType = $ogType;

        return $this;
    }

    /**
     * Get ogType.
     *
     * @return string
     */
    public function getOgType()
    {
        return $this->ogType;
    }

    /**
     * Set ogImage.
     *
     * @param Image $ogImage
     *
     * @return PageSeo
     */
    public function setOgImage($ogImage)
    {
        $this->ogImage = $ogImage;

        return $this;
    }

    /**
     * Get ogImage.
     *
     * @return string
     */
    public function getOgImage()
    {
        return $this->ogImage;
    }

    /**
     * Set ogUrl.
     *
     * @param string $ogUrl
     *
     * @return PageSeo
     */
    public function setOgUrl($ogUrl)
    {
        $this->ogUrl = $ogUrl;

        return $this;
    }

    /**
     * Get ogUrl.
     *
     * @return string
     */
    public function getOgUrl()
    {
        return $this->ogUrl;
    }

    /**
     * Set ogDescription.
     *
     * @param string $ogDescription
     *
     * @return PageSeo
     */
    public function setOgDescription($ogDescription)
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    /**
     * Get ogDescription.
     *
     * @return string
     */
    public function getOgDescription()
    {
        return $this->ogDescription;
    }

    /**
     * Set fbAdmins.
     *
     * @param string $fbAdmins
     *
     * @return PageSeo
     */
    public function setFbAdmins($fbAdmins)
    {
        $this->fbAdmins = $fbAdmins;

        return $this;
    }

    /**
     * Get fbAdmins.
     *
     * @return string
     */
    public function getFbAdmins()
    {
        return $this->fbAdmins;
    }

    /**
     * Set twitterCard.
     *
     * @param string $twitterCard
     *
     * @return PageSeo
     */
    public function setTwitterCard($twitterCard)
    {
        $this->twitterCard = $twitterCard;

        return $this;
    }

    /**
     * Get twitterCard.
     *
     * @return string
     */
    public function getTwitterCard()
    {
        return $this->twitterCard;
    }

    /**
     * Set twitterUrl.
     *
     * @param string $twitterUrl
     *
     * @return PageSeo
     */
    public function setTwitterUrl($twitterUrl)
    {
        $this->twitterUrl = $twitterUrl;

        return $this;
    }

    /**
     * Get twitterUrl.
     *
     * @return string
     */
    public function getTwitterUrl()
    {
        return $this->twitterUrl;
    }

    /**
     * Set twitterCreator.
     *
     * @param string $twitterCreator
     *
     * @return PageSeo
     */
    public function setTwitterCreator($twitterCreator)
    {
        $this->twitterCreator = $twitterCreator;

        return $this;
    }

    /**
     * Get twitterCreator.
     *
     * @return string
     */
    public function getTwitterCreator()
    {
        return $this->twitterCreator;
    }

    /**
     * Set twitterTitle.
     *
     * @param string $twitterTitle
     *
     * @return PageSeo
     */
    public function setTwitterTitle($twitterTitle)
    {
        $this->twitterTitle = $twitterTitle;

        return $this;
    }

    /**
     * Get twitterTitle.
     *
     * @return string
     */
    public function getTwitterTitle()
    {
        return $this->twitterTitle;
    }

    /**
     * Set twitterDescription.
     *
     * @param string $twitterDescription
     *
     * @return PageSeo
     */
    public function setTwitterDescription($twitterDescription)
    {
        $this->twitterDescription = $twitterDescription;

        return $this;
    }

    /**
     * Get twitterDescription.
     *
     * @return string
     */
    public function getTwitterDescription()
    {
        return $this->twitterDescription;
    }

    /**
     * Set twitterImage.
     *
     * @param Image $twitterImage
     *
     * @return PageSeo
     */
    public function setTwitterImage($twitterImage)
    {
        $this->twitterImage = $twitterImage;

        return $this;
    }

    /**
     * Get twitterImage.
     *
     * @return string
     */
    public function getTwitterImage()
    {
        return $this->twitterImage;
    }

    /**
     * Set schemaPageType.
     *
     * @param string $schemaPageType
     *
     * @return PageSeo
     */
    public function setSchemaPageType($schemaPageType)
    {
        $this->schemaPageType = $schemaPageType;

        return $this;
    }

    /**
     * Get schemaPageType.
     *
     * @return string
     */
    public function getSchemaPageType()
    {
        return $this->schemaPageType;
    }

    /**
     * Set schemaName.
     *
     * @param string $schemaName
     *
     * @return PageSeo
     */
    public function setSchemaName($schemaName)
    {
        $this->schemaName = $schemaName;

        return $this;
    }

    /**
     * Get schemaName.
     *
     * @return string
     */
    public function getSchemaName()
    {
        return $this->schemaName;
    }

    /**
     * Set schemaDescription.
     *
     * @param string $schemaDescription
     *
     * @return PageSeo
     */
    public function setSchemaDescription($schemaDescription)
    {
        $this->schemaDescription = $schemaDescription;

        return $this;
    }

    /**
     * Get schemaDescription.
     *
     * @return string
     */
    public function getSchemaDescription()
    {
        return $this->schemaDescription;
    }

    /**
     * Set schemaImage.
     *
     * @param Image $schemaImage
     *
     * @return PageSeo
     */
    public function setSchemaImage($schemaImage)
    {
        $this->schemaImage = $schemaImage;

        return $this;
    }

    /**
     * Get schemaImage.
     *
     * @return string
     */
    public function getSchemaImage()
    {
        return $this->schemaImage;
    }

    /**
     * Set metaRobotsIndex.
     *
     * @param string $metaRobotsIndex
     *
     * @return PageSeo
     */
    public function setMetaRobotsIndex($metaRobotsIndex)
    {
        $this->metaRobotsIndex = $metaRobotsIndex;

        return $this;
    }

    /**
     * Get metaRobotsIndex.
     *
     * @return string
     */
    public function getMetaRobotsIndex()
    {
        return $this->metaRobotsIndex;
    }

    /**
     * Set metaRobotsFollow.
     *
     * @param string $metaRobotsFollow
     *
     * @return PageSeo
     */
    public function setMetaRobotsFollow($metaRobotsFollow)
    {
        $this->metaRobotsFollow = $metaRobotsFollow;

        return $this;
    }

    /**
     * Get metaRobotsFollow.
     *
     * @return string
     */
    public function getMetaRobotsFollow()
    {
        return $this->metaRobotsFollow;
    }

    /**
     * Set metaRobotsAdvanced.
     *
     * @param string $metaRobotsAdvanced
     *
     * @return PageSeo
     */
    public function setMetaRobotsAdvanced($metaRobotsAdvanced)
    {
        $this->metaRobotsAdvanced = $metaRobotsAdvanced;

        return $this;
    }

    /**
     * Get metaRobotsAdvanced.
     *
     * @return string
     */
    public function getMetaRobotsAdvanced()
    {
        return $this->metaRobotsAdvanced;
    }

    /**
     * Set sitemapIndexed.
     *
     * @param bool $sitemapIndexed
     *
     * @return PageSeo
     */
    public function setSitemapIndexed($sitemapIndexed)
    {
        $this->sitemapIndexed = $sitemapIndexed;

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
     * Set sitemapPriority.
     *
     * @param float $sitemapPriority
     *
     * @return PageSeo
     */
    public function setSitemapPriority($sitemapPriority)
    {
        $this->sitemapPriority = $sitemapPriority;

        return $this;
    }

    /**
     * Get sitemapPriority.
     *
     * @return float
     */
    public function getSitemapPriority()
    {
        return $this->sitemapPriority;
    }

    /**
     * Set sitemapChangeFreq.
     *
     * @param float $sitemapChangeFreq
     *
     * @return PageSeo
     */
    public function setSitemapChangeFreq($sitemapChangeFreq)
    {
        $this->sitemapChangeFreq = $sitemapChangeFreq;

        return $this;
    }

    /**
     * Get sitemapChangeFreq.
     *
     * @return float
     */
    public function getSitemapChangeFreq()
    {
        return $this->sitemapChangeFreq;
    }

    /**
     * Set relCanonical.
     *
     * @param string $relCanonical
     *
     * @return PageSeo
     */
    public function setRelCanonical($relCanonical)
    {
        $this->relCanonical = $relCanonical;

        return $this;
    }

    /**
     * Get relCanonical.
     *
     * @return string
     */
    public function getRelCanonical()
    {
        return $this->relCanonical;
    }

    /**
     * Set keyword.
     *
     * @param string $keyword
     *
     * @return PageSeo
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword.
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
