<?php

namespace App\DataFixtures;

use App\Entity\Term;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    protected $faker;

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create();
        $tag84 = $manager->find('App:Tag', 84);
        $tag88 = $manager->find('App:Tag', 88);

        for ($i = 0; $i < 35; $i++) {
            if ($i % 2 == 0) {
                $tag = $tag84;
            } else {
                $tag = $tag88;
            }
            $term = new Term();
            $term->setWord($this->faker->word);
            $term->setTranslation($this->faker->word);
            $term->setAddAt(new \DateTime());

            $term->setTag($tag);

            $manager->persist($term);
        }
        $manager->flush();
    }
}
