<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Playlist;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'load-fixtures')]
class LoadFixturesCommand extends Command
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $randomMovieIds = [533535, 573435, 799583, 762441, 1309923, 929590, 1311546, 1022789, 293660, 1311550, 718821, 746036, 653346, 1048241, 748783, 383498, 786892, 774531, 1241674, 1160018, 519182, 1008409, 693134, 1277296, 1226578, 933090, 263115, 1111873, 1255350, 1051891, 932086, 280180, 967847, 9737, 948549, 438631, 560016, 150540, 671, 872585, 974262, 1083612, 8961, 508883, 437342, 1051896, 698687, 804616, 447332, 664, 897087, 639720, 945961, 365177, 1086747, 952022, 1209290, 38700, 842675, 823464, 1308757, 1020951, 7451, 635996, 1011985, 76170, 20352, 1041613, 866398, 1010600, 348, 618588, 1726, 1023922, 901315, 1175038, 634649, 36647, 838209, 974635, 278, 238, 240, 424, 389, 129, 19404, 155, 496243, 497, 372058, 680, 122, 13, 429, 769, 346, 667257, 12477, 1084736, 11216, 637, 550, 157336, 539, 372754, 598, 1058694, 510, 696374, 311, 704264, 120, 1160164, 4935, 324857, 724089, 568332, 40096, 255709, 121, 1891, 14537, 620249, 423, 244786, 761053, 378064, 807, 27205, 567, 569094, 274, 73, 128, 92321, 914, 12493, 105, 820067, 18491, 644479, 599, 207, 15804, 1139087, 3782, 101, 10494, 3082, 335, 901, 28, 77338, 29259, 1585, 975, 527641, 637920, 25237, 632632, 10376, 447362, 995133, 283566, 652837, 630566, 8587, 670, 533514, 299534, 299536, 508965, 490132, 42269, 618344, 265177, 315162, 504253, 110420, 572154, 635302, 290098, 441130, 16869, 654299, 98, 857, 24188, 694, 603, 37257, 354912, 361743, 50014, 284, 16672, 610892, 1124, 11324, 522924, 11, 185, 556574, 490, 324786, 5156, 476292, 797, 313106, 629, 620683, 26451, 77, 20334, 10098, 68718, 810693, 537061, 592350, 92060, 18148, 426, 1422, 111, 133919, 693134, 20941, 81481, 872, 575813, 289, 1026227, 422, 851644, 475557, 517814, 1398, 348, 489, 489, 399106, 791373, 606856, 103, 19, 37165, 666, 614, 458220, 398818, 518068, 406997, 600, 470044, 508442, 663558, 531428, 20914, 500, 55823, 996, 935, 762975, 600354, 100, 280, 38288, 755812, 411088, 838240, 843, 664767, 555604, 532067, 46738, 11878, 705, 655, 103663];

        for ($i = 0; $i <= 5; $i++) {
            $user = new User();
            $user->setUsername('user' . $i);
            $user->setEmail('user' . $i . '@mail.fr');
            $hashedPassword = $this->userPasswordHasher->hashPassword(
                $user,
                'UserPwd0!'
            );
            $user->setPassword($hashedPassword);
            $user->setIsVerified(true);
            $this->entityManager->persist($user);

            for ($j = 0; $j <= 3; $j++) {
                $playlist = new Playlist();
                $playlist->setName($user->getUsername() . ' n°' . $j);
                $playlist->setCreatedAt(new DateTimeImmutable());
                $playlist->setUser($user);
                $playlist->setIsPublic((bool)random_int(0, 1));
                for ($k = 0; $k < rand(4, 10); $k++) {
                    $playlist->addMovieId($randomMovieIds[array_rand($randomMovieIds)]);
                }

                $this->entityManager->persist($playlist);
            }
        }
        $this->entityManager->flush();

        $demoUser = new User();
        $demoUser->setUsername('Demo user');
        $demoUser->setEmail('demo@mail.fr');
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $demoUser,
            'Password0!'
        );
        $demoUser->setPassword($hashedPassword);
        for ($k = 0; $k < rand(4, 10); $k++) {
            $demoUser->addToFavorites($randomMovieIds[array_rand($randomMovieIds)]);
        }
        $demoUser->setIsVerified(true);
        $this->entityManager->persist($demoUser);
        $this->entityManager->flush();

        for ($j = 0; $j <= 4; $j++) {
            $playlist = new Playlist();
            $playlist->setName($demoUser->getUsername() . ' n°' . $j);
            $playlist->setCreatedAt(new DateTimeImmutable());
            $playlist->setUser($demoUser);
            $playlist->setIsPublic((bool)random_int(0, 1));
            for ($k = 0; $k < rand(4, 7); $k++) {
                $playlist->addMovieId($randomMovieIds[array_rand($randomMovieIds)]);
            }
            $this->entityManager->persist($playlist);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }
}
