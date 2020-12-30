<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VideoFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getVideoData() as [$title, $path, $category_id]) {
            $duration = random_int(10, 300);
            $category = $manager->getRepository(Category::class)->find($category_id);
            $video = new Video();
            $video->setTitle($title);
            $video->setPath($path);
            $video->setCategory($category);
            $video->setDuration($duration);
            $manager->persist($video);
        }

        $manager->flush();
    }

    private function getVideoData()
    {
        return [
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'nangamxadan.mp4', 27],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'emngyenlamay.mp4', 28],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'nholoptruong.mp4', 29],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'nholoptruong.mp4', 30],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'emngyenlamay.mp4', 31],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'emngyenlamay.mp4', 32],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'emngyenlamay.mp4', 33],
            ['[ AMV ] Nhỏ Lớp Trưởng - Nightcore', 'emngyenlamay.mp4', 34]
        ];
    }
}
