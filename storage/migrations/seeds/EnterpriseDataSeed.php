<?php
declare(strict_types=1);

namespace Platine\Framework\Migration\Seed;

use Platine\Framework\Migration\Seed\AbstractSeed;

class EnterpriseDataSeed extends AbstractSeed
{

    public function run(): void
    {
      //Action when run the seed
      
        $data = [
    0 => [
        'id' => 1,
        'status' => 'Y',
        'logo_id' => NULL,
        'company_id' => NULL,
        'role_id' => NULL,
        'created_at' => '2023-10-23 05:28:53',
        'updated_at' => NULL,
    ],
];
        foreach ($data as $row) {
            $this->insert($row)->into('enterprises');
        }
        
    }
}