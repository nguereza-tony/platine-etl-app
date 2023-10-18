<?php
declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddDataDefinitionFieldsTable20231017050049 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('data_definition_fields', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();

            $table->string('field')
                 ->description('The definition field')
                 ->notNull();
            
             $table->string('name')
                 ->description('The field display name')
                 ->notNull();
             
             $table->integer('position')
                 ->description('The field position (sort order)')
                 ->defaultValue(1)
                 ->notNull();
             
            $table->string('default_value')
                 ->description('The field default value');
             
            $table->integer('parent_id')
                ->description('The field parent');

            $table->integer('data_mapping_id')
                ->description('The data mapping');
            
            $table->integer('data_definition_id')
                ->description('The data definition')
                 ->notNull();

            $table->timestamps();

            $table->foreign('data_mapping_id')
                ->references('data_mappings', 'id')
                ->onDelete('NO ACTION');
            
            $table->foreign('data_definition_id')
                ->references('data_definitions', 'id')
                ->onDelete('NO ACTION');
            
            $table->foreign('parent_id')
                ->references('data_definition_fields', 'id')
                ->onDelete('NO ACTION');

            $table->engine('INNODB'); 
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('data_definition_fields');
    }
}