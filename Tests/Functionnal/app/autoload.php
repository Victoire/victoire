<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!file_exists($file = __DIR__.'/../../../vendor/autoload.php')) {
    throw new \RuntimeException(sprintf('File %s not found. Did you install the dependencies ?', $file));
}
$loader = require $file;
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
