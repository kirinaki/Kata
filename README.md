# Kata CLI

[![Tests](https://github.com/kirinaki/Kata/actions/workflows/tests.yml/badge.svg)](https://github.com/kirinaki/Kata/actions/workflows/tests.yml)

Generador de módulos para Laravel 12 y 13. **Kata** provee comandos Artisan para crear
módulos autocontenidos dentro de una carpeta `modules/`, a partir de *scaffolds*
predefinidos (backend simple, con migraciones, frontend SSR con Blade, islas de
React estilo Astro, o SPA con Inertia + React).

Cada módulo se genera con su propio `ServiceProvider`, se registra automáticamente
en `bootstrap/providers.php` (mecanismo de Laravel 11 en adelante) y queda cargable mediante
el namespace PSR-4 `Modules\`.

> **¿Por qué "Kata"?** El nombre proviene del japonés *kata* (型 / 形), el
> conjunto de movimientos predefinidos que se practican en disciplinas como el
> karate: patrones que, repetidos y perfeccionados, se convierten en la base para
> construir algo mayor. Igual que un kata, la librería parte de *scaffolds*
> prefijados y estructurados para generar código consistente y repetible sobre el
> que luego se construye cada aplicación.

---

## Requisitos

- PHP `^8.2` (`^8.3` si usas Laravel 13)
- Laravel `^12.0 || ^13.0`
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
        "kirinaki/kata": "^0.7"
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
composer require "kirinaki/kata:^0.7"
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
php artisan kata:create Blog frontend-ssr-islands-react
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

### `frontend-ssr-islands-react`
SSR con Blade + **islas de interactividad en React** (estilo [Astro](https://astro.build/)):
la página se renderiza en el servidor y los bloques interactivos son componentes React
que se montan de forma aislada en el cliente. Sin Inertia ni SPA completo.

```
modules/{Nombre}/
├── Providers/{Nombre}ServiceProvider.php   # loadRoutesFrom + loadViewsFrom + Blade::component
├── Routes/web.php
├── View/Components/Island.php              # componente <x-island> (puente Blade → React)
├── Resources/
│   ├── Views/
│   │   ├── index.blade.php                 # página SSR + <x-island>
│   │   └── components/island.blade.php     # vista del componente
│   ├── Islands/Counter.tsx                 # islas: un archivo por componente
│   └── Assets/{app.tsx, app.css}            # entry: escanea y monta/hidrata islas
├── vite.config.ts                          # react + tailwind, salida en public/build/modules/{kebab}
├── tsconfig.json                           # alias @assets, @islands
└── package.json                            # motor pnpm, React 19
```

El puente entre Blade y React es el componente **`<x-island>`**, registrado con dos
alias: el global `x-island` y el namespaced `x-{nombre}::island` (uso inequívoco
cuando hay varios módulos de islas).

```blade
{{-- Monta en el cliente con createRoot (sin HTML previo). --}}
<x-island component="Counter" :props="['initial' => 5]" />

{{-- Hidrata el HTML del slot con hydrateRoot (contenido visible sin JS). --}}
<x-island component="Counter" :props="['initial' => 10]">
    <p>Cargando contador…</p>
</x-island>
```

- `component` (obligatorio) se resuelve a un archivo dentro de `Resources/Islands/`:
  `Counter` → `Counter.tsx`, `Dashboard/Chart` → `Dashboard/Chart.tsx` (soporte anidado).
- `props` (opcional) se serializa a JSON seguro para HTML y se pasa tal cual al
  componente. Debe ser serializable a JSON.
- Sin contenido el entry monta la isla con `createRoot`; con slot la hidrata con
  `hydrateRoot` sobre el HTML ya renderizado. Para hidratar sin *warnings*, el slot
  debe contener HTML compatible con la salida de React (si no coincide, React
  re-renderiza en el cliente); en casos simples es preferible no usar slot.
- Si un componente no existe, se registra el error en consola sin afectar al resto
  de islas.

El entry `Resources/Assets/app.tsx` escanea los `[data-island]` del DOM y resuelve
las islas con `import.meta.glob('../Islands/**/*.tsx')`.

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
