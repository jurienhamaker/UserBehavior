<?php

namespace HazeDevelopment;

use Auth;
use Input;
use Request;
use Route;
use Session;

class UserBehavior extends Route
{
    /**
     * @var Singleton
     */
    private static $instance;

    /**
     * @var array
     */
    private static $banned;

    /**
     * @var string
     */
    private static $baseRouteName;

    /**
     * @var array
     */
    private static $defaultBanned;

    /**
     * @var array
     */
    private static $untracked;

    public function __construct()
    {
        if (!Session::has('user_behavior')) {
            Session::put('user_behavior', []);
        }

        self::$defaultBanned = (config('userbehavior.banned_routes') ? config('userbehavior.banned_routes') : []);
        self::$baseRouteName = (config('userbehavior.base_route') ? config('userbehavior.base_route') : []);
        self::$untracked = array_merge(['userbehavior/*'], (config('userbehavior.untracked') ? config('userbehavior.untracked') : []));
    }

    public static function init($bannedlist = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        self::$banned = array_merge(self::$defaultBanned, $bannedlist);

        return self::$instance;
    }

    public static function all()
    {
        return Session::get('user_behavior');
    }

    public static function getUntracked()
    {
        return self::$untracked;
    }

    public static function getLastUrl()
    {
        $user_behavior = Session::get('user_behavior');
        $amount = count($user_behavior);

        if ($amount > 0) {
            $last = $user_behavior[$amount - 1];

            return route($last['route'], $last['parameters']);
        }

        return false;
    }

    public static function getLastBehavior($user_behavior = false)
    {
        if (!$user_behavior) {
            $user_behavior = Session::get('user_behavior');
        }

        $amount = count($user_behavior);

        if ($amount > 0) {
            $last = $user_behavior[$amount - 1];

            return $last;
        }

        return false;
    }

    public static function getValidRoute($count = 1)
    {
        $user_behavior = Session::get('user_behavior');
        $amount = count($user_behavior);

        if ($amount > 0 && $count <= 10) {
            if ($count <= $amount) {
                $number = $amount - $count;
            } else {
                $number = 0;
            }

            $route = $user_behavior[$number];

            if (in_array($route['route'], (is_array(self::$banned) ? self::$banned : [])) || $route['method'] != 'GET' || !Auth::check() && in_array('auth', (is_array($route['middleware']) ? $route['middleware'] : []))) {
                return self::getValidRoute($count + 1);
            }

            return ['route' => $route['route'], 'parameters' => $route['parameters']];
        }

        return ['route' => self::$baseRouteName, 'parameters' => []];
    }

    public static function saveRoute($forced = false)
    {
        $user_behavior = Session::pull('user_behavior');

        $user_behavior = array_slice($user_behavior,
                                    -(config('userbehavior.max_tracking'))
                                    );

        $currentRoute = (Array) Route::getCurrentRoute(); //hack it.

        if (count($currentRoute) > 0) {
            $method = $currentRoute["\x00*\x00".'methods'][0];
            $action = $currentRoute["\x00*\x00".'action'];
            $parameters = $currentRoute["\x00*\x00".'parameters'];

            $action['as'] = (isset($action['as']) ? $action['as'] : 'none');
            $action['prefix'] = (isset($action['prefix']) ? $action['prefix'] : 'none');
            $middleware = (isset($action['middleware']) ? $action['middleware'] : false);

            $untracked = false;
            foreach (self::$untracked as $untrack) {
                if (fnmatch($untrack, Request::path())) {
                    $untracked = true;
                }
            }

            if (isset($action) && !in_array($action['as'], self::$banned) && !$untracked || $forced == true) {
                $lastBehavior = self::getLastBehavior($user_behavior);
                if ($lastBehavior['route'] != $action['as']) {
                    $user_behavior[] = ['route'          => $action['as'],
                                            'parameters' => $parameters,
                                            'method'     => $method,
                                            'full_url'   => Request::url(),
                                            'url'        => Request::path(),
                                            'middleware' => (is_array($middleware) ? $middleware : [$middleware]),
                                            'prefix'     => $action['prefix'],
                                            'input'      => Input::except('_token'), ];
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
