# Kata CLI

[![Tests](https://github.com/kirinaki/Kata/actions/workflows/tests.yml/badge.svg)](https://github.com/kirinaki/Kata/actions/workflows/tests.yml)

Generador de módulos para Laravel 12. **Kata** provee comandos Artisan para crear
módulos autocontenidos dentro de una carpeta `modules/`, a partir de *scaffolds*
predefinidos (backend simple, con migraciones, frontend SSR con Blade, o SPA con
Inertia + React).

Cada módulo se genera con su propio `ServiceProvider`, se registra automáticamente
en `bootstrap/providers.php` (mecanismo de Laravel 11/12) y queda cargable mediante
el namespace PSR-4 `Modules\`.

---

## Requisitos

- PHP `^8.2`
- Laravel `^12.0`
- [pnpm](https://pnpm.io/) `>=9` (para los scaffolds de frontend)
- Para el scaffold `frontend-spa`: `inertiajs/inertia-laravel` (Kata intenta
  instalarlo automáticamente si falta).

---

## Instalación

El package se distribuye vía un repositorio VCS de Composer.

En el `composer.json` del proyecto, señala el repositorio y añade la dependencia:

```json
{
    "require": {
        "kirinaki/kata": "^0.6"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kirinaki/Kata.git"
        }
    ]
}
```

```bash
composer require "kirinaki/kata:^0.6"
```

El `KataServiceProvider` se descubre automáticamente (package discovery).

---

## Puesta en marcha

Inicializa la estructura de módulos con:

```bash
php artisan kata:install
```

Esto:

1. Crea la carpeta `modules/` en la raíz del proyecto.
2. Registra el namespace `Modules\ => modules/` en el `autoload` PSR-4 del
   `composer.json`, junto a `App\`.

Luego regenera el autoload:

```bash
composer dump-autoload
```

---

## Comandos

| Comando | Descripción |
|---|---|
| `php artisan kata:install` | Crea `modules/` y registra el autoload `Modules\`. |
| `php artisan kata:create <nombre> <scaffold>` | Genera un módulo a partir de un scaffold. |
| `php artisan kata:remove <nombre> [--force]` | Elimina un módulo y lo desregistra de `bootstrap/providers.php`. |
| `php artisan kata:hello [nombre]` | Comando de ejemplo/salud del package. |

### `kata:create`

```bash
php artisan kata:create Blog basic
php artisan kata:create Catalog core
php artisan kata:create Shop frontend-ssr
php artisan kata:create Dashboard frontend-spa
```

- El `<nombre>` se normaliza a **StudlyCase** (`mi-modulo` → `MiModulo`).
- Si el módulo ya existe, pide confirmación para sobrescribir.
- El provider se registra automáticamente en `bootstrap/providers.php`.

### `kata:remove`

```bash
php artisan kata:remove Blog          # pide confirmación
php artisan kata:remove Blog --force  # sin confirmación
```

Desregistra el provider **antes** de borrar la carpeta, evitando referencias
huérfanas que romperían el arranque de Laravel. Es idempotente: también sirve para
reparar un registro huérfano (provider registrado pero carpeta ya borrada).

---

## Scaffolds

### `basic`
Módulo mínimo de backend.

```
modules/{Nombre}/
└── Providers/{Nombre}ServiceProvider.php   # register() y boot() vacíos
```

### `core`
Backend con soporte de migraciones.

```
modules/{Nombre}/
├── Providers/{Nombre}ServiceProvider.php   # loadMigrationsFrom(...)
└── Database/Migrations/
```

### `frontend-ssr`
Renderizado en servidor con Blade + Vite + Tailwind v4.

```
modules/{Nombre}/
├── Providers/{Nombre}ServiceProvider.php   # loadRoutesFrom + loadViewsFrom
├── Routes/web.php
├── Resources/
│   ├── Views/index.blade.php               # @vite(...)
│   └── Assets/{app.ts, app.css}
├── vite.config.ts                          # salida aislada en public/build/modules/{kebab}
└── package.json                            # motor pnpm
```

### `frontend-spa`
SPA con **Inertia + React + TypeScript + Tailwind v4**, siguiendo una estructura
[Feature-Sliced Design (FSD)](https://feature-sliced.design/).

```
modules/{Nombre}/
├── Providers/{Nombre}ServiceProvider.php   # loadRoutesFrom + loadViewsFrom
├── Routes/web.php                          # middleware Inertia aplicado SOLO a las rutas del módulo
├── Http/Middlewares/HandleInertiaRequests.php
├── Resources/
│   ├── App/{app.tsx, main.css, index.blade.php}
│   ├── Pages/Home/Page.tsx                 # convención: Pages/**/Page.tsx
│   ├── Widgets/
│   ├── Features/
│   └── Shared/
├── vite.config.ts                          # react + tailwind, salida en public/build/modules/{kebab}
├── tsconfig.json                           # alias @app, @pages, @widgets, @features, @shared
└── package.json                            # motor pnpm, React 19, @inertiajs/react
```

**Convención de páginas:** cada página es una carpeta dentro de `Pages/` con un
archivo `Page.tsx`. El nombre usado en `Inertia::render('Home')` corresponde a la
carpeta (`Pages/Home/Page.tsx`). El entry `app.tsx` las resuelve con
`import.meta.glob('../Pages/**/Page.tsx')`.

**Aislamiento entre SPAs:** el middleware de Inertia se aplica únicamente a las
rutas del módulo (no al grupo `web` global) y cada módulo tiene su propia
`rootView` y su propio bundle en `public/build/modules/{kebab}`. Dos SPAs con una
página `Home` **no colisionan**.

---

## Frontend (pnpm)

Los scaffolds de frontend traen su propio `package.json` y `vite.config.ts`. Vite
se ejecuta **desde el directorio del módulo** y compila a la carpeta pública de
Laravel, aislado por módulo (`public/build/modules/{kebab}`):

```bash
cd modules/{Nombre}
pnpm install
pnpm dev      # desarrollo
pnpm build    # producción
```

---

## Arquitectura

El package sigue principios SOLID. Las responsabilidades están separadas en
clases especializadas, cableadas por inyección de dependencias en
`KataServiceProvider`:

| Clase | Responsabilidad |
|---|---|
| `Enums\Scaffold` | Enum con la definición de cada scaffold (stub, carpetas, archivos, dependencias). |
| `Modules\ModuleName` | Value Object: normaliza el nombre y deriva namespaces, clase, rutas. |
| `Modules\StubRenderer` | Lee stubs y aplica reemplazos de placeholders. |
| `Modules\ProviderRegistry` (interface) | Contrato para registrar/desregistrar providers. |
| `Modules\ProviderRegistrar` | Implementación sobre `bootstrap/providers.php`. |
| `Modules\ModuleGenerator` | Orquesta la generación de un módulo. |
| `Modules\ModuleRemover` | Orquesta la eliminación de un módulo. |
| `Modules\InertiaInstaller` | Garantiza `inertiajs/inertia-laravel` (usa `ComposerRunner`). |
| `Composer\ComposerRunner` (interface) | Contrato para operaciones de Composer. |
| `Composer\ProcessComposerRunner` | Implementación con Symfony Process. |
| `Support\Reporter` (interface) | Abstracción de salida (desacopla los servicios de la consola). |
| `Support\ConsoleReporter` | Implementación sobre un comando Artisan. |

### Placeholders de los stubs

| Placeholder | Valor (ejemplo para `Blog`) |
|---|---|
| `{{ namespace }}` | `Modules\Blog\Providers` |
| `{{ class }}` | `BlogServiceProvider` |
| `{{ module }}` | `Blog` |
| `{{ nameKebab }}` | `blog` |
| `{{ nameLower }}` | `blog` |

Añadir un scaffold nuevo consiste en: crear sus stubs en `stubs/{scaffold}/`,
agregar el caso al enum `Scaffold` y cubrirlo en sus métodos `match`.

---

## Tests

```bash
# desde la raíz del proyecto
./vendor/bin/phpunit
```

- **Unitarios** (`tests/Unit/`): PHPUnit puro con dobles de prueba
  (`FakeComposerRunner`, `FakeProviderRegistry`, `SpyReporter`). No requieren
  Laravel arrancado.
- **Integración** (`tests/Feature/`): ejecutan los comandos `kata:*` reales contra
  la aplicación Laravel y verifican archivos generados y registro de providers.

> El package declara `orchestra/testbench` en `require-dev` para poder ejecutar los
> tests de integración de forma aislada si se distribuye como paquete independiente.

---

## Licencia

MIT.
