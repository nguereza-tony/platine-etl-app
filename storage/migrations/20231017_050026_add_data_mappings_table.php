<?php
declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddDataMappingsTable20231017050026 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('data_mappings', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();

            $table->string('type')
                 ->description('table name, entity name, etc.');

            $table->string('field')
                 ->description('The mapping field')
                 ->notNull();
            
             $table->string('name')
                 ->description('The field display name')
                 ->notNull();

            $table->integer('parent_id')
                ->description('The mapping parent');

            $table->timestamps();

            $table->foreign('parent_id')
                ->references('data_mappings', 'id')
                ->onDelete('NO ACTION');

            $table->engine('INNODB'); 
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('data_mappings');
    }
}