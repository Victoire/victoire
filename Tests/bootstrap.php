<?php

namespace {
    error_reporting(error_reporting() & ~E_USER_DEPRECATED);
    $loader = require_once __DIR__.'/../vendor/autoload.php';
}

namespace Symfony\Component\HttpFoundation
{
    class ParameterBag implements \IteratorAggregate, \Countable
    {
        protected $parameters;

        public function __construct(array $parameters = [])
        {
            $this->parameters = $parameters;
        }

        public function all()
        {
            return $this->parameters;
        }

        public function keys()
        {
            return array_keys($this->parameters);
        }

        public function replace(array $parameters = [])
        {
            $this->parameters = $parameters;
        }

        public function add(array $parameters = [])
        {
            $this->parameters = array_replace($this->parameters, $parameters);
        }

        public function get($path, $default = null, $deep = false)
        {
            if (!$deep || false === $pos = strpos($path, '[')) {
                return array_key_exists($path, $this->parameters) ? $this->parameters[$path] : $default;
            }
            $root = substr($path, 0, $pos);
            if (!array_key_exists($root, $this->parameters)) {
                return $default;
            }
            $value = $this->parameters[$root];
            $currentKey = null;
            for ($i = $pos, $c = strlen($path); $i < $c; $i++) {
                $char = $path[$i];
                if ('[' === $char) {
                    if (null !== $currentKey) {
                        throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "[" at position %d.', $i));
                    }
                    $currentKey = '';
                } elseif (']' === $char) {
                    if (null === $currentKey) {
                        throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "]" at position %d.', $i));
                    }
                    if (!is_array($value) || !array_key_exists($currentKey, $value)) {
                        return $default;
                    }
                    $value = $value[$currentKey];
                    $currentKey = null;
                } else {
                    if (null === $currentKey) {
                        throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "%s" at position %d.', $char, $i));
                    }
                    $currentKey .= $char;
                }
            }
            if (null !== $currentKey) {
                throw new \InvalidArgumentException(sprintf('Malformed path. Path must end with "]".'));
            }

            return $value;
        }

        public function set($key, $value)
        {
            $this->parameters[$key] = $value;
        }

        public function has($key)
        {
            return array_key_exists($key, $this->parameters);
        }

        /**
         * @param string $key
         */
        public function remove($key)
        {
            unset($this->parameters[$key]);
        }

        public function getAlpha($key, $default = '', $deep = false)
        {
            return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default, $deep));
        }

        public function getAlnum($key, $default = '', $deep = false)
        {
            return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default, $deep));
        }

        public function getDigits($key, $default = '', $deep = false)
        {
            return str_replace(['-', '+'], '', $this->filter($key, $default, $deep, FILTER_SANITIZE_NUMBER_INT));
        }

        public function getInt($key, $default = 0, $deep = false)
        {
            return (int) $this->get($key, $default, $deep);
        }

        public function getBoolean($key, $default = false, $deep = false)
        {
            return $this->filter($key, $default, $deep, FILTER_VALIDATE_BOOLEAN);
        }

        /**
         * @param string|bool $default
         * @param int         $filter
         */
        public function filter($key, $default = null, $deep = false, $filter = FILTER_DEFAULT, $options = [])
        {
            $value = $this->get($key, $default, $deep);
            if (!is_array($options) && $options) {
                $options = ['flags' => $options];
            }
            if (is_array($value) && !isset($options['flags'])) {
                $options['flags'] = FILTER_REQUIRE_ARRAY;
            }

            return filter_var($value, $filter, $options);
        }

        public function getIterator()
        {
            return new \ArrayIterator($this->parameters);
        }

        public function count()
        {
            return count($this->parameters);
        }
    }
}

namespace Symfony\Component\HttpFoundation
{
    class HeaderBag implements \IteratorAggregate, \Countable
    {
        protected $headers = [];
        protected $cacheControl = [];

        public function __construct(array $headers = [])
        {
            foreach ($headers as $key => $values) {
                $this->set($key, $values);
            }
        }

        public function __toString()
        {
            if (!$this->headers) {
                return'';
            }
            $max = max(array_map('strlen', array_keys($this->headers))) + 1;
            $content = '';
            ksort($this->headers);
            foreach ($this->headers as $name => $values) {
                $name = implode('-', array_map('ucfirst', explode('-', $name)));
                foreach ($values as $value) {
                    $content .= sprintf("%-{$max}s %s\r\n", $name.':', $value);
                }
            }

            return $content;
        }

        public function all()
        {
            return $this->headers;
        }

        public function keys()
        {
            return array_keys($this->headers);
        }

        public function replace(array $headers = [])
        {
            $this->headers = [];
            $this->add($headers);
        }

        public function add(array $headers)
        {
            foreach ($headers as $key => $values) {
                $this->set($key, $values);
            }
        }

        public function get($key, $default = null, $first = true)
        {
            $key = strtr(strtolower($key), '_', '-');
            if (!array_key_exists($key, $this->headers)) {
                if (null === $default) {
                    return $first ? null : [];
                }

                return $first ? $default : [$default];
            }
            if ($first) {
                return count($this->headers[$key]) ? $this->headers[$key][0] : $default;
            }

            return $this->headers[$key];
        }

        public function set($key, $values, $replace = true)
        {
            $key = strtr(strtolower($key), '_', '-');
            $values = array_values((array) $values);
            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $values);
            }
            if ('cache-control' === $key) {
                $this->cacheControl = $this->parseCacheControl($values[0]);
            }
        }

        public function has($key)
        {
            return array_key_exists(strtr(strtolower($key), '_', '-'), $this->headers);
        }

        public function contains($key, $value)
        {
            return in_array($value, $this->get($key, null, false));
        }

        /**
         * @param string $key
         */
        public function remove($key)
        {
            $key = strtr(strtolower($key), '_', '-');
            unset($this->headers[$key]);
            if ('cache-control' === $key) {
                $this->cacheControl = [];
            }
        }

        /**
         * @param string $key
         */
        public function getDate($key, \DateTime $default = null)
        {
            if (null === $value = $this->get($key)) {
                return $default;
            }
            if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $value)) {
                throw new \RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
            }

            return $date;
        }

        /**
         * @param string $key
         */
        public function addCacheControlDirective($key, $value = true)
        {
            $this->cacheControl[$key] = $value;
            $this->set('Cache-Control', $this->getCacheControlHeader());
        }

        /**
         * @param string $key
         */
        public function hasCacheControlDirective($key)
        {
            return array_key_exists($key, $this->cacheControl);
        }

        public function getCacheControlDirective($key)
        {
            return array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
        }

        /**
         * @param string $key
         */
        public function removeCacheControlDirective($key)
        {
            unset($this->cacheControl[$key]);
            $this->set('Cache-Control', $this->getCacheControlHeader());
        }

        public function getIterator()
        {
            return new \ArrayIterator($this->headers);
        }

        public function count()
        {
            return count($this->headers);
        }

        protected function getCacheControlHeader()
        {
            $parts = [];
            ksort($this->cacheControl);
            foreach ($this->cacheControl as $key => $value) {
                if (true === $value) {
                    $parts[] = $key;
                } else {
                    if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                        $value = '"'.$value.'"';
                    }
                    $parts[] = "$key=$value";
                }
            }

            return implode(', ', $parts);
        }

        protected function parseCacheControl($header)
        {
            $cacheControl = [];
            preg_match_all('#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $cacheControl[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
            }

            return $cacheControl;
        }
    }
}

namespace Symfony\Component\HttpFoundation
{
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class FileBag extends ParameterBag
    {
        private static $fileKeys = ['error', 'name', 'size', 'tmp_name', 'type'];

        public function __construct(array $parameters = [])
        {
            $this->replace($parameters);
        }

        public function replace(array $files = [])
        {
            $this->parameters = [];
            $this->add($files);
        }

        public function set($key, $value)
        {
            if (!is_array($value) && !$value instanceof UploadedFile) {
                throw new \InvalidArgumentException('An uploaded file must be an array or an instance of UploadedFile.');
            }
            parent::set($key, $this->convertFileInformation($value));
        }

        public function add(array $files = [])
        {
            foreach ($files as $key => $file) {
                $this->set($key, $file);
            }
        }

        protected function convertFileInformation($file)
        {
            if ($file instanceof UploadedFile) {
                return $file;
            }
            $file = $this->fixPhpFilesArray($file);
            if (is_array($file)) {
                $keys = array_keys($file);
                sort($keys);
                if ($keys == self::$fileKeys) {
                    if (UPLOAD_ERR_NO_FILE == $file['error']) {
                        $file = null;
                    } else {
                        $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
                    }
                } else {
                    $file = array_map([$this, 'convertFileInformation'], $file);
                }
            }

            return $file;
        }

        protected function fixPhpFilesArray($data)
        {
            if (!is_array($data)) {
                return $data;
            }
            $keys = array_keys($data);
            sort($keys);
            if (self::$fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
                return $data;
            }
            $files = $data;
            foreach (self::$fileKeys as $k) {
                unset($files[$k]);
            }
            foreach ($data['name'] as $key => $name) {
                $files[$key] = $this->fixPhpFilesArray(['error' => $data['error'][$key], 'name' => $name, 'type' => $data['type'][$key], 'tmp_name' => $data['tmp_name'][$key], 'size' => $data['size'][$key],
                    ]);
            }

            return $files;
        }
    }
}

namespace Symfony\Component\HttpFoundation
{
    class ServerBag extends ParameterBag
    {
        public function getHeaders()
        {
            $headers = [];
            $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];
            foreach ($this->parameters as $key => $value) {
                if (0 === strpos($key, 'HTTP_')) {
                    $headers[substr($key, 5)] = $value;
                } elseif (isset($contentHeaders[$key])) {
                    $headers[$key] = $value;
                }
            }
            if (isset($this->parameters['PHP_AUTH_USER'])) {
                $headers['PHP_AUTH_USER'] = $this->parameters['PHP_AUTH_USER'];
                $headers['PHP_AUTH_PW'] = isset($this->parameters['PHP_AUTH_PW']) ? $this->parameters['PHP_AUTH_PW'] : '';
            } else {
                $authorizationHeader = null;
                if (isset($this->parameters['HTTP_AUTHORIZATION'])) {
                    $authorizationHeader = $this->parameters['HTTP_AUTHORIZATION'];
                } elseif (isset($this->parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                    $authorizationHeader = $this->parameters['REDIRECT_HTTP_AUTHORIZATION'];
                }
                if (null !== $authorizationHeader) {
                    if (0 === stripos($authorizationHeader, 'basic ')) {
                        $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
                        if (count($exploded) == 2) {
                            list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                        }
                    } elseif (empty($this->parameters['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest '))) {
                        $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                        $this->parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    }
                }
            }
            if (isset($headers['PHP_AUTH_USER'])) {
                $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
            } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
                $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
            }

            return $headers;
        }
    }
}

namespace Symfony\Component\HttpFoundation
{
    use Symfony\Component\HttpFoundation\Session\SessionInterface;

    class Request
    {
        const HEADER_CLIENT_IP = 'client_ip';
        const HEADER_CLIENT_HOST = 'client_host';
        const HEADER_CLIENT_PROTO = 'client_proto';
        const HEADER_CLIENT_PORT = 'client_port';
        const METHOD_HEAD = 'HEAD';
        const METHOD_GET = 'GET';
        const METHOD_POST = 'POST';
        const METHOD_PUT = 'PUT';
        const METHOD_PATCH = 'PATCH';
        const METHOD_DELETE = 'DELETE';
        const METHOD_PURGE = 'PURGE';
        const METHOD_OPTIONS = 'OPTIONS';
        const METHOD_TRACE = 'TRACE';
        const METHOD_CONNECT = 'CONNECT';
        protected static $trustedProxies = [];
        protected static $trustedHostPatterns = [];
        protected static $trustedHosts = [];
        protected static $trustedHeaders = [
            self::HEADER_CLIENT_IP    => 'X_FORWARDED_FOR',
            self::HEADER_CLIENT_HOST  => 'X_FORWARDED_HOST',
            self::HEADER_CLIENT_PROTO => 'X_FORWARDED_PROTO',
            self::HEADER_CLIENT_PORT  => 'X_FORWARDED_PORT',
        ];
        protected static $httpMethodParameterOverride = false;
        public $attributes;
        public $request;
        public $query;
        public $server;
        public $files;
        public $cookies;
        public $headers;
        protected $content;
        protected $languages;
        protected $charsets;
        protected $encodings;
        protected $acceptableContentTypes;
        protected $pathInfo;
        protected $requestUri;
        protected $baseUrl;
        protected $basePath;
        protected $method;
        protected $format;
        protected $session;
        protected $locale;
        protected $defaultLocale = 'en';
        protected static $formats;
        protected static $requestFactory;

        public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
        {
            $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        }

        public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
        {
            $this->request = new ParameterBag($request);
            $this->query = new ParameterBag($query);
            $this->attributes = new ParameterBag($attributes);
            $this->cookies = new ParameterBag($cookies);
            $this->files = new FileBag($files);
            $this->server = new ServerBag($server);
            $this->headers = new HeaderBag($this->server->getHeaders());
            $this->content = $content;
            $this->languages = null;
            $this->charsets = null;
            $this->encodings = null;
            $this->acceptableContentTypes = null;
            $this->pathInfo = null;
            $this->requestUri = null;
            $this->baseUrl = null;
            $this->basePath = null;
            $this->method = null;
            $this->format = null;
        }

        public static function createFromGlobals()
        {
            $server = $_SERVER;
            if ('cli-server' === php_sapi_name()) {
                if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                    $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
                }
                if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                    $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
                }
            }
            $request = self::createRequestFromFactory($_GET, $_POST, [], $_COOKIE, $_FILES, $server);
            if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
            ) {
                parse_str($request->getContent(), $data);
                $request->request = new ParameterBag($data);
            }

            return $request;
        }

        public static function create($uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
        {
            $server = array_replace(['SERVER_NAME' => 'localhost', 'SERVER_PORT' => 80, 'HTTP_HOST' => 'localhost', 'HTTP_USER_AGENT' => 'Symfony/2.X', 'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5', 'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7', 'REMOTE_ADDR' => '127.0.0.1', 'SCRIPT_NAME' => '', 'SCRIPT_FILENAME' => '', 'SERVER_PROTOCOL' => 'HTTP/1.1', 'REQUEST_TIME' => time(),
                ], $server);
            $server['PATH_INFO'] = '';
            $server['REQUEST_METHOD'] = strtoupper($method);
            $components = parse_url($uri);
            if (isset($components['host'])) {
                $server['SERVER_NAME'] = $components['host'];
                $server['HTTP_HOST'] = $components['host'];
            }
            if (isset($components['scheme'])) {
                if ('https' === $components['scheme']) {
                    $server['HTTPS'] = 'on';
                    $server['SERVER_PORT'] = 443;
                } else {
                    unset($server['HTTPS']);
                    $server['SERVER_PORT'] = 80;
                }
            }
            if (isset($components['port'])) {
                $server['SERVER_PORT'] = $components['port'];
                $server['HTTP_HOST'] = $server['HTTP_HOST'].':'.$components['port'];
            }
            if (isset($components['user'])) {
                $server['PHP_AUTH_USER'] = $components['user'];
            }
            if (isset($components['pass'])) {
                $server['PHP_AUTH_PW'] = $components['pass'];
            }
            if (!isset($components['path'])) {
                $components['path'] = '/';
            }
            switch (strtoupper($method)) {
                case'POST':
                case'PUT':
                case'DELETE':
                    if (!isset($server['CONTENT_TYPE'])) {
                        $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                    }
                case'PATCH':
                    $request = $parameters;
                    $query = [];
                    break;
                default:
                    $request = [];
                    $query = $parameters;
                    break;
            }
            $queryString = '';
            if (isset($components['query'])) {
                parse_str(html_entity_decode($components['query']), $qs);
                if ($query) {
                    $query = array_replace($qs, $query);
                    $queryString = http_build_query($query, '', '&');
                } else {
                    $query = $qs;
                    $queryString = $components['query'];
                }
            } elseif ($query) {
                $queryString = http_build_query($query, '', '&');
            }
            $server['REQUEST_URI'] = $components['path'].('' !== $queryString ? '?'.$queryString : '');
            $server['QUERY_STRING'] = $queryString;

            return self::createRequestFromFactory($query, $request, [], $cookies, $files, $server, $content);
        }

        public static function setFactory($callable)
        {
            self::$requestFactory = $callable;
        }

        public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
        {
            $dup = clone $this;
            if ($query !== null) {
                $dup->query = new ParameterBag($query);
            }
            if ($request !== null) {
                $dup->request = new ParameterBag($request);
            }
            if ($attributes !== null) {
                $dup->attributes = new ParameterBag($attributes);
            }
            if ($cookies !== null) {
                $dup->cookies = new ParameterBag($cookies);
            }
            if ($files !== null) {
                $dup->files = new FileBag($files);
            }
            if ($server !== null) {
                $dup->server = new ServerBag($server);
                $dup->headers = new HeaderBag($dup->server->getHeaders());
            }
            $dup->languages = null;
            $dup->charsets = null;
            $dup->encodings = null;
            $dup->acceptableContentTypes = null;
            $dup->pathInfo = null;
            $dup->requestUri = null;
            $dup->baseUrl = null;
            $dup->basePath = null;
            $dup->method = null;
            $dup->format = null;
            if (!$dup->get('_format') && $this->get('_format')) {
                $dup->attributes->set('_format', $this->get('_format'));
            }
            if (!$dup->getRequestFormat(null)) {
                $dup->setRequestFormat($this->getRequestFormat(null));
            }

            return $dup;
        }

        public function __clone()
        {
            $this->query = clone $this->query;
            $this->request = clone $this->request;
            $this->attributes = clone $this->attributes;
            $this->cookies = clone $this->cookies;
            $this->files = clone $this->files;
            $this->server = clone $this->server;
            $this->headers = clone $this->headers;
        }

        public function __toString()
        {
            return
                sprintf('%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get('SERVER_PROTOCOL'))."\r\n".
                $this->headers."\r\n".
                $this->getContent();
        }

        public function overrideGlobals()
        {
            $this->server->set('QUERY_STRING', static::normalizeQueryString(http_build_query($this->query->all(), null, '&')));
            $_GET = $this->query->all();
            $_POST = $this->request->all();
            $_SERVER = $this->server->all();
            $_COOKIE = $this->cookies->all();
            foreach ($this->headers->all() as $key => $value) {
                $key = strtoupper(str_replace('-', '_', $key));
                if (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                    $_SERVER[$key] = implode(', ', $value);
                } else {
                    $_SERVER['HTTP_'.$key] = implode(', ', $value);
                }
            }
            $request = ['g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE];
            $requestOrder = ini_get('request_order') ?: ini_get('variables_order');
            $requestOrder = preg_replace('#[^cgp]#', '', strtolower($requestOrder)) ?: 'gp';
            $_REQUEST = [];
            foreach (str_split($requestOrder) as $order) {
                $_REQUEST = array_merge($_REQUEST, $request[$order]);
            }
        }

        public static function setTrustedProxies(array $proxies)
        {
            self::$trustedProxies = $proxies;
        }

        public static function getTrustedProxies()
        {
            return self::$trustedProxies;
        }

        public static function setTrustedHosts(array $hostPatterns)
        {
            self::$trustedHostPatterns = array_map(function ($hostPattern) {
                    return sprintf('#%s#i', $hostPattern);
                }, $hostPatterns);
            self::$trustedHosts = [];
        }

        public static function getTrustedHosts()
        {
            return self::$trustedHostPatterns;
        }

        public static function setTrustedHeaderName($key, $value)
        {
            if (!array_key_exists($key, self::$trustedHeaders)) {
                throw new \InvalidArgumentException(sprintf('Unable to set the trusted header name for key "%s".', $key));
            }
            self::$trustedHeaders[$key] = $value;
        }

        public static function getTrustedHeaderName($key)
        {
            if (!array_key_exists($key, self::$trustedHeaders)) {
                throw new \InvalidArgumentException(sprintf('Unable to get the trusted header name for key "%s".', $key));
            }

            return self::$trustedHeaders[$key];
        }

        public static function normalizeQueryString($qs)
        {
            if ('' == $qs) {
                return'';
            }
            $parts = [];
            $order = [];
            foreach (explode('&', $qs) as $param) {
                if ('' === $param || '=' === $param[0]) {
                    continue;
                }
                $keyValuePair = explode('=', $param, 2);
                $parts[] = isset($keyValuePair[1]) ?
                    rawurlencode(urldecode($keyValuePair[0])).'='.rawurlencode(urldecode($keyValuePair[1])) : rawurlencode(urldecode($keyValuePair[0]));
                $order[] = urldecode($keyValuePair[0]);
            }
            array_multisort($order, SORT_ASC, $parts);

            return implode('&', $parts);
        }

        public static function enableHttpMethodParameterOverride()
        {
            self::$httpMethodParameterOverride = true;
        }

        public static function getHttpMethodParameterOverride()
        {
            return self::$httpMethodParameterOverride;
        }

        /**
         * @param string $key
         * @param string $default
         */
        public function get($key, $default = null, $deep = false)
        {
            if ($this !== $result = $this->query->get($key, $this, $deep)) {
                return $result;
            }
            if ($this !== $result = $this->attributes->get($key, $this, $deep)) {
                return $result;
            }
            if ($this !== $result = $this->request->get($key, $this, $deep)) {
                return $result;
            }

            return $default;
        }

        public function getSession()
        {
            return $this->session;
        }

        public function hasPreviousSession()
        {
            return $this->hasSession() && $this->cookies->has($this->session->getName());
        }

        public function hasSession()
        {
            return null !== $this->session;
        }

        public function setSession(SessionInterface $session)
        {
            $this->session = $session;
        }

        public function getClientIps()
        {
            $ip = $this->server->get('REMOTE_ADDR');
            if (!$this->isFromTrustedProxy()) {
                return [$ip];
            }
            if (!self::$trustedHeaders[self::HEADER_CLIENT_IP] || !$this->headers->has(self::$trustedHeaders[self::HEADER_CLIENT_IP])) {
                return [$ip];
            }
            $clientIps = array_map('trim', explode(',', $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_IP])));
            $clientIps[] = $ip;
            $ip = $clientIps[0];
            foreach ($clientIps as $key => $clientIp) {
                if (preg_match('{((?:\d+\.){3}\d+)\:\d+}', $clientIp, $match)) {
                    $clientIps[$key] = $clientIp = $match[1];
                }
                if (IpUtils::checkIp($clientIp, self::$trustedProxies)) {
                    unset($clientIps[$key]);
                }
            }

            return $clientIps ? array_reverse($clientIps) : [$ip];
        }

        /**
         * @return string
         */
        public function getClientIp()
        {
            $ipAddresses = $this->getClientIps();

            return $ipAddresses[0];
        }

        public function getScriptName()
        {
            return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
        }

        public function getPathInfo()
        {
            if (null === $this->pathInfo) {
                $this->pathInfo = $this->preparePathInfo();
            }

            return $this->pathInfo;
        }

        public function getBasePath()
        {
            if (null === $this->basePath) {
                $this->basePath = $this->prepareBasePath();
            }

            return $this->basePath;
        }

        public function getBaseUrl()
        {
            if (null === $this->baseUrl) {
                $this->baseUrl = $this->prepareBaseUrl();
            }

            return $this->baseUrl;
        }

        public function getScheme()
        {
            return $this->isSecure() ? 'https' : 'http';
        }

        public function getPort()
        {
            if ($this->isFromTrustedProxy()) {
                if (self::$trustedHeaders[self::HEADER_CLIENT_PORT] && $port = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PORT])) {
                    return $port;
                }
                if (self::$trustedHeaders[self::HEADER_CLIENT_PROTO] && 'https' === $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PROTO], 'http')) {
                    return 443;
                }
            }
            if ($host = $this->headers->get('HOST')) {
                if ($host[0] === '[') {
                    $pos = strpos($host, ':', strrpos($host, ']'));
                } else {
                    $pos = strrpos($host, ':');
                }
                if (false !== $pos) {
                    return (int) substr($host, $pos + 1);
                }

                return'https' === $this->getScheme() ? 443 : 80;
            }

            return $this->server->get('SERVER_PORT');
        }

        public function getUser()
        {
            return $this->headers->get('PHP_AUTH_USER');
        }

        public function getPassword()
        {
            return $this->headers->get('PHP_AUTH_PW');
        }

        public function getUserInfo()
        {
            $userinfo = $this->getUser();
            $pass = $this->getPassword();
            if ('' != $pass) {
                $userinfo .= ":$pass";
            }

            return $userinfo;
        }

        public function getHttpHost()
        {
            $scheme = $this->getScheme();
            $port = $this->getPort();
            if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
                return $this->getHost();
            }

            return $this->getHost().':'.$port;
        }

        public function getRequestUri()
        {
            if (null === $this->requestUri) {
                $this->requestUri = $this->prepareRequestUri();
            }

            return $this->requestUri;
        }

        public function getSchemeAndHttpHost()
        {
            return $this->getScheme().'://'.$this->getHttpHost();
        }

        public function getUri()
        {
            if (null !== $qs = $this->getQueryString()) {
                $qs = '?'.$qs;
            }

            return $this->getSchemeAndHttpHost().$this->getBaseUrl().$this->getPathInfo().$qs;
        }

        public function getUriForPath($path)
        {
            return $this->getSchemeAndHttpHost().$this->getBaseUrl().$path;
        }

        public function getQueryString()
        {
            $qs = static::normalizeQueryString($this->server->get('QUERY_STRING'));

            return'' === $qs ? null : $qs;
        }

        public function isSecure()
        {
            if ($this->isFromTrustedProxy() && self::$trustedHeaders[self::HEADER_CLIENT_PROTO] && $proto = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PROTO])) {
                return in_array(strtolower(current(explode(',', $proto))), ['https', 'on', 'ssl', '1']);
            }
            $https = $this->server->get('HTTPS');

            return !empty($https) && 'off' !== strtolower($https);
        }

        public function getHost()
        {
            if ($this->isFromTrustedProxy() && self::$trustedHeaders[self::HEADER_CLIENT_HOST] && $host = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_HOST])) {
                $elements = explode(',', $host);
                $host = $elements[count($elements) - 1];
            } elseif (!$host = $this->headers->get('HOST')) {
                if (!$host = $this->server->get('SERVER_NAME')) {
                    $host = $this->server->get('SERVER_ADDR', '');
                }
            }
            $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
            if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
                throw new \UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
            }
            if (count(self::$trustedHostPatterns) > 0) {
                if (in_array($host, self::$trustedHosts)) {
                    return $host;
                }
                foreach (self::$trustedHostPatterns as $pattern) {
                    if (preg_match($pattern, $host)) {
                        self::$trustedHosts[] = $host;

                        return $host;
                    }
                }
                throw new \UnexpectedValueException(sprintf('Untrusted Host "%s"', $host));
            }

            return $host;
        }

        public function setMethod($method)
        {
            $this->method = null;
            $this->server->set('REQUEST_METHOD', $method);
        }

        public function getMethod()
        {
            if (null === $this->method) {
                $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
                if ('POST' === $this->method) {
                    if ($method = $this->headers->get('X-HTTP-METHOD-OVERRIDE')) {
                        $this->method = strtoupper($method);
                    } elseif (self::$httpMethodParameterOverride) {
                        $this->method = strtoupper($this->request->get('_method', $this->query->get('_method', 'POST')));
                    }
                }
            }

            return $this->method;
        }

        public function getRealMethod()
        {
            return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
        }

        public function getMimeType($format)
        {
            if (null === static::$formats) {
                static::initializeFormats();
            }

            return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
        }

        public function getFormat($mimeType)
        {
            if (false !== $pos = strpos($mimeType, ';')) {
                $mimeType = substr($mimeType, 0, $pos);
            }
            if (null === static::$formats) {
                static::initializeFormats();
            }
            foreach (static::$formats as $format => $mimeTypes) {
                if (in_array($mimeType, (array) $mimeTypes)) {
                    return $format;
                }
            }
        }

        public function setFormat($format, $mimeTypes)
        {
            if (null === static::$formats) {
                static::initializeFormats();
            }
            static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : [$mimeTypes];
        }

        public function getRequestFormat($default = 'html')
        {
            if (null === $this->format) {
                $this->format = $this->get('_format', $default);
            }

            return $this->format;
        }

        public function setRequestFormat($format)
        {
            $this->format = $format;
        }

        public function getContentType()
        {
            return $this->getFormat($this->headers->get('CONTENT_TYPE'));
        }

        public function setDefaultLocale($locale)
        {
            $this->defaultLocale = $locale;
            if (null === $this->locale) {
                $this->setPhpDefaultLocale($locale);
            }
        }

        public function getDefaultLocale()
        {
            return $this->defaultLocale;
        }

        public function setLocale($locale)
        {
            $this->setPhpDefaultLocale($this->locale = $locale);
        }

        public function getLocale()
        {
            return null === $this->locale ? $this->defaultLocale : $this->locale;
        }

        /**
         * @param string $method
         */
        public function isMethod($method)
        {
            return $this->getMethod() === strtoupper($method);
        }

        public function isMethodSafe()
        {
            return in_array($this->getMethod(), ['GET', 'HEAD']);
        }

        /**
         * @return string
         */
        public function getContent($asResource = false)
        {
            if (false === $this->content || (true === $asResource && null !== $this->content)) {
                throw new \LogicException('getContent() can only be called once when using the resource return type.');
            }
            if (true === $asResource) {
                $this->content = false;

                return fopen('php://input', 'rb');
            }
            if (null === $this->content) {
                $this->content = file_get_contents('php://input');
            }

            return $this->content;
        }

        public function getETags()
        {
            return preg_split('/\s*,\s*/', $this->headers->get('if_none_match'), null, PREG_SPLIT_NO_EMPTY);
        }

        public function isNoCache()
        {
            return $this->headers->hasCacheControlDirective('no-cache') || 'no-cache' == $this->headers->get('Pragma');
        }

        public function getPreferredLanguage(array $locales = null)
        {
            $preferredLanguages = $this->getLanguages();
            if (empty($locales)) {
                return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
            }
            if (!$preferredLanguages) {
                return $locales[0];
            }
            $extendedPreferredLanguages = [];
            foreach ($preferredLanguages as $language) {
                $extendedPreferredLanguages[] = $language;
                if (false !== $position = strpos($language, '_')) {
                    $superLanguage = substr($language, 0, $position);
                    if (!in_array($superLanguage, $preferredLanguages)) {
                        $extendedPreferredLanguages[] = $superLanguage;
                    }
                }
            }
            $preferredLanguages = array_values(array_intersect($extendedPreferredLanguages, $locales));

            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
        }

        public function getLanguages()
        {
            if (null !== $this->languages) {
                return $this->languages;
            }
            $languages = AcceptHeader::fromString($this->headers->get('Accept-Language'))->all();
            $this->languages = [];
            foreach ($languages as $lang => $acceptHeaderItem) {
                if (false !== strpos($lang, '-')) {
                    $codes = explode('-', $lang);
                    if ('i' === $codes[0]) {
                        if (count($codes) > 1) {
                            $lang = $codes[1];
                        }
                    } else {
                        for ($i = 0, $max = count($codes); $i < $max; $i++) {
                            if ($i === 0) {
                                $lang = strtolower($codes[0]);
                            } else {
                                $lang .= '_'.strtoupper($codes[$i]);
                            }
                        }
                    }
                }
                $this->languages[] = $lang;
            }

            return $this->languages;
        }

        public function getCharsets()
        {
            if (null !== $this->charsets) {
                return $this->charsets;
            }

            return $this->charsets = array_keys(AcceptHeader::fromString($this->headers->get('Accept-Charset'))->all());
        }

        public function getEncodings()
        {
            if (null !== $this->encodings) {
                return $this->encodings;
            }

            return $this->encodings = array_keys(AcceptHeader::fromString($this->headers->get('Accept-Encoding'))->all());
        }

        public function getAcceptableContentTypes()
        {
            if (null !== $this->acceptableContentTypes) {
                return $this->acceptableContentTypes;
            }

            return $this->acceptableContentTypes = array_keys(AcceptHeader::fromString($this->headers->get('Accept'))->all());
        }

        public function isXmlHttpRequest()
        {
            return'XMLHttpRequest' == $this->headers->get('X-Requested-With');
        }

        protected function prepareRequestUri()
        {
            $requestUri = '';
            if ($this->headers->has('X_ORIGINAL_URL')) {
                $requestUri = $this->headers->get('X_ORIGINAL_URL');
                $this->headers->remove('X_ORIGINAL_URL');
                $this->server->remove('HTTP_X_ORIGINAL_URL');
                $this->server->remove('UNENCODED_URL');
                $this->server->remove('IIS_WasUrlRewritten');
            } elseif ($this->headers->has('X_REWRITE_URL')) {
                $requestUri = $this->headers->get('X_REWRITE_URL');
                $this->headers->remove('X_REWRITE_URL');
            } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
                $requestUri = $this->server->get('UNENCODED_URL');
                $this->server->remove('UNENCODED_URL');
                $this->server->remove('IIS_WasUrlRewritten');
            } elseif ($this->server->has('REQUEST_URI')) {
                $requestUri = $this->server->get('REQUEST_URI');
                $schemeAndHttpHost = $this->getSchemeAndHttpHost();
                if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                    $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
                }
            } elseif ($this->server->has('ORIG_PATH_INFO')) {
                $requestUri = $this->server->get('ORIG_PATH_INFO');
                if ('' != $this->server->get('QUERY_STRING')) {
                    $requestUri .= '?'.$this->server->get('QUERY_STRING');
                }
                $this->server->remove('ORIG_PATH_INFO');
            }
            $this->server->set('REQUEST_URI', $requestUri);

            return $requestUri;
        }

        protected function prepareBaseUrl()
        {
            $filename = basename($this->server->get('SCRIPT_FILENAME'));
            if (basename($this->server->get('SCRIPT_NAME')) === $filename) {
                $baseUrl = $this->server->get('SCRIPT_NAME');
            } elseif (basename($this->server->get('PHP_SELF')) === $filename) {
                $baseUrl = $this->server->get('PHP_SELF');
            } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $filename) {
                $baseUrl = $this->server->get('ORIG_SCRIPT_NAME');
            } else {
                $path = $this->server->get('PHP_SELF', '');
                $file = $this->server->get('SCRIPT_FILENAME', '');
                $segs = explode('/', trim($file, '/'));
                $segs = array_reverse($segs);
                $index = 0;
                $last = count($segs);
                $baseUrl = '';
                do {
                    $seg = $segs[$index];
                    $baseUrl = '/'.$seg.$baseUrl;
                    ++$index;
                } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
            }
            $requestUri = $this->getRequestUri();
            if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
                return $prefix;
            }
            if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl).'/')) {
                return rtrim($prefix, '/');
            }
            $truncatedRequestUri = $requestUri;
            if (false !== $pos = strpos($requestUri, '?')) {
                $truncatedRequestUri = substr($requestUri, 0, $pos);
            }
            $basename = basename($baseUrl);
            if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
                return'';
            }
            if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && $pos !== 0) {
                $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
            }

            return rtrim($baseUrl, '/');
        }

        protected function prepareBasePath()
        {
            $filename = basename($this->server->get('SCRIPT_FILENAME'));
            $baseUrl = $this->getBaseUrl();
            if (empty($baseUrl)) {
                return'';
            }
            if (basename($baseUrl) === $filename) {
                $basePath = dirname($baseUrl);
            } else {
                $basePath = $baseUrl;
            }
            if ('\\' === DIRECTORY_SEPARATOR) {
                $basePath = str_replace('\\', '/', $basePath);
            }

            return rtrim($basePath, '/');
        }

        protected function preparePathInfo()
        {
            $baseUrl = $this->getBaseUrl();
            if (null === ($requestUri = $this->getRequestUri())) {
                return'/';
            }
            $pathInfo = '/';
            if ($pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            if (null !== $baseUrl && false === $pathInfo = substr($requestUri, strlen($baseUrl))) {
                return'/';
            } elseif (null === $baseUrl) {
                return $requestUri;
            }

            return (string) $pathInfo;
        }

        protected static function initializeFormats()
        {
            static::$formats = ['html' => ['text/html', 'application/xhtml+xml'], 'txt' => ['text/plain'], 'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'], 'css' => ['text/css'], 'json' => ['application/json', 'application/x-json'], 'xml' => ['text/xml', 'application/xml', 'application/x-xml'], 'rdf' => ['application/rdf+xml'], 'atom' => ['application/atom+xml'], 'rss' => ['application/rss+xml'], 'form' => ['application/x-www-form-urlencoded'],
            ];
        }

        private function setPhpDefaultLocale($locale)
        {
            try {
                if (class_exists('Locale', false)) {
                    \Locale::setDefault($locale);
                }
            } catch (\Exception $e) {
            }
        }

        private function getUrlencodedPrefix($string, $prefix)
        {
            if (0 !== strpos(rawurldecode($string), $prefix)) {
                return false;
            }
            $len = strlen($prefix);
            if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
                return $match[0];
            }

            return false;
        }

        private static function createRequestFromFactory(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
        {
            if (self::$requestFactory) {
                $request = call_user_func(self::$requestFactory, $query, $request, $attributes, $cookies, $files, $server, $content);
                if (!$request instanceof self) {
                    throw new \LogicException('The Request factory must return an instance of Symfony\Component\HttpFoundation\Request.');
                }

                return $request;
            }

            return new static($query, $request, $attributes, $cookies, $files, $server, $content);
        }

        private function isFromTrustedProxy()
        {
            return self::$trustedProxies && IpUtils::checkIp($this->server->get('REMOTE_ADDR'), self::$trustedProxies);
        }
    }
}

namespace Symfony\Component\HttpFoundation
{
    class Response
    {
        const HTTP_CONTINUE = 100;
        const HTTP_SWITCHING_PROTOCOLS = 101;
        const HTTP_PROCESSING = 102;
        const HTTP_OK = 200;
        const HTTP_CREATED = 201;
        const HTTP_ACCEPTED = 202;
        const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
        const HTTP_NO_CONTENT = 204;
        const HTTP_RESET_CONTENT = 205;
        const HTTP_PARTIAL_CONTENT = 206;
        const HTTP_MULTI_STATUS = 207;
        const HTTP_ALREADY_REPORTED = 208;
        const HTTP_IM_USED = 226;
        const HTTP_MULTIPLE_CHOICES = 300;
        const HTTP_MOVED_PERMANENTLY = 301;
        const HTTP_FOUND = 302;
        const HTTP_SEE_OTHER = 303;
        const HTTP_NOT_MODIFIED = 304;
        const HTTP_USE_PROXY = 305;
        const HTTP_RESERVED = 306;
        const HTTP_TEMPORARY_REDIRECT = 307;
        const HTTP_PERMANENTLY_REDIRECT = 308;
        const HTTP_BAD_REQUEST = 400;
        const HTTP_UNAUTHORIZED = 401;
        const HTTP_PAYMENT_REQUIRED = 402;
        const HTTP_FORBIDDEN = 403;
        const HTTP_NOT_FOUND = 404;
        const HTTP_METHOD_NOT_ALLOWED = 405;
        const HTTP_NOT_ACCEPTABLE = 406;
        const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
        const HTTP_REQUEST_TIMEOUT = 408;
        const HTTP_CONFLICT = 409;
        const HTTP_GONE = 410;
        const HTTP_LENGTH_REQUIRED = 411;
        const HTTP_PRECONDITION_FAILED = 412;
        const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
        const HTTP_REQUEST_URI_TOO_LONG = 414;
        const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
        const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
        const HTTP_EXPECTATION_FAILED = 417;
        const HTTP_I_AM_A_TEAPOT = 418;
        const HTTP_UNPROCESSABLE_ENTITY = 422;
        const HTTP_LOCKED = 423;
        const HTTP_FAILED_DEPENDENCY = 424;
        const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;
        const HTTP_UPGRADE_REQUIRED = 426;
        const HTTP_PRECONDITION_REQUIRED = 428;
        const HTTP_TOO_MANY_REQUESTS = 429;
        const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
        const HTTP_INTERNAL_SERVER_ERROR = 500;
        const HTTP_NOT_IMPLEMENTED = 501;
        const HTTP_BAD_GATEWAY = 502;
        const HTTP_SERVICE_UNAVAILABLE = 503;
        const HTTP_GATEWAY_TIMEOUT = 504;
        const HTTP_VERSION_NOT_SUPPORTED = 505;
        const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
        const HTTP_INSUFFICIENT_STORAGE = 507;
        const HTTP_LOOP_DETECTED = 508;
        const HTTP_NOT_EXTENDED = 510;
        const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
        public $headers;
        protected $content;
        protected $version;
        protected $statusCode;
        protected $statusText;
        protected $charset;
        public static $statusTexts = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', 200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', 208 => 'Already Reported', 226 => 'IM Used', 300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect', 400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Reserved for WebDAV advanced collections expired proposal', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 510 => 'Not Extended', 511 => 'Network Authentication Required',];

        public function __construct($content = '', $status = 200, $headers = [])
        {
            $this->headers = new ResponseHeaderBag($headers);
            $this->setContent($content);
            $this->setStatusCode($status);
            $this->setProtocolVersion('1.0');
            if (!$this->headers->has('Date')) {
                $this->setDate(new \DateTime(null, new \DateTimeZone('UTC')));
            }
        }

        public static function create($content = '', $status = 200, $headers = [])
        {
            return new static($content, $status, $headers);
        }

        public function __toString()
        {
            return
                sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
                $this->headers."\r\n".
                $this->getContent();
        }

        public function __clone()
        {
            $this->headers = clone $this->headers;
        }

        public function prepare(Request $request)
        {
            $headers = $this->headers;
            if ($this->isInformational() || $this->isEmpty()) {
                $this->setContent(null);
                $headers->remove('Content-Type');
                $headers->remove('Content-Length');
            } else {
                if (!$headers->has('Content-Type')) {
                    $format = $request->getRequestFormat();
                    if (null !== $format && $mimeType = $request->getMimeType($format)) {
                        $headers->set('Content-Type', $mimeType);
                    }
                }
                $charset = $this->charset ?: 'UTF-8';
                if (!$headers->has('Content-Type')) {
                    $headers->set('Content-Type', 'text/html; charset='.$charset);
                } elseif (0 === stripos($headers->get('Content-Type'), 'text/') && false === stripos($headers->get('Content-Type'), 'charset')) {
                    $headers->set('Content-Type', $headers->get('Content-Type').'; charset='.$charset);
                }
                if ($headers->has('Transfer-Encoding')) {
                    $headers->remove('Content-Length');
                }
                if ($request->isMethod('HEAD')) {
                    $length = $headers->get('Content-Length');
                    $this->setContent(null);
                    if ($length) {
                        $headers->set('Content-Length', $length);
                    }
                }
            }
            if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
                $this->setProtocolVersion('1.1');
            }
            if ('1.0' == $this->getProtocolVersion() && 'no-cache' == $this->headers->get('Cache-Control')) {
                $this->headers->set('pragma', 'no-cache');
                $this->headers->set('expires', -1);
            }
            $this->ensureIEOverSSLCompatibility($request);

            return $this;
        }

        public function sendHeaders()
        {
            if (headers_sent()) {
                return $this;
            }
            header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);
            foreach ($this->headers->allPreserveCase() as $name => $values) {
                foreach ($values as $value) {
                    header($name.': '.$value, false, $this->statusCode);
                }
            }
            foreach ($this->headers->getCookies() as $cookie) {
                setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
            }

            return $this;
        }

        public function sendContent()
        {
            echo $this->content;

            return $this;
        }

        public function send()
        {
            $this->sendHeaders();
            $this->sendContent();
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } elseif ('cli' !== PHP_SAPI) {
                static::closeOutputBuffers(0, true);
            }

            return $this;
        }

        /**
         * @param string|null $content
         */
        public function setContent($content)
        {
            if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([$content, '__toString'])) {
                throw new \UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
            }
            $this->content = (string) $content;

            return $this;
        }

        public function getContent()
        {
            return $this->content;
        }

        /**
         * @param string $version
         */
        public function setProtocolVersion($version)
        {
            $this->version = $version;

            return $this;
        }

        public function getProtocolVersion()
        {
            return $this->version;
        }

        public function setStatusCode($code, $text = null)
        {
            $this->statusCode = $code = (int) $code;
            if ($this->isInvalid()) {
                throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
            }
            if (null === $text) {
                $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : '';

                return $this;
            }
            if (false === $text) {
                $this->statusText = '';

                return $this;
            }
            $this->statusText = $text;

            return $this;
        }

        public function getStatusCode()
        {
            return $this->statusCode;
        }

        public function setCharset($charset)
        {
            $this->charset = $charset;

            return $this;
        }

        public function getCharset()
        {
            return $this->charset;
        }

        public function isCacheable()
        {
            if (!in_array($this->statusCode, [200, 203, 300, 301, 302, 404, 410])) {
                return false;
            }
            if ($this->headers->hasCacheControlDirective('no-store') || $this->headers->getCacheControlDirective('private')) {
                return false;
            }

            return $this->isValidateable() || $this->isFresh();
        }

        public function isFresh()
        {
            return $this->getTtl() > 0;
        }

        public function isValidateable()
        {
            return $this->headers->has('Last-Modified') || $this->headers->has('ETag');
        }

        public function setPrivate()
        {
            $this->headers->removeCacheControlDirective('public');
            $this->headers->addCacheControlDirective('private');

            return $this;
        }

        public function setPublic()
        {
            $this->headers->addCacheControlDirective('public');
            $this->headers->removeCacheControlDirective('private');

            return $this;
        }

        public function mustRevalidate()
        {
            return $this->headers->hasCacheControlDirective('must-revalidate') || $this->headers->has('proxy-revalidate');
        }

        public function getDate()
        {
            return $this->headers->getDate('Date', new \DateTime());
        }

        public function setDate(\DateTime $date)
        {
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Date', $date->format('D, d M Y H:i:s').' GMT');

            return $this;
        }

        public function getAge()
        {
            if (null !== $age = $this->headers->get('Age')) {
                return (int) $age;
            }

            return max(time() - $this->getDate()->format('U'), 0);
        }

        public function expire()
        {
            if ($this->isFresh()) {
                $this->headers->set('Age', $this->getMaxAge());
            }

            return $this;
        }

        public function getExpires()
        {
            try {
                return $this->headers->getDate('Expires');
            } catch (\RuntimeException $e) {
                return \DateTime::createFromFormat(DATE_RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
            }
        }

        public function setExpires(\DateTime $date = null)
        {
            if (null === $date) {
                $this->headers->remove('Expires');
            } else {
                $date = clone $date;
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->headers->set('Expires', $date->format('D, d M Y H:i:s').' GMT');
            }

            return $this;
        }

        public function getMaxAge()
        {
            if ($this->headers->hasCacheControlDirective('s-maxage')) {
                return (int) $this->headers->getCacheControlDirective('s-maxage');
            }
            if ($this->headers->hasCacheControlDirective('max-age')) {
                return (int) $this->headers->getCacheControlDirective('max-age');
            }
            if (null !== $this->getExpires()) {
                return $this->getExpires()->format('U') - $this->getDate()->format('U');
            }
        }

        public function setMaxAge($value)
        {
            $this->headers->addCacheControlDirective('max-age', $value);

            return $this;
        }

        public function setSharedMaxAge($value)
        {
            $this->setPublic();
            $this->headers->addCacheControlDirective('s-maxage', $value);

            return $this;
        }

        public function getTtl()
        {
            if (null !== $maxAge = $this->getMaxAge()) {
                return $maxAge - $this->getAge();
            }
        }

        public function setTtl($seconds)
        {
            $this->setSharedMaxAge($this->getAge() + $seconds);

            return $this;
        }

        public function setClientTtl($seconds)
        {
            $this->setMaxAge($this->getAge() + $seconds);

            return $this;
        }

        public function getLastModified()
        {
            return $this->headers->getDate('Last-Modified');
        }

        public function setLastModified(\DateTime $date = null)
        {
            if (null === $date) {
                $this->headers->remove('Last-Modified');
            } else {
                $date = clone $date;
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->headers->set('Last-Modified', $date->format('D, d M Y H:i:s').' GMT');
            }

            return $this;
        }

        public function getEtag()
        {
            return $this->headers->get('ETag');
        }

        public function setEtag($etag = null, $weak = false)
        {
            if (null === $etag) {
                $this->headers->remove('Etag');
            } else {
                if (0 !== strpos($etag, '"')) {
                    $etag = '"'.$etag.'"';
                }
                $this->headers->set('ETag', (true === $weak ? 'W/' : '').$etag);
            }

            return $this;
        }

        public function setCache(array $options)
        {
            if ($diff = array_diff(array_keys($options), ['etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public'])) {
                throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_values($diff))));
            }
            if (isset($options['etag'])) {
                $this->setEtag($options['etag']);
            }
            if (isset($options['last_modified'])) {
                $this->setLastModified($options['last_modified']);
            }
            if (isset($options['max_age'])) {
                $this->setMaxAge($options['max_age']);
            }
            if (isset($options['s_maxage'])) {
                $this->setSharedMaxAge($options['s_maxage']);
            }
            if (isset($options['public'])) {
                if ($options['public']) {
                    $this->setPublic();
                } else {
                    $this->setPrivate();
                }
            }
            if (isset($options['private'])) {
                if ($options['private']) {
                    $this->setPrivate();
                } else {
                    $this->setPublic();
                }
            }

            return $this;
        }

        public function setNotModified()
        {
            $this->setStatusCode(304);
            $this->setContent(null);
            foreach (['Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified'] as $header) {
                $this->headers->remove($header);
            }

            return $this;
        }

        public function hasVary()
        {
            return null !== $this->headers->get('Vary');
        }

        public function getVary()
        {
            if (!$vary = $this->headers->get('Vary', null, false)) {
                return [];
            }
            $ret = [];
            foreach ($vary as $item) {
                $ret = array_merge($ret, preg_split('/[\s,]+/', $item));
            }

            return $ret;
        }

        public function setVary($headers, $replace = true)
        {
            $this->headers->set('Vary', $headers, $replace);

            return $this;
        }

        public function isNotModified(Request $request)
        {
            if (!$request->isMethodSafe()) {
                return false;
            }
            $notModified = false;
            $lastModified = $this->headers->get('Last-Modified');
            $modifiedSince = $request->headers->get('If-Modified-Since');
            if ($etags = $request->getEtags()) {
                $notModified = in_array($this->getEtag(), $etags) || in_array('*', $etags);
            }
            if ($modifiedSince && $lastModified) {
                $notModified = strtotime($modifiedSince) >= strtotime($lastModified) && (!$etags || $notModified);
            }
            if ($notModified) {
                $this->setNotModified();
            }

            return $notModified;
        }

        public function isInvalid()
        {
            return $this->statusCode < 100 || $this->statusCode >= 600;
        }

        public function isInformational()
        {
            return $this->statusCode >= 100 && $this->statusCode < 200;
        }

        public function isSuccessful()
        {
            return $this->statusCode >= 200 && $this->statusCode < 300;
        }

        public function isRedirection()
        {
            return $this->statusCode >= 300 && $this->statusCode < 400;
        }

        public function isClientError()
        {
            return $this->statusCode >= 400 && $this->statusCode < 500;
        }

        public function isServerError()
        {
            return $this->statusCode >= 500 && $this->statusCode < 600;
        }

        public function isOk()
        {
            return 200 === $this->statusCode;
        }

        public function isForbidden()
        {
            return 403 === $this->statusCode;
        }

        public function isNotFound()
        {
            return 404 === $this->statusCode;
        }

        public function isRedirect($location = null)
        {
            return in_array($this->statusCode, [201, 301, 302, 303, 307, 308]) && (null === $location ?: $location == $this->headers->get('Location'));
        }

        public function isEmpty()
        {
            return in_array($this->statusCode, [204, 304]);
        }

        /**
         * @param int  $targetLevel
         * @param bool $flush
         */
        public static function closeOutputBuffers($targetLevel, $flush)
        {
            $status = ob_get_status(true);
            $level = count($status);
            while ($level-- > $targetLevel
                && (!empty($status[$level]['del'])
                    || (isset($status[$level]['flags'])
                        && ($status[$level]['flags'] & PHP_OUTPUT_HANDLER_REMOVABLE)
                        && ($status[$level]['flags'] & ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE))
                    )
                )
            ) {
                if ($flush) {
                    ob_end_flush();
                } else {
                    ob_end_clean();
                }
            }
        }

        protected function ensureIEOverSSLCompatibility(Request $request)
        {
            if (false !== stripos($this->headers->get('Content-Disposition'), 'attachment') && preg_match('/MSIE (.*?);/i', $request->server->get('HTTP_USER_AGENT'), $match) == 1 && true === $request->isSecure()) {
                if ((int) preg_replace('/(MSIE )(.*?);/', '$2', $match[0]) < 9) {
                    $this->headers->remove('Cache-Control');
                }
            }
        }
    }
}

namespace Symfony\Component\HttpFoundation
{
    class ResponseHeaderBag extends HeaderBag
    {
        const COOKIES_FLAT = 'flat';
        const COOKIES_ARRAY = 'array';
        const DISPOSITION_ATTACHMENT = 'attachment';
        const DISPOSITION_INLINE = 'inline';
        protected $computedCacheControl = [];
        protected $cookies = [];
        protected $headerNames = [];

        public function __construct(array $headers = [])
        {
            parent::__construct($headers);
            if (!isset($this->headers['cache-control'])) {
                $this->set('Cache-Control', '');
            }
        }

        public function __toString()
        {
            $cookies = '';
            foreach ($this->getCookies() as $cookie) {
                $cookies .= 'Set-Cookie: '.$cookie."\r\n";
            }
            ksort($this->headerNames);

            return parent::__toString().$cookies;
        }

        public function allPreserveCase()
        {
            return array_combine($this->headerNames, $this->headers);
        }

        public function replace(array $headers = [])
        {
            $this->headerNames = [];
            parent::replace($headers);
            if (!isset($this->headers['cache-control'])) {
                $this->set('Cache-Control', '');
            }
        }

        /**
         * @param string $key
         */
        public function set($key, $values, $replace = true)
        {
            parent::set($key, $values, $replace);
            $uniqueKey = strtr(strtolower($key), '_', '-');
            $this->headerNames[$uniqueKey] = $key;
            if (in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'])) {
                $computed = $this->computeCacheControlValue();
                $this->headers['cache-control'] = [$computed];
                $this->headerNames['cache-control'] = 'Cache-Control';
                $this->computedCacheControl = $this->parseCacheControl($computed);
            }
        }

        /**
         * @param string $key
         */
        public function remove($key)
        {
            parent::remove($key);
            $uniqueKey = strtr(strtolower($key), '_', '-');
            unset($this->headerNames[$uniqueKey]);
            if ('cache-control' === $uniqueKey) {
                $this->computedCacheControl = [];
            }
        }

        /**
         * @param string $key
         */
        public function hasCacheControlDirective($key)
        {
            return array_key_exists($key, $this->computedCacheControl);
        }

        /**
         * @param string $key
         */
        public function getCacheControlDirective($key)
        {
            return array_key_exists($key, $this->computedCacheControl) ? $this->computedCacheControl[$key] : null;
        }

        public function setCookie(Cookie $cookie)
        {
            $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        }

        public function removeCookie($name, $path = '/', $domain = null)
        {
            if (null === $path) {
                $path = '/';
            }
            unset($this->cookies[$domain][$path][$name]);
            if (empty($this->cookies[$domain][$path])) {
                unset($this->cookies[$domain][$path]);
                if (empty($this->cookies[$domain])) {
                    unset($this->cookies[$domain]);
                }
            }
        }

        public function getCookies($format = self::COOKIES_FLAT)
        {
            if (!in_array($format, [self::COOKIES_FLAT, self::COOKIES_ARRAY])) {
                throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', [self::COOKIES_FLAT, self::COOKIES_ARRAY])));
            }
            if (self::COOKIES_ARRAY === $format) {
                return $this->cookies;
            }
            $flattenedCookies = [];
            foreach ($this->cookies as $path) {
                foreach ($path as $cookies) {
                    foreach ($cookies as $cookie) {
                        $flattenedCookies[] = $cookie;
                    }
                }
            }

            return $flattenedCookies;
        }

        public function clearCookie($name, $path = '/', $domain = null, $secure = false, $httpOnly = true)
        {
            $this->setCookie(new Cookie($name, null, 1, $path, $domain, $secure, $httpOnly));
        }

        public function makeDisposition($disposition, $filename, $filenameFallback = '')
        {
            if (!in_array($disposition, [self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE])) {
                throw new \InvalidArgumentException(sprintf('The disposition must be either "%s" or "%s".', self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE));
            }
            if ('' == $filenameFallback) {
                $filenameFallback = $filename;
            }
            if (!preg_match('/^[\x20-\x7e]*$/', $filenameFallback)) {
                throw new \InvalidArgumentException('The filename fallback must only contain ASCII characters.');
            }
            if (false !== strpos($filenameFallback, '%')) {
                throw new \InvalidArgumentException('The filename fallback cannot contain the "%" character.');
            }
            if (false !== strpos($filename, '/') || false !== strpos($filename, '\\') || false !== strpos($filenameFallback, '/') || false !== strpos($filenameFallback, '\\')) {
                throw new \InvalidArgumentException('The filename and the fallback cannot contain the "/" and "\\" characters.');
            }
            $output = sprintf('%s; filename="%s"', $disposition, str_replace('"', '\\"', $filenameFallback));
            if ($filename !== $filenameFallback) {
                $output .= sprintf("; filename*=utf-8''%s", rawurlencode($filename));
            }

            return $output;
        }

        protected function computeCacheControlValue()
        {
            if (!$this->cacheControl && !$this->has('ETag') && !$this->has('Last-Modified') && !$this->has('Expires')) {
                return'no-cache';
            }
            if (!$this->cacheControl) {
                return'private, must-revalidate';
            }
            $header = $this->getCacheControlHeader();
            if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
                return $header;
            }
            if (!isset($this->cacheControl['s-maxage'])) {
                return $header.', private';
            }

            return $header;
        }
    }
}

namespace Symfony\Component\DependencyInjection
{
    interface ContainerAwareInterface
    {
        /**
         * @return void
         */
        public function setContainer(ContainerInterface $container = null);
    }
}

namespace Symfony\Component\DependencyInjection
{
    interface ContainerInterface
    {
        const EXCEPTION_ON_INVALID_REFERENCE = 1;
        const NULL_ON_INVALID_REFERENCE = 2;
        const IGNORE_ON_INVALID_REFERENCE = 3;
        const SCOPE_CONTAINER = 'container';
        const SCOPE_PROTOTYPE = 'prototype';

        /**
         * @param string                                         $id
         * @param \Symfony\Component\HttpFoundation\Request|null $service
         *
         * @return void
         */
        public function set($id, $service, $scope = self::SCOPE_CONTAINER);

        /**
         * @param string $id
         */
        public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE);

        /**
         * @param string $id
         *
         * @return bool|null
         */
        public function has($id);

        /**
         * @param string $name
         *
         * @return string
         */
        public function getParameter($name);

        /**
         * @return bool
         */
        public function hasParameter($name);

        /**
         * @return void
         */
        public function setParameter($name, $value);

        /**
         * @param string $name
         *
         * @return void
         */
        public function enterScope($name);

        /**
         * @param string $name
         *
         * @return void
         */
        public function leaveScope($name);

        /**
         * @return void
         */
        public function addScope(ScopeInterface $scope);

        /**
         * @param string $name
         *
         * @return bool
         */
        public function hasScope($name);

        /**
         * @return bool
         */
        public function isScopeActive($name);
    }
}

namespace Symfony\Component\DependencyInjection
{
    interface IntrospectableContainerInterface extends ContainerInterface
    {
        /**
         * @return bool
         */
        public function initialized($id);
    }
}

namespace Symfony\Component\DependencyInjection
{
    use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
    use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
    use Symfony\Component\DependencyInjection\Exception\RuntimeException;
    use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
    use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
    use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

    class Container implements IntrospectableContainerInterface
    {
        protected $parameterBag;
        protected $services = [];
        protected $methodMap = [];
        protected $aliases = [];
        protected $scopes = [];
        protected $scopeChildren = [];
        protected $scopedServices = [];
        protected $scopeStacks = [];
        protected $loading = [];

        public function __construct(ParameterBagInterface $parameterBag = null)
        {
            $this->parameterBag = $parameterBag ?: new ParameterBag();
        }

        public function compile()
        {
            $this->parameterBag->resolve();
            $this->parameterBag = new FrozenParameterBag($this->parameterBag->all());
        }

        public function isFrozen()
        {
            return $this->parameterBag instanceof FrozenParameterBag;
        }

        public function getParameterBag()
        {
            return $this->parameterBag;
        }

        /**
         * @param string $name
         */
        public function getParameter($name)
        {
            return $this->parameterBag->get($name);
        }

        public function hasParameter($name)
        {
            return $this->parameterBag->has($name);
        }

        public function setParameter($name, $value)
        {
            $this->parameterBag->set($name, $value);
        }

        public function set($id, $service, $scope = self::SCOPE_CONTAINER)
        {
            if (self::SCOPE_PROTOTYPE === $scope) {
                throw new InvalidArgumentException(sprintf('You cannot set service "%s" of scope "prototype".', $id));
            }
            $id = strtolower($id);
            if ('service_container' === $id) {
                return;
            }
            if (self::SCOPE_CONTAINER !== $scope) {
                if (!isset($this->scopedServices[$scope])) {
                    throw new RuntimeException(sprintf('You cannot set service "%s" of inactive scope.', $id));
                }
                $this->scopedServices[$scope][$id] = $service;
            }
            $this->services[$id] = $service;
            if (method_exists($this, $method = 'synchronize'.strtr($id, ['_' => '', '.' => '_', '\\' => '_']).'Service')) {
                $this->$method();
            }
            if (null === $service) {
                if (self::SCOPE_CONTAINER !== $scope) {
                    unset($this->scopedServices[$scope][$id]);
                }
                unset($this->services[$id]);
            }
        }

        /**
         * @param string $id
         */
        public function has($id)
        {
            $id = strtolower($id);
            if ('service_container' === $id) {
                return true;
            }

            return isset($this->services[$id])
            || array_key_exists($id, $this->services)
            || isset($this->aliases[$id])
            || method_exists($this, 'get'.strtr($id, ['_' => '', '.' => '_', '\\' => '_']).'Service');
        }

        /**
         * @param string $id
         */
        public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
        {
            foreach ([false, true] as $strtolower) {
                if ($strtolower) {
                    $id = strtolower($id);
                }
                if ('service_container' === $id) {
                    return $this;
                }
                if (isset($this->aliases[$id])) {
                    $id = $this->aliases[$id];
                }
                if (isset($this->services[$id]) || array_key_exists($id, $this->services)) {
                    return $this->services[$id];
                }
            }
            if (isset($this->loading[$id])) {
                throw new ServiceCircularReferenceException($id, array_keys($this->loading));
            }
            if (isset($this->methodMap[$id])) {
                $method = $this->methodMap[$id];
            } elseif (method_exists($this, $method = 'get'.strtr($id, ['_' => '', '.' => '_', '\\' => '_']).'Service')) {
            } else {
                if (self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
                    if (!$id) {
                        throw new ServiceNotFoundException($id);
                    }
                    $alternatives = [];
                    foreach ($this->services as $key => $associatedService) {
                        $lev = levenshtein($id, $key);
                        if ($lev <= strlen($id) / 3 || false !== strpos($key, $id)) {
                            $alternatives[] = $key;
                        }
                    }
                    throw new ServiceNotFoundException($id, null, null, $alternatives);
                }

                return;
            }
            $this->loading[$id] = true;
            try {
                $service = $this->$method();
            } catch (\Exception $e) {
                unset($this->loading[$id]);
                if (array_key_exists($id, $this->services)) {
                    unset($this->services[$id]);
                }
                if ($e instanceof InactiveScopeException && self::EXCEPTION_ON_INVALID_REFERENCE !== $invalidBehavior) {
                    return;
                }
                throw $e;
            }
            unset($this->loading[$id]);

            return $service;
        }

        public function initialized($id)
        {
            $id = strtolower($id);
            if ('service_container' === $id) {
                return true;
            }
            if (isset($this->aliases[$id])) {
                $id = $this->aliases[$id];
            }

            return isset($this->services[$id]) || array_key_exists($id, $this->services);
        }

        public function getServiceIds()
        {
            $ids = [];
            $r = new \ReflectionClass($this);
            foreach ($r->getMethods() as $method) {
                if (preg_match('/^get(.+)Service$/', $method->name, $match)) {
                    $ids[] = self::underscore($match[1]);
                }
            }
            $ids[] = 'service_container';

            return array_unique(array_merge($ids, array_keys($this->services)));
        }

        public function enterScope($name)
        {
            if (!isset($this->scopes[$name])) {
                throw new InvalidArgumentException(sprintf('The scope "%s" does not exist.', $name));
            }
            if (self::SCOPE_CONTAINER !== $this->scopes[$name] && !isset($this->scopedServices[$this->scopes[$name]])) {
                throw new RuntimeException(sprintf('The parent scope "%s" must be active when entering this scope.', $this->scopes[$name]));
            }
            if (isset($this->scopedServices[$name])) {
                $services = [$this->services, $name => $this->scopedServices[$name]];
                unset($this->scopedServices[$name]);
                foreach ($this->scopeChildren[$name] as $child) {
                    if (isset($this->scopedServices[$child])) {
                        $services[$child] = $this->scopedServices[$child];
                        unset($this->scopedServices[$child]);
                    }
                }
                $this->services = call_user_func_array('array_diff_key', $services);
                array_shift($services);
                if (!isset($this->scopeStacks[$name])) {
                    $this->scopeStacks[$name] = new \SplStack();
                }
                $this->scopeStacks[$name]->push($services);
            }
            $this->scopedServices[$name] = [];
        }

        public function leaveScope($name)
        {
            if (!isset($this->scopedServices[$name])) {
                throw new InvalidArgumentException(sprintf('The scope "%s" is not active.', $name));
            }
            $services = [$this->services, $this->scopedServices[$name]];
            unset($this->scopedServices[$name]);
            foreach ($this->scopeChildren[$name] as $child) {
                if (isset($this->scopedServices[$child])) {
                    $services[] = $this->scopedServices[$child];
                    unset($this->scopedServices[$child]);
                }
            }
            $this->services = call_user_func_array('array_diff_key', $services);
            if (isset($this->scopeStacks[$name]) && count($this->scopeStacks[$name]) > 0) {
                $services = $this->scopeStacks[$name]->pop();
                $this->scopedServices += $services;
                if ($this->scopeStacks[$name]->isEmpty()) {
                    unset($this->scopeStacks[$name]);
                }
                foreach ($services as $array) {
                    foreach ($array as $id => $service) {
                        $this->set($id, $service, $name);
                    }
                }
            }
        }

        public function addScope(ScopeInterface $scope)
        {
            $name = $scope->getName();
            $parentScope = $scope->getParentName();
            if (self::SCOPE_CONTAINER === $name || self::SCOPE_PROTOTYPE === $name) {
                throw new InvalidArgumentException(sprintf('The scope "%s" is reserved.', $name));
            }
            if (isset($this->scopes[$name])) {
                throw new InvalidArgumentException(sprintf('A scope with name "%s" already exists.', $name));
            }
            if (self::SCOPE_CONTAINER !== $parentScope && !isset($this->scopes[$parentScope])) {
                throw new InvalidArgumentException(sprintf('The parent scope "%s" does not exist, or is invalid.', $parentScope));
            }
            $this->scopes[$name] = $parentScope;
            $this->scopeChildren[$name] = [];
            while ($parentScope !== self::SCOPE_CONTAINER) {
                $this->scopeChildren[$parentScope][] = $name;
                $parentScope = $this->scopes[$parentScope];
            }
        }

        public function hasScope($name)
        {
            return isset($this->scopes[$name]);
        }

        public function isScopeActive($name)
        {
            return isset($this->scopedServices[$name]);
        }

        /**
         * @param string $id
         */
        public static function camelize($id)
        {
            return strtr(ucwords(strtr($id, ['_' => ' ', '.' => '_ ', '\\' => '_ '])), [' ' => '']);
        }

        public static function underscore($id)
        {
            return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], strtr($id, '_', '.')));
        }
    }
}

namespace Symfony\Component\HttpKernel
{
    use Symfony\Component\HttpFoundation\Request;

    interface HttpKernelInterface
    {
        const MASTER_REQUEST = 1;
        const SUB_REQUEST = 2;

        public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true);
    }
}

namespace Symfony\Component\HttpKernel
{
    use Symfony\Component\Config\Loader\LoaderInterface;

    interface KernelInterface extends HttpKernelInterface, \Serializable
    {
        public function registerBundles();

        /**
         * @return \Symfony\Component\DependencyInjection\ContainerBuilder
         */
        public function registerContainerConfiguration(LoaderInterface $loader);

        /**
         * @return void
         */
        public function boot();

        /**
         * @return void
         */
        public function shutdown();

        public function getBundles();

        /**
         * @return bool
         */
        public function isClassInActiveBundle($class);

        public function getBundle($name, $first = true);

        public function locateResource($name, $dir = null, $first = true);

        public function getName();

        public function getEnvironment();

        /**
         * @return bool
         */
        public function isDebug();

        public function getRootDir();

        public function getContainer();

        public function getStartTime();

        /**
         * @return string
         */
        public function getCacheDir();

        /**
         * @return string
         */
        public function getLogDir();

        /**
         * @return string
         */
        public function getCharset();
    }
}

namespace Symfony\Component\HttpKernel
{
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    interface TerminableInterface
    {
        /**
         * @return void
         */
        public function terminate(Request $request, Response $response);
    }
}

namespace Symfony\Component\HttpKernel
{
    use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
    use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
    use Symfony\Component\ClassLoader\ClassCollectionLoader;
    use Symfony\Component\Config\ConfigCache;
    use Symfony\Component\Config\Loader\DelegatingLoader;
    use Symfony\Component\Config\Loader\LoaderResolver;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
    use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
    use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
    use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Config\EnvParametersResource;
    use Symfony\Component\HttpKernel\Config\FileLocator;
    use Symfony\Component\HttpKernel\DependencyInjection\AddClassesToCachePass;
    use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

    abstract class Kernel implements KernelInterface, TerminableInterface
    {
        protected $bundles = [];
        protected $bundleMap;
        protected $container;
        protected $rootDir;
        protected $environment;
        protected $debug;
        protected $booted = false;
        protected $name;
        protected $startTime;
        protected $loadClassCache;
        const VERSION = '2.6.7';
        const VERSION_ID = '20607';
        const MAJOR_VERSION = '2';
        const MINOR_VERSION = '6';
        const RELEASE_VERSION = '7';
        const EXTRA_VERSION = '';

        public function __construct($environment, $debug)
        {
            $this->environment = $environment;
            $this->debug = (bool) $debug;
            $this->rootDir = $this->getRootDir();
            $this->name = $this->getName();
            if ($this->debug) {
                $this->startTime = microtime(true);
            }
            $this->init();
        }

        public function init()
        {
        }

        public function __clone()
        {
            if ($this->debug) {
                $this->startTime = microtime(true);
            }
            $this->booted = false;
            $this->container = null;
        }

        public function boot()
        {
            if (true === $this->booted) {
                return;
            }
            if ($this->loadClassCache) {
                $this->doLoadClassCache($this->loadClassCache[0], $this->loadClassCache[1]);
            }
            $this->initializeBundles();
            $this->initializeContainer();
            foreach ($this->getBundles() as $bundle) {
                $bundle->setContainer($this->container);
                $bundle->boot();
            }
            $this->booted = true;
        }

        public function terminate(Request $request, Response $response)
        {
            if (false === $this->booted) {
                return;
            }
            if ($this->getHttpKernel() instanceof TerminableInterface) {
                $this->getHttpKernel()->terminate($request, $response);
            }
        }

        public function shutdown()
        {
            if (false === $this->booted) {
                return;
            }
            $this->booted = false;
            foreach ($this->getBundles() as $bundle) {
                $bundle->shutdown();
                $bundle->setContainer(null);
            }
            $this->container = null;
        }

        public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
        {
            if (false === $this->booted) {
                $this->boot();
            }

            return $this->getHttpKernel()->handle($request, $type, $catch);
        }

        protected function getHttpKernel()
        {
            return $this->container->get('http_kernel');
        }

        public function getBundles()
        {
            return $this->bundles;
        }

        public function isClassInActiveBundle($class)
        {
            foreach ($this->getBundles() as $bundle) {
                if (0 === strpos($class, $bundle->getNamespace())) {
                    return true;
                }
            }

            return false;
        }

        public function getBundle($name, $first = true)
        {
            if (!isset($this->bundleMap[$name])) {
                throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist or it is not enabled. Maybe you forgot to add it in the registerBundles() method of your %s.php file?', $name, get_class($this)));
            }
            if (true === $first) {
                return $this->bundleMap[$name][0];
            }

            return $this->bundleMap[$name];
        }

        public function locateResource($name, $dir = null, $first = true)
        {
            if ('@' !== $name[0]) {
                throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
            }
            if (false !== strpos($name, '..')) {
                throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
            }
            $bundleName = substr($name, 1);
            $path = '';
            if (false !== strpos($bundleName, '/')) {
                list($bundleName, $path) = explode('/', $bundleName, 2);
            }
            $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
            $overridePath = substr($path, 9);
            $resourceBundle = null;
            $bundles = $this->getBundle($bundleName, false);
            $files = [];
            foreach ($bundles as $bundle) {
                if ($isResource && file_exists($file = $dir.'/'.$bundle->getName().$overridePath)) {
                    if (null !== $resourceBundle) {
                        throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                                $file,
                                $resourceBundle,
                                $dir.'/'.$bundles[0]->getName().$overridePath
                            ));
                    }
                    if ($first) {
                        return $file;
                    }
                    $files[] = $file;
                }
                if (file_exists($file = $bundle->getPath().'/'.$path)) {
                    if ($first && !$isResource) {
                        return $file;
                    }
                    $files[] = $file;
                    $resourceBundle = $bundle->getName();
                }
            }
            if (count($files) > 0) {
                return $first && $isResource ? $files[0] : $files;
            }
            throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
        }

        public function getName()
        {
            if (null === $this->name) {
                $this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
            }

            return $this->name;
        }

        public function getEnvironment()
        {
            return $this->environment;
        }

        public function isDebug()
        {
            return $this->debug;
        }

        public function getRootDir()
        {
            if (null === $this->rootDir) {
                $r = new \ReflectionObject($this);
                $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()));
            }

            return $this->rootDir;
        }

        public function getContainer()
        {
            return $this->container;
        }

        public function loadClassCache($name = 'classes', $extension = '.php')
        {
            $this->loadClassCache = [$name, $extension];
        }

        public function setClassCache(array $classes)
        {
            file_put_contents($this->getCacheDir().'/classes.map', sprintf('<?php return %s;', var_export($classes, true)));
        }

        public function getStartTime()
        {
            return $this->debug ? $this->startTime : -INF;
        }

        public function getCacheDir()
        {
            return $this->rootDir.'/../var/cache/'.$this->environment;
        }

        public function getLogDir()
        {
            return $this->rootDir.'/../var/logs';
        }

        public function getCharset()
        {
            return'UTF-8';
        }

        protected function doLoadClassCache($name, $extension)
        {
            if (!$this->booted && is_file($this->getCacheDir().'/classes.map')) {
                ClassCollectionLoader::load(include($this->getCacheDir().'/classes.map'), $this->getCacheDir(), $name, $this->debug, false, $extension);
            }
        }

        protected function initializeBundles()
        {
            $this->bundles = [];
            $topMostBundles = [];
            $directChildren = [];
            foreach ($this->registerBundles() as $bundle) {
                $name = $bundle->getName();
                if (isset($this->bundles[$name])) {
                    throw new \LogicException(sprintf('Trying to register two bundles with the same name "%s"', $name));
                }
                $this->bundles[$name] = $bundle;
                if ($parentName = $bundle->getParent()) {
                    if (isset($directChildren[$parentName])) {
                        throw new \LogicException(sprintf('Bundle "%s" is directly extended by two bundles "%s" and "%s".', $parentName, $name, $directChildren[$parentName]));
                    }
                    if ($parentName == $name) {
                        throw new \LogicException(sprintf('Bundle "%s" can not extend itself.', $name));
                    }
                    $directChildren[$parentName] = $name;
                } else {
                    $topMostBundles[$name] = $bundle;
                }
            }
            if (!empty($directChildren) && count($diff = array_diff_key($directChildren, $this->bundles))) {
                $diff = array_keys($diff);
                throw new \LogicException(sprintf('Bundle "%s" extends bundle "%s", which is not registered.', $directChildren[$diff[0]], $diff[0]));
            }
            $this->bundleMap = [];
            foreach ($topMostBundles as $name => $bundle) {
                $bundleMap = [$bundle];
                $hierarchy = [$name];
                while (isset($directChildren[$name])) {
                    $name = $directChildren[$name];
                    array_unshift($bundleMap, $this->bundles[$name]);
                    $hierarchy[] = $name;
                }
                foreach ($hierarchy as $bundle) {
                    $this->bundleMap[$bundle] = $bundleMap;
                    array_pop($bundleMap);
                }
            }
        }

        protected function getContainerClass()
        {
            return $this->name.ucfirst($this->environment).($this->debug ? 'Debug' : '').'ProjectContainer';
        }

        protected function getContainerBaseClass()
        {
            return'Container';
        }

        protected function initializeContainer()
        {
            $class = $this->getContainerClass();
            $cache = new ConfigCache($this->getCacheDir().'/'.$class.'.php', $this->debug);
            $fresh = true;
            if (!$cache->isFresh()) {
                $container = $this->buildContainer();
                $container->compile();
                $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());
                $fresh = false;
            }
            require_once $cache;
            $this->container = new $class();
            $this->container->set('kernel', $this);
            if (!$fresh && $this->container->has('cache_warmer')) {
                $this->container->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
            }
        }

        protected function getKernelParameters()
        {
            $bundles = [];
            foreach ($this->bundles as $name => $bundle) {
                $bundles[$name] = get_class($bundle);
            }

            return array_merge(
                ['kernel.root_dir' => realpath($this->rootDir) ?: $this->rootDir, 'kernel.environment' => $this->environment, 'kernel.debug' => $this->debug, 'kernel.name' => $this->name, 'kernel.cache_dir' => realpath($this->getCacheDir()) ?: $this->getCacheDir(), 'kernel.logs_dir' => realpath($this->getLogDir()) ?: $this->getLogDir(), 'kernel.bundles' => $bundles, 'kernel.charset' => $this->getCharset(), 'kernel.container_class' => $this->getContainerClass(),
                ],
                $this->getEnvParameters()
            );
        }

        protected function getEnvParameters()
        {
            $parameters = [];
            foreach ($_SERVER as $key => $value) {
                if (0 === strpos($key, 'SYMFONY__')) {
                    $parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
                }
            }

            return $parameters;
        }

        protected function buildContainer()
        {
            foreach (['cache' => $this->getCacheDir(), 'logs' => $this->getLogDir()] as $name => $dir) {
                if (!is_dir($dir)) {
                    if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                        throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                    }
                } elseif (!is_writable($dir)) {
                    throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
                }
            }
            $container = $this->getContainerBuilder();
            $container->addObjectResource($this);
            $this->prepareContainer($container);
            if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
                $container->merge($cont);
            }
            $container->addCompilerPass(new AddClassesToCachePass($this));
            $container->addResource(new EnvParametersResource('SYMFONY__'));

            return $container;
        }

        protected function prepareContainer(ContainerBuilder $container)
        {
            $extensions = [];
            foreach ($this->bundles as $bundle) {
                if ($extension = $bundle->getContainerExtension()) {
                    $container->registerExtension($extension);
                    $extensions[] = $extension->getAlias();
                }
                if ($this->debug) {
                    $container->addObjectResource($bundle);
                }
            }
            foreach ($this->bundles as $bundle) {
                $bundle->build($container);
            }
            $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
        }

        protected function getContainerBuilder()
        {
            $container = new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
            if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator')) {
                $container->setProxyInstantiator(new RuntimeInstantiator());
            }

            return $container;
        }

        /**
         * @param string $class
         * @param string $baseClass
         */
        protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
        {
            $dumper = new PhpDumper($container);
            if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper')) {
                $dumper->setProxyDumper(new ProxyDumper(md5((string) $cache)));
            }
            $content = $dumper->dump(['class' => $class, 'base_class' => $baseClass, 'file' => (string) $cache]);
            if (!$this->debug) {
                $content = static::stripComments($content);
            }
            $cache->write($content, $container->getResources());
        }

        protected function getContainerLoader(ContainerInterface $container)
        {
            $locator = new FileLocator($this);
            $resolver = new LoaderResolver([
                    new XmlFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                    new IniFileLoader($container, $locator),
                    new PhpFileLoader($container, $locator),
                    new ClosureLoader($container),
                ]);

            return new DelegatingLoader($resolver);
        }

        /**
         * @param string $source
         */
        public static function stripComments($source)
        {
            if (!function_exists('token_get_all')) {
                return $source;
            }
            $rawChunk = '';
            $output = '';
            $tokens = token_get_all($source);
            $ignoreSpace = false;
            for (reset($tokens); false !== $token = current($tokens); next($tokens)) {
                if (is_string($token)) {
                    $rawChunk .= $token;
                } elseif (T_START_HEREDOC === $token[0]) {
                    $output .= $rawChunk.$token[1];
                    do {
                        $token = next($tokens);
                        $output .= $token[1];
                    } while ($token[0] !== T_END_HEREDOC);
                    $rawChunk = '';
                } elseif (T_WHITESPACE === $token[0]) {
                    if ($ignoreSpace) {
                        $ignoreSpace = false;
                        continue;
                    }
                    $rawChunk .= preg_replace(['/\n{2,}/S'], "\n", $token[1]);
                } elseif (in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                    $ignoreSpace = true;
                } else {
                    $rawChunk .= $token[1];
                    if (T_OPEN_TAG === $token[0]) {
                        $ignoreSpace = true;
                    }
                }
            }
            $output .= $rawChunk;

            return $output;
        }

        public function serialize()
        {
            return serialize([$this->environment, $this->debug]);
        }

        public function unserialize($data)
        {
            list($environment, $debug) = unserialize($data);
            $this->__construct($environment, $debug);
        }
    }
}

namespace Symfony\Component\ClassLoader
{
    class ApcClassLoader
    {
        private $prefix;
        protected $decorated;

        public function __construct($prefix, $decorated)
        {
            if (!extension_loaded('apc')) {
                throw new \RuntimeException('Unable to use ApcClassLoader as APC is not enabled.');
            }
            if (!method_exists($decorated, 'findFile')) {
                throw new \InvalidArgumentException('The class finder must implement a "findFile" method.');
            }
            $this->prefix = $prefix;
            $this->decorated = $decorated;
        }

        public function register($prepend = false)
        {
            spl_autoload_register([$this, 'loadClass'], true, $prepend);
        }

        public function unregister()
        {
            spl_autoload_unregister([$this, 'loadClass']);
        }

        public function loadClass($class)
        {
            if ($file = $this->findFile($class)) {
                require $file;

                return true;
            }
        }

        public function findFile($class)
        {
            if (false === $file = apc_fetch($this->prefix.$class)) {
                apc_store($this->prefix.$class, $file = $this->decorated->findFile($class));
            }

            return $file;
        }

        public function __call($method, $args)
        {
            return call_user_func_array([$this->decorated, $method], $args);
        }
    }
}

namespace Symfony\Component\HttpKernel\Bundle
{
    use Symfony\Component\DependencyInjection\ContainerAwareInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    interface BundleInterface extends ContainerAwareInterface
    {
        /**
         * @return void
         */
        public function boot();

        /**
         * @return void
         */
        public function shutdown();

        /**
         * @return void
         */
        public function build(ContainerBuilder $container);

        public function getContainerExtension();

        /**
         * @return null|string
         */
        public function getParent();

        public function getName();

        /**
         * @return string
         */
        public function getNamespace();

        public function getPath();
    }
}

namespace Symfony\Component\DependencyInjection
{
    abstract class ContainerAware implements ContainerAwareInterface
    {
        protected $container;

        public function setContainer(ContainerInterface $container = null)
        {
            $this->container = $container;
        }
    }
}

namespace Symfony\Component\HttpKernel\Bundle
{
    use Symfony\Component\Console\Application;
    use Symfony\Component\DependencyInjection\Container;
    use Symfony\Component\DependencyInjection\ContainerAware;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\Finder\Finder;

    abstract class Bundle extends ContainerAware implements BundleInterface
    {
        protected $name;
        protected $extension;
        protected $path;

        public function boot()
        {
        }

        public function shutdown()
        {
        }

        public function build(ContainerBuilder $container)
        {
        }

        public function getContainerExtension()
        {
            if (null === $this->extension) {
                $class = $this->getContainerExtensionClass();
                if (class_exists($class)) {
                    $extension = new $class();
                    $basename = preg_replace('/Bundle$/', '', $this->getName());
                    $expectedAlias = Container::underscore($basename);
                    if ($expectedAlias != $extension->getAlias()) {
                        throw new \LogicException(sprintf('Users will expect the alias of the default extension of a bundle to be the underscored version of the bundle name ("%s"). You can override "Bundle::getContainerExtension()" if you want to use "%s" or another alias.',
                                $expectedAlias, $extension->getAlias()
                            ));
                    }
                    $this->extension = $extension;
                } else {
                    $this->extension = false;
                }
            }
            if ($this->extension) {
                return $this->extension;
            }
        }

        public function getNamespace()
        {
            $class = get_class($this);

            return substr($class, 0, strrpos($class, '\\'));
        }

        public function getPath()
        {
            if (null === $this->path) {
                $reflected = new \ReflectionObject($this);
                $this->path = dirname($reflected->getFileName());
            }

            return $this->path;
        }

        public function getParent()
        {
        }

        final public function getName()
        {
            if (null !== $this->name) {
                return $this->name;
            }
            $name = get_class($this);
            $pos = strrpos($name, '\\');

            return $this->name = false === $pos ? $name : substr($name, $pos + 1);
        }

        public function registerCommands(Application $application)
        {
            if (!is_dir($dir = $this->getPath().'/Command')) {
                return;
            }
            $finder = new Finder();
            $finder->files()->name('*Command.php')->in($dir);
            $prefix = $this->getNamespace().'\\Command';
            foreach ($finder as $file) {
                $ns = $prefix;
                if ($relativePath = $file->getRelativePath()) {
                    $ns .= '\\'.strtr($relativePath, '/', '\\');
                }
                $class = $ns.'\\'.$file->getBasename('.php');
                if ($this->container) {
                    $alias = 'console.command.'.strtolower(str_replace('\\', '_', $class));
                    if ($this->container->has($alias)) {
                        continue;
                    }
                }
                $r = new \ReflectionClass($class);
                if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract() && !$r->getConstructor()->getNumberOfRequiredParameters()) {
                    $application->add($r->newInstance());
                }
            }
        }

        protected function getContainerExtensionClass()
        {
            $basename = preg_replace('/Bundle$/', '', $this->getName());

            return $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
        }
    }
}

namespace Symfony\Component\Config
{
    use Symfony\Component\Filesystem\Exception\IOException;
    use Symfony\Component\Filesystem\Filesystem;

    class ConfigCache
    {
        private $debug;
        private $file;

        /**
         * @param string $file
         * @param bool   $debug
         */
        public function __construct($file, $debug)
        {
            $this->file = $file;
            $this->debug = (bool) $debug;
        }

        public function __toString()
        {
            return $this->file;
        }

        public function isFresh()
        {
            if (!is_file($this->file)) {
                return false;
            }
            if (!$this->debug) {
                return true;
            }
            $metadata = $this->getMetaFile();
            if (!is_file($metadata)) {
                return false;
            }
            $time = filemtime($this->file);
            $meta = unserialize(file_get_contents($metadata));
            foreach ($meta as $resource) {
                if (!$resource->isFresh($time)) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @param string $content
         */
        public function write($content, array $metadata = null)
        {
            $mode = 0666;
            $umask = umask();
            $filesystem = new Filesystem();
            $filesystem->dumpFile($this->file, $content, null);
            try {
                $filesystem->chmod($this->file, $mode, $umask);
            } catch (IOException $e) {
            }
            if (null !== $metadata && true === $this->debug) {
                $filesystem->dumpFile($this->getMetaFile(), serialize($metadata), null);
                try {
                    $filesystem->chmod($this->getMetaFile(), $mode, $umask);
                } catch (IOException $e) {
                }
            }
        }

        private function getMetaFile()
        {
            return $this->file.'.meta';
        }
    }
}

namespace Symfony\Component\HttpKernel
{
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
    use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
    use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpKernel\Event\PostResponseEvent;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    class HttpKernel implements HttpKernelInterface, TerminableInterface
    {
        protected $dispatcher;
        protected $resolver;
        protected $requestStack;

        public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, RequestStack $requestStack = null)
        {
            $this->dispatcher = $dispatcher;
            $this->resolver = $resolver;
            $this->requestStack = $requestStack ?: new RequestStack();
        }

        public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
        {
            try {
                return $this->handleRaw($request, $type);
            } catch (\Exception $e) {
                if (false === $catch) {
                    $this->finishRequest($request, $type);
                    throw $e;
                }

                return $this->handleException($e, $request, $type);
            }
        }

        public function terminate(Request $request, Response $response)
        {
            $this->dispatcher->dispatch(KernelEvents::TERMINATE, new PostResponseEvent($this, $request, $response));
        }

        public function terminateWithException(\Exception $exception)
        {
            if (!$request = $this->requestStack->getMasterRequest()) {
                throw new \LogicException('Request stack is empty', 0, $exception);
            }
            $response = $this->handleException($exception, $request, self::MASTER_REQUEST);
            $response->sendHeaders();
            $response->sendContent();
            $this->terminate($request, $response);
        }

        private function handleRaw(Request $request, $type = self::MASTER_REQUEST)
        {
            $this->requestStack->push($request);
            $event = new GetResponseEvent($this, $request, $type);
            $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);
            if ($event->hasResponse()) {
                return $this->filterResponse($event->getResponse(), $request, $type);
            }
            if (false === $controller = $this->resolver->getController($request)) {
                throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo()));
            }
            $event = new FilterControllerEvent($this, $controller, $request, $type);
            $this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);
            $controller = $event->getController();
            $arguments = $this->resolver->getArguments($request, $controller);
            $response = call_user_func_array($controller, $arguments);
            if (!$response instanceof Response) {
                $event = new GetResponseForControllerResultEvent($this, $request, $type, $response);
                $this->dispatcher->dispatch(KernelEvents::VIEW, $event);
                if ($event->hasResponse()) {
                    $response = $event->getResponse();
                }
                if (!$response instanceof Response) {
                    $msg = sprintf('The controller must return a response (%s given).', $this->varToString($response));
                    if (null === $response) {
                        $msg .= ' Did you forget to add a return statement somewhere in your controller?';
                    }
                    throw new \LogicException($msg);
                }
            }

            return $this->filterResponse($response, $request, $type);
        }

        private function filterResponse(Response $response, Request $request, $type)
        {
            $event = new FilterResponseEvent($this, $request, $type, $response);
            $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);
            $this->finishRequest($request, $type);

            return $event->getResponse();
        }

        private function finishRequest(Request $request, $type)
        {
            $this->dispatcher->dispatch(KernelEvents::FINISH_REQUEST, new FinishRequestEvent($this, $request, $type));
            $this->requestStack->pop();
        }

        /**
         * @param Request $request
         */
        private function handleException(\Exception $e, $request, $type)
        {
            $event = new GetResponseForExceptionEvent($this, $request, $type, $e);
            $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);
            $e = $event->getException();
            if (!$event->hasResponse()) {
                $this->finishRequest($request, $type);
                throw $e;
            }
            $response = $event->getResponse();
            if ($response->headers->has('X-Status-Code')) {
                $response->setStatusCode($response->headers->get('X-Status-Code'));
                $response->headers->remove('X-Status-Code');
            } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
                if ($e instanceof HttpExceptionInterface) {
                    $response->setStatusCode($e->getStatusCode());
                    $response->headers->add($e->getHeaders());
                } else {
                    $response->setStatusCode(500);
                }
            }
            try {
                return $this->filterResponse($response, $request, $type);
            } catch (\Exception $e) {
                return $response;
            }
        }

        private function varToString($var)
        {
            if (is_object($var)) {
                return sprintf('Object(%s)', get_class($var));
            }
            if (is_array($var)) {
                $a = [];
                foreach ($var as $k => $v) {
                    $a[] = sprintf('%s => %s', $k, $this->varToString($v));
                }

                return sprintf('Array(%s)', implode(', ', $a));
            }
            if (is_resource($var)) {
                return sprintf('Resource(%s)', get_resource_type($var));
            }
            if (null === $var) {
                return'null';
            }
            if (false === $var) {
                return'false';
            }
            if (true === $var) {
                return'true';
            }

            return (string) $var;
        }
    }
}

namespace Symfony\Component\HttpKernel\DependencyInjection
{
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\DependencyInjection\Scope;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
    use Symfony\Component\HttpKernel\HttpKernel;
    use Symfony\Component\HttpKernel\HttpKernelInterface;

    class ContainerAwareHttpKernel extends HttpKernel
    {
        protected $container;

        public function __construct(EventDispatcherInterface $dispatcher, ContainerInterface $container, ControllerResolverInterface $controllerResolver, RequestStack $requestStack = null)
        {
            parent::__construct($dispatcher, $controllerResolver, $requestStack);
            $this->container = $container;
            if (!$container->hasScope('request')) {
                $container->addScope(new Scope('request'));
            }
        }

        public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
        {
            $request->headers->set('X-Php-Ob-Level', ob_get_level());
            $this->container->enterScope('request');
            $this->container->set('request', $request, 'request');
            try {
                $response = parent::handle($request, $type, $catch);
            } catch (\Exception $e) {
                $this->container->set('request', null, 'request');
                $this->container->leaveScope('request');
                throw $e;
            }
            $this->container->set('request', null, 'request');
            $this->container->leaveScope('request');

            return $response;
        }
    }
}

namespace { return $loader; }
