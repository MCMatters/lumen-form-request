<?php

declare(strict_types = 1);

namespace McMatters\LumenFormRequest;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use function array_filter, is_array;

/**
 * Class ServiceProvider
 *
 * @package McMatters\LumenFormRequest
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->app->afterResolving(FormRequest::class, function ($resolved) {
            $resolved->validate();
        });

        $this->app->resolving(
            FormRequest::class,
            function ($request, $app) {
                $baseRequest = $app->make('request');

                $files = $baseRequest->files->all();

                $request->initialize(
                    $baseRequest->query->all(),
                    $baseRequest->request->all(),
                    $baseRequest->attributes->all(),
                    $baseRequest->cookies->all(),
                    is_array($files) ? array_filter($files) : $files,
                    $baseRequest->server->all(),
                    $baseRequest->getContent()
                );

                $request->setJson($baseRequest->json());

                if ($session = $baseRequest->getSession()) {
                    $request->setLaravelSession($session);
                }

                $request->setUserResolver($baseRequest->getUserResolver());

                $request->setRouteResolver($baseRequest->getRouteResolver());
            }
        );
    }
}
