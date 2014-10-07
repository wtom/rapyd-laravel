<?php namespace Zofe\Rapyd;

/**
 * Class Router
 * the rapyd router, works "before" laravel router to check uri/query string
 * it set widgets status / actions.
 *
 * @package Zofe\Rapyd
 *
 * @method public static get($uri=null, $query=null, Array $route)
 * @method public static post($uri=null, $query=null, Array $route)
 * @method public static patch($uri=null, $query=null, Array $route)
 * @method public static put($uri=null, $query=null, Array $route)
 * @method public static delete($uri=null, $query=null, Array $route)
 */
class Router
{
    public static $routes = array();
    public static $qs = array();
    public static $remove = array();
    public static $methods = array();
    public static $callbacks = array();

    /**
     * little bit magic here, to catch all http methods to define a named route
     * <code>
     *    Router::get(null, 'page=(\d+)', array('as'=>'page', function ($page) {
     *        //with this query string: ?page=n  this closure will be triggered
     *    }));
     * </code>
     * @param $method
     * @param $params
     * @return static
     */
    public static function __callstatic($method, $params)
    {
        self::checkParams($method, $params);

        $uri = ltrim($params[0],"/");
        $qs = $params[1];

        $name = $params[2]['as'];
        self::$routes[$name] = $uri;
        self::$qs[$name] = $qs;
        self::$remove[$name] = array();
        self::$methods[$name] = strtoupper($method);
        self::$callbacks[$name] = $params[2][0];

        return new static();
    }

    /**
     * dispatch the router: check for all defined routes and call closures
     */
    public static function dispatch()
    {
        $uri = \Request::path();
        $qs = parse_url(\Request::fullUrl(), PHP_URL_QUERY);
        $method = \Request::method();

        foreach (self::$routes as $name=>$route) {

            $matched = array();
            if ($route=='' || preg_match('#' . $route . '#', $uri, $matched) && self::$methods[$name] == $method) {

                array_shift($matched);

                if (self::$qs[$name]!='' && preg_match('#' . self::$qs[$name] . '#', $qs, $qsmatched)) {
                    
                    array_shift($qsmatched);

                    $matched = array_merge($matched, $qsmatched);
                    call_user_func_array(self::$callbacks[$name], $matched);

                } elseif (self::$qs[$name] == '') {
                    call_user_func_array(self::$callbacks[$name], $matched);
                }
            }

        }
    }

    
    public static function isRoute($name, $params)
    {
        $uri = \Request::path();
        $qs = parse_url(\Request::fullUrl(), PHP_URL_QUERY);
        $method = \Request::method();
        $route = @self::$routes[$name];

        $matched = array();
        if ($route=='' || preg_match('#' . $route . '#', $uri, $matched) && self::$methods[$name] == $method) {

            array_shift($matched);

            if (self::$qs[$name]!='' && preg_match('#' . self::$qs[$name] . '#', $qs, $qsmatched)) {

                array_shift($qsmatched);

                $matched = array_merge($matched, $qsmatched);
                if ($matched == $params)
                    return true;

            } elseif (self::$qs[$name] == '') {
                if ($matched == $params)
                    return true;

            }
        }
        return false;
    }
    
    
    /**
     * check if route method call is correct
     * @param $method
     * @param $params
     */
    private static function checkParams($method, $params)
    {
        if (! in_array($method, array('get','post','patch','put','delete')))
            throw new \BadMethodCallException("valid methods are 'get','post','patch','put','delete'");

        if (count($params)<3 ||
            !is_array($params[2]) ||
            !array_key_exists('as', $params[2]) ||
            !array_key_exists('0', $params[2]) ||
            !($params[2][0] instanceof \Closure) )
            throw new \InvalidArgumentException('third parameter should be an array containing a
                                                   valid callback: array(\'as\'=>\'routename\', function () { })  ');

    }

    /**
     * queque to remove from url one or more named route
     *
     * @return static
     */
    public function remove()
    {
        $routes = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

        end(self::$routes);
        self::$remove[key(self::$routes)] = $routes;

        return new static();
    }

    /**
     * return a link starting from routename and params
     * like laravel link_to_route() but working with rapyd widgets/params
     *
     * @param $name route name
     * @param $param  one or more params required by the route
     * @return string
     */
    public static function linkRoute($name, $params, $url = null)
    {
        $url = ($url != '') ? $url : \Request::fullUrl();

        $url_arr = explode('?', $url);
        $url = $url_arr[0];
        $qs = (isset($url_arr[1])) ? $url_arr[1] : '';
        
        
        if (!is_array($params)) $params = (array)$params;
        //If required we remove other routes (from segments or qs)
        if (count(self::$remove[$name])) {
            foreach (self::$remove[$name] as $route) {
                if (self::$routes[$route])
                    $url = preg_replace('#(\/?)'.self::$routes[$route].'#', '', $url);
                if (self::$qs[$route]) {
                    $url = preg_replace('#(&?)'.self::$qs[$route].'#', '', $url);
                }

            }
        }


        //if this route works with uri
        //I check for all params to build the append segment with given params,
        //then I strip current rule and append new one.
        if (self::$routes[$name]) {
            $append =  self::$routes[$name];
            if (preg_match_all('#\(.*\)#U',$append, $matches)) {
                foreach ($params as $key=>$param) {
                    $append = str_replace($matches[0][$key],$param, $append);
                }
            }
            $url = preg_replace('#(\/?)'.self::$routes[$name].'#', '', $url);
            $url = ltrim($url."/".$append,'/');

        }

        //if this route works on query string
        //I check for all params to buid the "append" string with given params,
        //then I strip current rule and append the new one.
        if (self::$qs[$name]) {
            $append =  self::$qs[$name];
            if (preg_match_all('#\(.*\)#U',$append, $matches)) {
                foreach ($params as $key=>$param) {
                    $append = str_replace($matches[0][$key],$param, $append);
                }
            }
            $qs = preg_replace('#(&?)'.self::$qs[$name].'#', '', $qs);
            $qs = str_replace('&&','&',$qs);
            $qs = rtrim($qs, '&');
            $qs =  $qs .'&'.$append;
            $qs = ltrim($qs, '&');
            
        }

        if ($qs != '') $qs = '?'.$qs;
            
        return $url.$qs;
    }
    
}
