<?php

namespace Kata\Kata\Enums;

enum Scaffold: string
{
    case Basic = 'basic';
    case Core = 'core';
    case FrontendSsr = 'frontend-ssr';
    case FrontendSpa = 'frontend-spa';

    /**
     * Stub del ServiceProvider del scaffold.
     */
    public function providerStub(): string
    {
        return match ($this) {
            self::Basic => 'basic/service-provider.stub',
            self::Core => 'core/service-provider.stub',
            self::FrontendSsr => 'frontend-ssr/service-provider.stub',
            self::FrontendSpa => 'frontend-spa/service-provider.stub',
        };
    }

    /**
     * Carpetas vacías (con .gitkeep) que crea el scaffold.
     *
     * @return array<int, string>
     */
    public function directories(): array
    {
        return match ($this) {
            self::Basic => [],
            self::Core => ['Database/Migrations'],
            self::FrontendSsr => ['Resources/Views'],
            self::FrontendSpa => [
                'Resources/Widgets',
                'Resources/Features',
                'Resources/Shared',
            ],
        };
    }

    /**
     * Archivos a generar desde stubs: mapa stub => destino relativo al módulo.
     *
     * @return array<string, string>
     */
    public function files(): array
    {
        return match ($this) {
            self::Basic, self::Core => [],
            self::FrontendSsr => [
                'frontend-ssr/web.stub' => 'Routes/web.php',
                'frontend-ssr/vite.config.ts.stub' => 'vite.config.ts',
                'frontend-ssr/package.json.stub' => 'package.json',
                'frontend-ssr/index.blade.stub' => 'Resources/Views/index.blade.php',
                'frontend-ssr/app.ts.stub' => 'Resources/Assets/app.ts',
                'frontend-ssr/app.css.stub' => 'Resources/Assets/app.css',
            ],
            self::FrontendSpa => [
                'frontend-spa/web.stub' => 'Routes/web.php',
                'frontend-spa/middleware.stub' => 'Http/Middlewares/HandleInertiaRequests.php',
                'frontend-spa/vite.config.ts.stub' => 'vite.config.ts',
                'frontend-spa/tsconfig.json.stub' => 'tsconfig.json',
                'frontend-spa/package.json.stub' => 'package.json',
                'frontend-spa/index.blade.stub' => 'Resources/App/index.blade.php',
                'frontend-spa/app.tsx.stub' => 'Resources/App/app.tsx',
                'frontend-spa/main.css.stub' => 'Resources/App/main.css',
                'frontend-spa/home.page.stub' => 'Resources/Pages/Home/Page.tsx',
            ],
        };
    }

    /**
     * Indica si el scaffold requiere inertiajs/inertia-laravel.
     */
    public function requiresInertia(): bool
    {
        return $this === self::FrontendSpa;
    }

    /**
     * Lista de valores disponibles (para mensajes de ayuda/errores).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
