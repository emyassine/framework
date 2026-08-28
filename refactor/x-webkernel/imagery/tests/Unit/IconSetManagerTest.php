<?php

declare(strict_types=1);

namespace Webkernel\Imagery\Tests\Unit;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Webkernel\Imagery\IconSetManager;
use Webkernel\Imagery\Tests\TestCase;

final class IconSetManagerTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $manager = new IconSetManager;

        $this->assertInstanceOf(IconSetManager::class, $manager);
    }

    #[Test]
    public function it_returns_array_of_set_names(): void
    {
        $manager = new IconSetManager;
        $set_names = $manager->get_set_names();

        $this->assertIsArray($set_names);
    }

    #[Test]
    public function it_returns_collection_of_icons(): void
    {
        $manager = new IconSetManager;
        $icons = $manager->get_icons();

        $this->assertInstanceOf(Collection::class, $icons);
    }

    #[Test]
    public function it_can_search_icons(): void
    {
        $manager = new IconSetManager;
        $results = $manager->search_icons('user');

        $this->assertInstanceOf(Collection::class, $results);
    }

    #[Test]
    public function it_can_get_icons_for_set(): void
    {
        $manager = new IconSetManager;
        $set_names = $manager->get_set_names();

        if (count($set_names) > 0) {
            $icons = $manager->get_icons_for_set($set_names[0]);
            $this->assertIsArray($icons);
        } else {
            $this->markTestSkipped('No icon sets installed');
        }
    }

    #[Test]
    public function it_returns_sets_with_metadata(): void
    {
        $manager = new IconSetManager;
        $sets = $manager->get_sets();

        $this->assertIsArray($sets);

        if (count($sets) > 0) {
            $first_set = array_values($sets)[0];
            $this->assertArrayHasKey('name', $first_set);
            $this->assertArrayHasKey('prefix', $first_set);
        }
    }
}
