<?php
namespace HazeDevelopment;

use Session;
use Request;
use Route;
use Log;
use Auth;
use Input;

class UserBehavior extends Route
{
    /**
   * 
   * @var Singleton
   */
    private static $instance;

    /**
    *
    * @var Array
    */
    private static $banned;

    /**
    *
    * @var String
    */
    private static $baseRouteName;

    /**
    *
    * @var String
    */
    private static $untracked;

    /**
    *
    * @var Array
    */
    private static $defaultBanned;

    public function __construct()
    {
        if(!Session::has('user_behavior'))
        {
            Session::put('user_behavior', array());
        }


        self::$defaultBanned = config('userbehavior.banned_routes');
        self::$baseRouteName = config('userbehavior.base_route');
        self::$untracked = config('userbehavior.untracked');
    }

    public static function init($bannedlist = array())
    {
        Log::info('UserBehavior Initiated');
        if(is_null(self::$instance))
        {
            self::$instance = new self();
        }

        self::$banned = array_merge(self::$defaultBanned, $bannedlist);

        return self::$instance;
    }

    public static function all()
    {
        return Session::get("user_behavior");
    }

    public static function getLastUrl()
    {
        $user_behavior = Session::get("user_behavior");
        $amount = count($user_behavior);

        if($amount > 0)
        {
            $last = $user_behavior[$amount-1];
            return route($last['route'], $last['parameters']);
        }

        return false;
    }

    public static function getLastBehavior($user_behavior = false)
    {
        if(!$user_behavior)
        {
            $user_behavior = Session::get("user_behavior");    
        }
        
        $amount = count($user_behavior);

        if($amount > 0)
        {
            $last = $user_behavior[$amount-1];
            return $last;
        }

        return false;
    }

    public static function getValidRoute($count = 1)
    {
        $user_behavior = Session::get("user_behavior");
        $amount = count($user_behavior);

        if($amount > 0 && $count <= 10)
        {
            if($count <= $amount)
            {
                $number = $amount-$count;
            }
            else
            {
                $number = 0;
            }

            $route = $user_behavior[$number];

            if(in_array($route['route'], self::$banned) || $route['method'] != 'GET' || !Auth::check() && $route['middleware'] == 'auth')
            {
                return self::getValidRoute($count+1);
            }

            return array('route' => $route['route'], 'parameters' => $route['parameters']);
        }

        return array('route' => self::$baseRouteName, 'parameters' => []);
    }

    public static function saveRoute($forced = false)
    {

        $user_behavior = Session::pull('user_behavior');

        $user_behavior = array_slice($user_behavior, 
                                    -(config('userbehavior.max_tracking'))
                                    );

        $currentRoute = (Array)Route::getCurrentRoute(); //hack it.

        if(count($currentRoute) > 0)
        {
            $method = $currentRoute["\x00*\x00" . 'methods'][0];
            $action = $currentRoute["\x00*\x00" . 'action'];
            $parameters = $currentRoute["\x00*\x00" . 'parameters'];
            
            $action['as'] = (isset($action['as']) ? $action['as'] : 'none');
            $middleware = (isset($action['middleware']) ? $action['middleware'] : false);

            $untracked = false;

            if(isset($action) || $forced == true)
            {
                foreach(self::$untracked as $untrack)
                {
                    if (fnmatch($untrack, $action['as'])) {
                        $untracked = true;
                    }
                }

                $lastBehavior = self::getLastBehavior($user_behavior);
                if($lastBehavior['route'] != $action['as'] && !$untracked)
                {
                    $user_behavior[] = array('route' => $action['as'], 
                                            'parameters' => $parameters, 
                                            'method' => $method, 
                                            'full_url' => Request::url(), 
                                            'url' => Request::path(), 
                                            'middleware' => $middleware, 
                                            'input' => Input::except('_token'),
                                            'prefix' => $action['prefix']
                                            );
                }
            }

            Session::reflash();
            Session::put('user_behavior', $user_behavior);
            Session::save();
        }
    }

    public function __destruct()
    {
        self::saveRoute();
    }
}