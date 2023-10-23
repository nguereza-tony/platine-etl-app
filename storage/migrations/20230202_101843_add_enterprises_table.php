<?php

declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddEnterprisesTable20230202101843 extends AbstractMigration
{
    public function up(): void
    {
      //Action when migrate up
        $this->create('enterprises', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();

            $table->enum('status', ['Y', 'N'])
                 ->notNull();

            $table->integer('logo_id')
                ->description('The logo file');

            $table->integer('company_id')
                ->description('The related company');

            $table->integer('role_id')
                ->description('Access role');

            $table->timestamps();

            $table->foreign('role_id')
                ->references('roles', 'id')
                ->onDelete('NO ACTION');

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('enterprises');
    }
}
