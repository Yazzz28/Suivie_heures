<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Work;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTime;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private \Faker\Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // --- ADMIN ---
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('Principal');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $manager->persist($admin);

        // --- USERS ---
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setFirstName($this->faker->firstName());
            $user->setLastName($this->faker->lastName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
            $manager->persist($user);
            $users[] = $user;
        }

        // --- WORK ENTRIES ---
        foreach ($users as $user) {
            for ($m = 0; $m < 3; $m++) {
                $startDate = (new DateTime("first day of -$m month"))->setTime(0, 0);
                $endDate = (new DateTime("last day of -$m month"))->modify('+1 day');

                $interval = new \DateInterval('P1D');
                $period = new \DatePeriod($startDate, $interval, $endDate);

                foreach ($period as $day) {
                    if (in_array($day->format('N'), [6, 7])) {
                        continue;
                    }

                    $workEntry = new Work();
                    $date = clone $day;

                    $start = (clone $date)->setTime(rand(7, 9), 0);
                    $end = (clone $start)->modify('+'.rand(6, 9).' hours');

                    $workEntry->setUser($user);
                    $workEntry->setDate($date);
                    $workEntry->setStartTime($start);
                    $workEntry->setEndTime($end);
                    $workEntry->setComment($this->faker->optional()->sentence());

                    // Pause aléatoire (50%)
                    $pauseDuration = 0;
                    if (rand(0, 1)) {
                        $pauseStart = (clone $start)->modify('+'.rand(90, 180).' minutes');
                        $pauseDuration = rand(15, 60);
                        $pauseEnd = (clone $pauseStart)->modify("+$pauseDuration minutes");

                        if ($pauseEnd < $end) {
                            $workEntry->setPauseStart($pauseStart);
                            $workEntry->setPauseEnd($pauseEnd);
                        } else {
                            $pauseDuration = 0;
                        }
                    }

                    // Transports (0 à 3)
                    $workEntry->setNumberOfTransport(rand(0, 3));

                    // Congés
                    if (rand(0, 20) === 0) {
                        $workEntry->setDayOf(true);
                    } elseif (rand(0, 30) === 0) {
                        $workEntry->setDayOfWhitoutSolde(true);
                    }

                    $manager->persist($workEntry);
                }
            }
        }

        $manager->flush();
    }
}
