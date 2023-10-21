<?php

declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddFilesTable20230202101922 extends AbstractMigration
{
    public function up(): void
    {
      //Action when migrate up
        $this->create('files', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();

            $table->string('name')
                 ->notNull();

            $table->string('real_name')
                 ->notNull();

            $table->string('type')
                 ->notNull();

            $table->string('note');

            $table->integer('size')
                 ->defaultValue(0)
                 ->description('size in byte')
                 ->notNull();

            $table->integer('revision')
                 ->defaultValue(0)
                 ->notNull();

            $table->timestamps();

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('files');
    }
}
