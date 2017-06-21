<?php
declare(strict_types=1);
/**
 * @author William Borba
 * @package Core
 * @uses Core\Util
 */
namespace Core {
    use Core\{Request};
    // use Psr\Http\Message\UriInterface;
    /**
     * see UriInterface
     * @see Request
     * @var array $request
     * @var array $parse_url
     * @var string $scheme
     * @var string $host
     * @var string $user
     * @var string $password
     * @var int $port
     * @var string $path
     * @var string $query
     * @var string $fragment
     */
    // class Uri implements UriInterface {
    class Uri {
        private $request;
        private $parse_url;
        private $scheme;
        private $host;
        private $user;
        private $password;
        private $port;
        private $path;
        private $query;
        private $fragment;
        /**
         * @param Request $request
         */
        public function __construct(Request $request) {
            $this->setRequest($request);

            $http_server = $request->getHttpServer();

            if (!array_key_exists('REQUEST_URI',$http_server) || empty($http_server['REQUEST_URI'])) {
                throw new \Error('Global SERVER missing var "REQUEST_URI"');
            }

            $parse_url = parse_url($http_server['REQUEST_URI']);

            $this->setParseUrl($parse_url);

            if (array_key_exists('scheme',$parse_url)) {
                $this->setScheme($parse_url['scheme']);
            }

            if (array_key_exists('host',$parse_url)) {
                $this->setHost($parse_url['host']);
            }

            if (array_key_exists('user',$parse_url)) {
                $this->setUser($parse_url['user']);
            }

            if (array_key_exists('pass',$parse_url)) {
                $this->setPassword($parse_url['pass']);
            }

            if (array_key_exists('port',$parse_url)) {
                $this->setPort($parse_url['port']);
            }

            if (array_key_exists('path',$parse_url)) {
                $this->setPath($parse_url['path']);
            }

            if (array_key_exists('query',$parse_url)) {
                $this->setQuery($parse_url['query']);
            }

            if (array_key_exists('fragment',$parse_url)) {
                $this->setFragment($parse_url['fragment']);
            }
        }
        /**
         * @return Request
         */
        public function getRequest(): Request {
            return $this->request;
        }
        /**
         * @param Request $request
         * @return self
         */
        private function setRequest($request): self {
            $this->request = $request;

            return $this;
        }
        /**
         * @return array|null
         */
        public function getParseUrl(): ?array {
            return $this->parse_url;
        }
        /**
         * @param array $parse_url
         * @return self
         */
        private function setParseUrl(array $parse_url): self {
            $this->parse_url = $parse_url;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getScheme(): ?string {
            return $this->scheme;
        }
        /**
         * @param string $scheme
         * @return self
         */
        private function setScheme(string $scheme): self {
            $this->scheme = $scheme;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getHost(): ?string {
            return $this->host;
        }
        /**
         * @param  string $host
         * @return self
         */
        public function setHost(string $host): self {
            $this->host = $host;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getUser(): ?string {
            return $this->user;
        }
        /**
         * @param  string $user
         * @return self
         */
        public function setUser(string $user): self {
            $this->user = $user;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getPassword(): ?string {
            return $this->password;
        }
        /**
         * @param  string $password
         * @return self
         */
        public function setPassword(string $password): self {
            $this->password = $password;

            return $this;
        }
        /**
         * @return int|null
         */
        public function getPort(): ?int {
            return $this->port;
        }
        /**
         * @param  int $port
         * @return self
         */
        public function setPort(int $password): self {
            $this->port = $port;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getPath(): ?string {
            return $this->path;
        }
        /**
         * @param  string $path
         * @return self
         */
        public function setPath(string $path): self {
            $this->path = $path;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getQuery(): ?string {
            return $this->query;
        }
        /**
         * @param  string $query
         * @return self
         */
        public function setQuery(string $query): self {
            $this->query = $query;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getFragment(): ?string {
            return $this->fragment;
        }
        /**
         * @param  string $fragment
         * @return self
         */
        public function setFragment(string $fragment): self {
            $this->fragment = $fragment;

            return $this;
        }
        /**
         * @return string|null
         */
        public function getAuthority(): ?string {
            $user = $this->getUser();
            $password = $this->getPassword();
            $host = $this->getHost();
            $port = $this->getPort();

            $user_and_pass = null;

            if (!empty($user) || !empty($password)) {
                $user_and_pass = vsprintf('%s:%s');
            }

            $authority = '';

            if (!empty($user_and_pass)) {
                $authority .= vsprintf('%s@',[
                    $user_and_pass]);
            }

            $authority .= $host;

            if (!empty($port)) {
                $authority .= vsprintf('%s:%s',[
                    $authority,$port]);
            }

            if (empty($authority)) {
                return null;
            }

            return $authority;
        }
        /**
         * @return string|null
         */
        public function getUserInfo(): ?string {
            $user = $this->getUser();
            $password = $this->getPassword();

            $user_info = '';

            if (!empty($user) || !empty($password)) {
                $user_info = vsprintf('%s:%s');
            }

            if (empty($user_info)) {
                return null;
            }

            return $user_info;
        }
        /**
         * @param  string $scheme
         * return UriInterface
         * @return Uri
         */
        // public function withScheme($scheme): UriInterface {
        public function withScheme($scheme): Uri {
            $clone = clone $this;
            $clone->setScheme($scheme);

            return $clone;
        }
        /**
         * @param  string $user
         * @param  string $password
         * return UriInterface
         * @return Uri
         */
        // public function withUserInfo(string $user,string $password = null): UriInterface {
        public function withUserInfo(string $user,string $password = null): Uri {
            $clone = clone $this;
            $clone->setUser($user);
            $clone->setPassword($password);

            return $clone;
        }
        /**
         * @param  string $host
         * return UriInterface
         * @return Uri
         */
        // public function withHost(string $host): UriInterface {
        public function withHost(string $host): Uri {
            $clone = clone $this;
            $clone->setHost($host);

            return $clone;
        }
        /**
         * @param  int $port
         * return UriInterface
         * @return Uri
         */
        // public function withPort(int $port): UriInterface {
        public function withPort(int $port): Uri {
            $clone = clone $this;
            $clone->setPort($port);

            return $clone;
        }
        /**
         * @param string $path
         * return UriInterface
         * @return Uri
         */
        // public function withPath(string $path): UriInterface {
        public function withPath(string $path): Uri {
            $clone = clone $this;
            $clone->setPath($path);

            return $clone;
        }
        /**
         * @param string $query
         * return UriInterface
         * @return Uri
         */
        // public function withQuery(string $query): UriInterface {
        public function withQuery(string $query): Uri {
            $clone = clone $this;
            $clone->setQuery($query);

            return $clone;
        }
        /**
         * @param string $fragment
         * return UriInterface
         * @return Uri
         */
        // public function withFragment(string $fragment): UriInterface {
        public function withFragment(string $fragment): Uri {
            $clone = clone $this;
            $clone->setFragment($fragment);

            return $clone;
        }
        /**
         * @return string
         */
        public function __toString(): string {
            $scheme = $this->getScheme();
            $authority = $this->getAuthority();
            $path = $this->getPath();
            $query = $this->getQuery();
            $fragment = $this->getFragment();

            $url_render = '';

            if (!empty($scheme)) {
                $url_render .= vsprintf('%s:',[$scheme,]);
            }

            if (!empty($authority)) {
                $url_render .= vsprintf('//%s',[$authority,]);
            }

            $url_render .= $path;

            if (!empty($query)) {
                $url_render .= vsprintf('?%s',[$query,]);
            }

            if (!empty($fragment)) {
                $url_render .= vsprintf('#%s',[$fragment,]);
            }

            return $url_render;
        }
    }
}
