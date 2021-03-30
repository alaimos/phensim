<?php

namespace App\Providers;

use App\Http\Middleware\CheckUserIsAdmin;
use App\Services\ApiDownloadService;
use App\Services\DashboardService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DashboardService::class, fn($app) => new DashboardService());
        $this->app->bind(ApiDownloadService::class, fn($app, $params) => new ApiDownloadService(array_shift($params)));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware(
            [
                CheckUserIsAdmin::class,
            ]
        );

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param  int  $perPage
         * @param  int  $total
         * @param  int  $page
         * @param  string  $pageName
         *
         * @return array
         */
        Collection::macro(
            'paginate',
            function ($perPage, $total = null, $page = null, $pageName = 'page') {
                $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

                return new LengthAwarePaginator(
                    $this->forPage($page, $perPage),
                    $total ?: $this->count(),
                    $perPage,
                    $page,
                    [
                        'path'     => LengthAwarePaginator::resolveCurrentPath(),
                        'pageName' => $pageName,
                    ]
                );
            }
        );
    }
}
