<?php

namespace Victoire\Bundle\MediaBundle\Helper\RemoteVideo;

use Behat\Mink\Exception\Exception;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\MediaBundle\Form\RemoteVideo\RemoteVideoType;
use Victoire\Bundle\MediaBundle\Helper\Media\AbstractMediaHandler;
use Victoire\Bundle\MediaBundle\Helper\RemoteVideo\Exception\VideoException;

/**
 * RemoteVideoStrategy.
 */
class RemoteVideoHandler extends AbstractMediaHandler
{
    /**
     * @var string
     */
    const CONTENT_TYPE = 'remote/video';

    /**
     * @var string
     */
    const TYPE = 'video';

    /**
     * @return string
     */
    public function getName()
    {
        return 'Remote Video Handler';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @return RemoteVideoType
     */
    public function getFormType()
    {
        return new RemoteVideoType();
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function canHandle($object)
    {
        if ((is_string($object)) || ($object instanceof Media && $object->getContentType() == self::CONTENT_TYPE)) {
            return true;
        }

        return false;
    }

    /**
     * @param Media $media
     *
     * @return RemoteVideoHelper
     */
    public function getFormHelper(Media $media)
    {
        return new RemoteVideoHelper($media);
    }

    /**
     * @param Media $media
     * @throws VideoException
     */
    public function prepareMedia(Media $media)
    {
        if (null == $media->getUuid()) {
            $uuid = uniqid();
            $media->setUuid($uuid);
        }
        $video = new RemoteVideoHelper($media);
        $url = $video->getCode();
        $code = null;
        //update thumbnail
        switch ($video->getType()) {
            case 'youtube':
                $code = $this->isolateYoutubeVideoCode($url);
                $video->setThumbnailUrl('http://img.youtube.com/vi/'.$code.'/0.jpg');
                break;
            case 'vimeo':
                $code = $this->isolateVimeoVideoCode($url);
                $xml = simplexml_load_file('http://vimeo.com/api/v2/video/'.$code.'.xml');
                $video->setThumbnailUrl((string) $xml->video->thumbnail_large);
                break;
            case 'dailymotion':
                $code = $this->isolateDailymotionVideoCode($url);
                $json = json_decode(file_get_contents('https://api.dailymotion.com/video/'.$code.'?fields=thumbnail_large_url'));
                $thumbnailUrl = $json->{'thumbnail_large_url'};
                /* dirty hack to fix urls for imagine */
                if (!$this->endsWith($thumbnailUrl, '.jpg') && !$this->endsWith($thumbnailUrl, '.png')) {
                    $thumbnailUrl = $thumbnailUrl.'&ext=.jpg';
                }
                $video->setThumbnailUrl($thumbnailUrl);
                break;
        }

        if (null != $code) {
            $video->setCode($code);
            $video->setUrl($url);
        } else {
            throw new VideoException("no code found for remote video");
        }
    }

    /**
     * @param $link
     * @return mixed
     * @throws VideoException
     */
    public function isolateVimeoVideoCode($link)
    {
        try {
            if(preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $link, $output_array)) {
                return $output_array[5];
            }
        } catch (\Exception $e) {
            throw new VideoException("can't match vimeo code in given url", $e);
        }

    }

    /**
     * @param $link
     * @return mixed
     * @throws VideoException
     */
    public function isolateYoutubeVideoCode($link)
    {
        try {
            if (preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $link, $matches)) {
                return $matches[0];
            }
        } catch (\Exception $e) {
            throw new VideoException("can't match youtube code in given url", $e);
        }
    }

    /**
     * @param $link
     * @return bool
     * @throws VideoException
     */
    function isolateDailymotionVideoCode($link)
    {
        try {
            if (preg_match('!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $link, $matches)) {
                if (isset($matches[6])) {
                    return $matches[6];
                }
                if (isset($matches[4])) {
                    return $matches[4];
                }
                return $matches[2];
            }
            return false;
        }catch (\Exception $e) {
            throw new VideoException("can't match dailymotion code in given url", $e);
        }
    }

    /**
     * String helper.
     *
     * @param string $str string
     * @param string $sub substring
     *
     * @return bool
     */
    private function endsWith($str, $sub)
    {
        return substr($str, strlen($str) - strlen($sub)) === $sub;
    }

    /**
     * @param Media $media
     */
    public function saveMedia(Media $media)
    {
    }

    /**
     * @param Media $media
     */
    public function removeMedia(Media $media)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateMedia(Media $media)
    {
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getAddUrlFor(array $params = [])
    {
        return [
            'video' => [
                'path'   => 'VictoireMediaBundle_folder_videocreate',
                'params' => [
                    'folderId' => $params['folderId'],
                ],
            ],
        ];
    }

    /**
     * @param mixed $data
     *
     * @return Media
     */
    public function createNew($data)
    {
        $result = null;
        if (is_string($data)) {
            if (strpos($data, 'http') !== 0) {
                $data = 'http://'.$data;
            }
            $parsedUrl = parse_url($data);
            switch ($parsedUrl['host']) {
                case 'www.youtube.com':
                case 'youtube.com':
                    parse_str($parsedUrl['query'], $queryFields);
                    $code = $queryFields['v'];
                    $result = new Media();
                    $video = new RemoteVideoHelper($result);
                    $video->setType('youtube');
                    $video->setCode($code);
                    $result = $video->getMedia();
                    $result->setName('Youtube '.$code);
                    break;
                case 'www.vimeo.com':
                case 'vimeo.com':
                    $code = substr($parsedUrl['path'], 1);
                    $result = new Media();
                    $video = new RemoteVideoHelper($result);
                    $video->setType('vimeo');
                    $video->setCode($code);
                    $result = $video->getMedia();
                    $result->setName('Vimeo '.$code);
                    break;
                case 'www.dailymotion.com':
                case 'dailymotion.com':
                    $code = substr($parsedUrl['path'], 7);
                    $result = new Media();
                    $video = new RemoteVideoHelper($result);
                    $video->setType('dailymotion');
                    $video->setCode($code);
                    $result = $video->getMedia();
                    $result->setName('Dailymotion '.$code);
                    break;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowTemplate(Media $media)
    {
        return 'VictoireMediaBundle:Media\RemoteVideo:show.html.twig';
    }

    /**
     * @param Media  $media    The media entity
     * @param string $basepath The base path
     *
     * @return string
     */
    public function getImageUrl(Media $media, $basepath)
    {
        $helper = new RemoteVideoHelper($media);

        return $helper->getThumbnailUrl();
    }

    /**
     * @return array
     */
    public function getAddFolderActions()
    {
        return [
            self::TYPE => [
                'type' => self::TYPE,
                'name' => 'media.video.add', ],
        ];
    }
}
