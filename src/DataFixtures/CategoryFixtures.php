<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getMainCategoriesData() as [$name]) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
        }

        $manager->flush();
    }

    private function getMainCategoriesData()
    {
        return [
            ['Pop'],
            ['Pop Ballad'],
            ['Rap'],
            ['Rock'],
            ['HipHop'],
            ['Vinahouse'],
            ['EDM'],
            ['Edm Tropical House'],
            ['Remix'],
            ['Cover'],
            ['Lofi']
        ];
    }
}
