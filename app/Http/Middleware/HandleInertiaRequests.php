<?php

namespace App\Http\Middleware;

// use Illuminate\Foundation\Inspiring; // Elimina esta línea si no usas Inspiring
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Si no usas Inspiring, puedes eliminar estas dos líneas:
        // [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            // Si eliminaste Inspiring, también elimina esta línea:
            // 'quote' => ['message' => trim($message), 'author' => trim($author)],

            'auth' => [
                'user' => $request->user() ? [ // Asegúrate de que el usuario esté autenticado antes de acceder a sus propiedades
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    // Puedes añadir más propiedades del usuario aquí si las necesitas en el frontend
                ] : null, // Si no hay usuario autenticado, devuelve null
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // ¡¡¡ESTO ES LO MÁS IMPORTANTE PARA LOS MENSAJES FLASH!!!
            // Esto compartirá el array completo que envías con ->with('flash', [...])
            'flash' => fn () => $request->session()->get('flash', []),
        ]);
    }
}
