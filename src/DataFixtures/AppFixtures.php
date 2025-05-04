<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Work;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;
use DateTime;
use DateInterval;
use DatePeriod;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private \Faker\Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR');
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
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");
            $user->setFirstName($this->faker->firstName());
            $user->setLastName($this->faker->lastName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
            $manager->persist($user);
            $users[] = $user;
        }

        // --- WORK ENTRIES FOR EACH USER ---
        $monthsToGenerate = 3; // Nombre de mois précédents
        foreach ($users as $user) {
            for ($m = 0; $m < $monthsToGenerate; $m++) {
                $startDate = (new DateTime("first day of -{$m} month"))->setTime(0, 0);
                $endDate = (new DateTime("last day of -{$m} month"))->modify('+1 day');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($startDate, $interval, $endDate);

                foreach ($period as $day) {
                    // Exclure weekends
                    if (in_array((int)$day->format('N'), [6, 7])) {
                        continue;
                    }

                    $workEntry = new Work();
                    $workEntry->setUser($user);
                    $workEntry->setDate(clone $day);

                    // Heures de début entre 7h et 9h
                    $hourStart = rand(7, 9);
                    $startTime = (clone $day)->setTime($hourStart, 0);

                    // Durée de travail entre 6 et 9 heures
                    $duration = rand(6, 9);
                    $endTime = (clone $startTime)->modify("+{$duration} hours");

                    $workEntry->setStartTime($startTime);
                    $workEntry->setEndTime($endTime);

                    // Pause aléatoire 50%
                    if (rand(0, 1) === 1) {
                        $pauseAfter = rand(90, 180); // minutes après le début
                        $pauseStart = (clone $startTime)->modify("+{$pauseAfter} minutes");
                        $pauseDuration = rand(15, 60);
                        $pauseEnd = (clone $pauseStart)->modify("+{$pauseDuration} minutes");

                        if ($pauseEnd < $endTime) {
                            $workEntry->setPauseStart($pauseStart);
                            $workEntry->setPauseEnd($pauseEnd);
                        }
                    }

                    // Nombre de transports entre 0 et 3
                    $workEntry->setNumberOfTransport(rand(0, 3));

                    // Congés payés (1/20) ou sans solde (1/30)
                    if (rand(1, 20) === 1) {
                        $workEntry->setDayOf(true);
                    } elseif (rand(1, 30) === 1) {
                        $workEntry->setDayOfWhitoutSolde(true);
                    }

                    // Commentaire aléatoire
                    $workEntry->setComment($this->faker->optional(0.7)->sentence());

                    $manager->persist($workEntry);
                }
            }
        }

        $manager->flush();
    }
}
