<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function () {
    return Auth::user();
});

$routesLookup = function ($ns, $path) {
    if (!file_exists($path)) return;
    $dir = new DirectoryIterator($path);
    foreach ($dir as $file) {
        /** @var SplFileInfo $file */
        $fileName = $file->getBasename('.php');
        if ($file->isFile() && $fileName{0} != '.' && $file->getExtension() == 'php' && $fileName != 'Controller') {
            $class = $ns . $fileName;
            if (class_exists($class) && method_exists($class, 'provideRoutes')) {
                $routes = forward_static_call([$class, 'provideRoutes']);
                $allVerbs = ['get', 'post', 'put', 'delete', 'patch'];
                if (is_array($routes)) {
                    foreach ($routes as $uri => $specs) {
                        $usedVerbs = [];
                        foreach ($specs as $verb => $conf) {
                            $action = (is_array($conf)) ? $conf[0] : $conf;
                            $name = (is_array($conf) && isset($conf[1])) ? $conf[1] : null;
                            if ($verb == 'others') {
                                $verb = array_diff($allVerbs, $usedVerbs);
                            }
                            $route = Route::match($verb, $uri, $action);
                            $usedVerbs = array_merge($usedVerbs, (array)$verb);
                            if (!empty($name)) {
                                $route->name($name);
                            }
                        }
                    }
                }
            }
        }
    }
};

$routesLookup('\App\Http\Controllers\Api\\', app_path('Http/Controllers/Api/'));

