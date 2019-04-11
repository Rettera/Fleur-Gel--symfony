<?php
// src/DataFixtures/FakerFixtures.php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;

use Faker;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

    	 // On configure dans quelles langues nous voulons nos données
        $faker = Faker\Factory::create('fr_FR');

        // on créé 10 Article
        for ($i = 0; $i < 40; $i++) {
            $article = new Article();
            $article->setTitle($faker-> catchPhrase )
            		->setcontent($faker->realText($maxNbChars = 2000, $indexSize = 2))
            		->setimage($faker->imageUrl($width = 640, $height = 480, 'nature'))
                    ->setcreatedAt($faker->dateTime($max = 'now', $timezone = null))
                    ->setAuteur($faker-> firstNameMale)
                    ->setCategory($faker-> randomElement($array = array ('Eté','Hiver','Printemps','Automne')) );
           	$manager -> persist($article);
        }

        $manager->flush();
       
    }
}
