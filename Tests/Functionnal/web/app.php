<?php
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__.'/../app/AppKernel.php';
$kernel = new AppKernel('test', true);
$kernel->loadClassCache();
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
