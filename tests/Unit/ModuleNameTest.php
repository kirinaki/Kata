<?php

namespace Kata\Tests\Unit;

use Kata\Modules\ModuleName;
use PHPUnit\Framework\TestCase;

class ModuleNameTest extends TestCase
{
    public function test_normaliza_a_studly_case(): void
    {
        $this->assertSame('MiModulo', ModuleName::fromInput('mi-modulo')->studly);
        $this->assertSame('MiModulo', ModuleName::fromInput('mi_modulo')->studly);
        $this->assertSame('MiModulo', ModuleName::fromInput('mi modulo')->studly);
        $this->assertSame('Blog', ModuleName::fromInput('blog')->studly);
    }

    public function test_deriva_kebab_y_lower(): void
    {
        $name = ModuleName::fromInput('MiModulo');

        $this->assertSame('mi-modulo', $name->kebab());
        $this->assertSame('mimodulo', $name->lower());
    }

    public function test_deriva_namespaces_y_clase_del_provider(): void
    {
        $name = ModuleName::fromInput('Blog');

        $this->assertSame('Modules\\Blog', $name->rootNamespace());
        $this->assertSame('Modules\\Blog\\Providers', $name->providerNamespace());
        $this->assertSame('BlogServiceProvider', $name->providerClass());
        $this->assertSame('Modules\\Blog\\Providers\\BlogServiceProvider', $name->providerFqcn());
    }

    public function test_ruta_relativa_y_to_string(): void
    {
        $name = ModuleName::fromInput('Shop');

        $this->assertSame('modules/Shop', $name->relativePath());
        $this->assertSame('Shop', (string) $name);
    }
}
