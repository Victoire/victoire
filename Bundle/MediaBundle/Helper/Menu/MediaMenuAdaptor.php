<?php

namespace Victoire\Bundle\MediaBundle\Helper\Menu;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Media Menu Adaptor.
 */
class MediaMenuAdaptor implements MenuAdaptorInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * In this method you can add children for a specific parent, but also remove and change the already created children.
     *
     * @param MenuBuilder $menu      The MenuBuilder
     * @param MenuItem[]  &$children The current children
     * @param MenuItem    $parent    The parent Menu item
     * @param Request     $request   The Request
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        $mediaRoutes = [
            'Show media'    => 'VictoireMediaBundle_media_show',
            'Edit metadata' => 'VictoireMediaBundle_metadata_edit',
            'Edit slide'    => 'VictoireMediaBundle_slide_edit',
            'Edit video'    => 'VictoireMediaBundle_video_edit',
        ];

        $createRoutes = [
            'Create'      => 'VictoireMediaBundle_media_create',
            'Bulk upload' => 'VictoireMediaBundle_media_bulk_upload',
        ];

        $allRoutes = array_merge($createRoutes, $mediaRoutes);

        if (is_null($parent)) {
            /* @var \Victoire\Bundle\MediaBundle\Entity\Folder[] $galleries */
            $galleries = $this->em->getRepository('VictoireMediaBundle:Folder')->getAllFolders();
            $currentId = $request->get('folderId');

            if (isset($currentId)) {
                /* @var \Victoire\Bundle\MediaBundle\Entity\Folder $currentFolder */
                $currentFolder = $this->em->getRepository('VictoireMediaBundle:Folder')->findOneById($currentId);
            } elseif (in_array($request->attributes->get('_route'), $mediaRoutes)) {
                /* @var \Victoire\Bundle\MediaBundle\Entity\Media $media */
                $media = $this->em->getRepository('VictoireMediaBundle:Media')->getMedia($request->get('mediaId'));
                $currentFolder = $media->getFolder();
            } elseif (in_array($request->attributes->get('_route'), $createRoutes)) {
                $currentId = $request->get('folderId');
                if (isset($currentId)) {
                    $currentFolder = $this->em->getRepository('VictoireMediaBundle:Folder')->findOneById($currentId);
                }
            }

            if (isset($currentFolder)) {
                $parents = $currentFolder->getParents();
            } else {
                $parents = [];
            }

            foreach ($galleries as $folder) {
                $menuitem = new TopMenuItem($menu);
                $menuitem->setRoute('VictoireMediaBundle_folder_show');
                $menuitem->setRouteparams(['folderId' => $folder->getId()]);
                $menuitem->setInternalname($folder->getName());
                $menuitem->setParent($parent);
                $menuitem->setRole($folder->getRel());
                if (isset($currentFolder) && (stripos($request->attributes->get('_route'), $menuitem->getRoute()) !== false || in_array($request->attributes->get('_route'), $allRoutes))) {
                    if ($currentFolder->getId() == $folder->getId()) {
                        $menuitem->setActive(true);
                    } else {
                        foreach ($parents as $_parent) {
                            if ($_parent->getId() == $folder->getId()) {
                                $menuitem->setActive(true);
                                break;
                            }
                        }
                    }
                }
                $children[] = $menuitem;
            }
        } elseif ('VictoireMediaBundle_folder_show' == $parent->getRoute()) {
            $parentRouteParams = $parent->getRouteparams();
            /* @var \Victoire\Bundle\MediaBundle\Entity\Folder $parentFolder */
            $parentFolder = $this->em->getRepository('VictoireMediaBundle:Folder')->findOneById($parentRouteParams['folderId']);
            /* @var \Victoire\Bundle\MediaBundle\Entity\Folder[] $galleries */
            $galleries = $parentFolder->getChildren();
            $currentId = $request->get('folderId');

            if (isset($currentId)) {
                /* @var \Victoire\Bundle\MediaBundle\Entity\Folder $currentFolder */
                $currentFolder = $this->em->getRepository('VictoireMediaBundle:Folder')->findOneById($currentId);
            } elseif (in_array($request->attributes->get('_route'), $mediaRoutes)) {
                $media = $this->em->getRepository('VictoireMediaBundle:Media')->getMedia($request->get('mediaId'));
                $currentFolder = $media->getFolder();
            } elseif (in_array($request->attributes->get('_route'), $createRoutes)) {
                $currentId = $request->get('folderId');
                if (isset($currentId)) {
                    $currentFolder = $this->em->getRepository('VictoireMediaBundle:Folder')->findOneById($currentId);
                }
            }

            /* @var \Victoire\Bundle\MediaBundle\Entity\Folder[] $parentGalleries */
            $parentGalleries = null;
            if (isset($currentFolder)) {
                $parentGalleries = $currentFolder->getParents();
            } else {
                $parentGalleries = [];
            }

            foreach ($galleries as $folder) {
                $menuitem = new MenuItem($menu);
                $menuitem->setRoute('VictoireMediaBundle_folder_show');
                $menuitem->setRouteparams(['folderId' => $folder->getId()]);
                $menuitem->setInternalname($folder->getName());
                $menuitem->setParent($parent);
                $menuitem->setRole($folder->getRel());
                if (isset($currentFolder) && (stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0 || in_array($request->attributes->get('_route'), $allRoutes))) {
                    if ($currentFolder->getId() == $folder->getId()) {
                        $menuitem->setActive(true);
                    } else {
                        foreach ($parentGalleries as $parentFolder) {
                            if ($parentFolder->getId() == $folder->getId()) {
                                $menuitem->setActive(true);
                                break;
                            }
                        }
                    }
                }
                $children[] = $menuitem;
            }

            foreach ($allRoutes as $name => $route) {
                $menuitem = new MenuItem($menu);
                $menuitem->setRoute($route);
                $menuitem->setInternalname($name);
                $menuitem->setParent($parent);
                $menuitem->setAppearInNavigation(false);
                if (stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0) {
                    if (stripos($menuitem->getRoute(), 'VictoireMediaBundle_media_show') === 0) {
                        /* @var Media $media */
                        $media = $this->em->getRepository('VictoireMediaBundle:Media')->getMedia($request->get('mediaId'));
                        $menuitem->setInternalname('Show '.$media->getClassType().' '.$media->getName());
                    }
                    $menuitem->setActive(true);
                }

                $children[] = $menuitem;
            }
        }
    }
}
