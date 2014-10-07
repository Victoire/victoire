<?php

namespace Victoire\Bundle\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Victoire\Bundle\CoreBundle\DataFixtures\Fixtures;

/**
 * Load fixtures
 */
class LoadFixtureData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {

        // Load fixtures files
        $files = array();

        if ('test' != $this->container->getParameter('kernel.environment')) {
            $files['user']      = __DIR__ . '/User/user.yml';
            $files['folder']    = __DIR__ . '/Media/folder.yml';
            $files['template']  = __DIR__ . '/View/template.yml';
            $files['page']      = __DIR__ . '/View/page.yml';
            $files['errorPage'] = __DIR__ . '/View/errorPage.yml';
        }

        $objects = Fixtures::load($files, $manager, array(
                'providers'    => array($this),
                'locale'       => 'fr_FR',
                'persist_once' => false,
                'user_class'   => $this->container->getParameter('victoire_core.user_class'), //loader will change user class
        ));

        $manager->flush();
    }

    /**
     * Return random gender
     *
     * @return string
     */
    public function gender()
    {
        $genders = array(
            'male',
            'female'
        );

        return $genders[array_rand($genders)];
    }

    /**
     * Return an image path for a new image
     *
     * @param string  $dir    The upload dir.
     * @param integer $width  The image width.
     * @param integer $height The image height.
     * @param string  $type   The image type.
     *
     * @return string
     */
    public function image($dir, $width = null, $height = null, $type= '')
    {
        $originalWidth = $width ?: 'rand';
        $originalHeight = $height ?: 'rand';
        $rootDir = $this->container->get('kernel')->getRootDir() . '/../web';

        $existingImages = glob($rootDir . "/" .  $dir .'/*.png');
        // print_r($existingImages);
        if ($matches = preg_grep("/" . $originalWidth . "-" . $originalHeight . ".png/", $existingImages)) {
            if (count($matches) > 30) {
                $image = array_rand($matches);

                return $image;
            }
        }

        $width = $width ?: rand(100, 300);
        $height = $height ?: rand(100, 300);

        $fileName = uniqid();
        $imageName = sprintf($rootDir . '/%s/%s-%s-%s.png', $dir, $fileName, $originalWidth, $originalHeight);
        $image = sprintf('http://%s/%d/%d/%s', 'lorempixel.com', $width, $height, $type);

        if (! is_dir(dirname($imageName))) {
            mkdir(dirname($imageName), 0777, true);
        }
        file_put_contents($imageName, file_get_contents($image));
        $imagePath = $dir . '/' . $fileName . '.png';

        return $imagePath;
    }

    /**
     * Return an image path for a new image
     *
     * @param string $dir The upload dir.
     *
     * @return string
     */
    public function pdf($dir)
    {
        $rootDir = $this->container->get('kernel')->getRootDir() . '/../web';

        $fileName = uniqid();
        $pdfName = sprintf($rootDir . '/%s/%s.pdf', $dir, $fileName);
        $pdf = __DIR__ . "/lorem.pdf";

        if (! is_dir(dirname($pdfName))) {
            mkdir(dirname($pdfName), 0777, true);
        }
        file_put_contents($pdfName, file_get_contents($pdf));
        $pdfPath = $dir . '/' . $fileName . '.pdf';

        return $pdfPath;
    }

    /**
     * Remove all files from given folder
     *
     * @param string $folder Path of the folder to clear.
     *
     * @return void
     */
    public function clearFolder($folder)
    {
        if (is_dir($folder)) {
            // Open folder
            $openFolder = opendir($folder);

            // While folder is not empty
            while ($file = readdir($openFolder)) {
                if ($file != '.' && $file != '..') {
                    // Remove file
                    $recursiveDelete = function ($str) use (&$recursiveDelete) {
                        if (is_file($str)) {
                            return @unlink($str);
                        } elseif (is_dir($str)) {
                            $scan = glob(rtrim($str,'/').'/*');
                            foreach ($scan as $index=>$path) {
                                $recursiveDelete($path);
                            }

                            return @rmdir($str);
                        }
                    };
                    $recursiveDelete($folder . $file);
                }
            }

            // Close empty folder
            closedir($openFolder);
        }
    }

    /**
     * Replaces all question mark ('?') occurrences with a random letter uppercase
     *
     * @param  string $string String that needs to bet parsed.
     * @return string
     */
    public static function lexifyUpper($string = '????')
    {
        return strtoupper(preg_replace_callback('/\?/', 'static::randomLetter', $string));
    }

    /**
     * Returns a random letter from a to z
     *
     * @return string
     */
    public static function randomLetter()
    {
        return chr(mt_rand(97, 122));
    }

}
