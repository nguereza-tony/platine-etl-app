<?php
declare(strict_types=1);

namespace Platine\Framework\Migration\Seed;

use Platine\Framework\Migration\Seed\AbstractSeed;

class UserDataSeed extends AbstractSeed
{

    public function run(): void
    {
      //Action when run the seed
      
        $data = [
    0 => [
        'id' => 1,
        'username' => 'tnh',
        'email' => 'tnh@gmail.com',
        'password' => '$2y$10$WbxqyK4eqCEvPddenrcHneu4rqmbHlDpZQSweO.wlYmbhnWXyLLmO',
        'status' => 'A',
        'lastname' => 'Super',
        'firstname' => 'Admin',
        'role' => NULL,
        'created_at' => '2023-10-16 19:48:24',
        'updated_at' => '2023-10-16 19:49:14',
    ],
];
        foreach ($data as $row) {
            $this->insert($row)->into('users');
        }
        
    }
}