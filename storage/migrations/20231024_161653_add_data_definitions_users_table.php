<?php
declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddDataDefinitionsUsersTable20231024161653 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('data_definitions_users', function (CreateTable $table) {
            $table->integer('user_id');
            $table->integer('data_definition_id');

            $table->primary(['user_id', 'data_definition_id']);

            $table->foreign('user_id')
                ->references('users', 'id')
                ->onDelete('CASCADE');

            $table->foreign('data_definition_id')
                ->references('data_definitions', 'id')
                ->onDelete('CASCADE');

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('data_definitions_users');
    }
}