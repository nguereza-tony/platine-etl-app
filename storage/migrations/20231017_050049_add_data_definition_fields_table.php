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
             
             $table->string('column')
                 ->description('The database column if any');
             
             $table->string('transformer')
                 ->description('The transformer to use');
             
             $table->string('parameters')
                 ->description('The transformer parameters pipe (|) separated');
             
             $table->integer('position')
                 ->description('The field position (sort order)')
                 ->defaultValue(1)
                 ->notNull();
             
            $table->string('default_value')
                 ->description('The field default value');
             
            $table->integer('data_definition_id')
                ->description('The data definition')
                 ->notNull();
            
            $table->integer('enterprise_id')
                ->description('The system company')
                ->notNull();

            $table->integer('user_id')
                ->description('The user')
                ->notNull();

            $table->timestamps();

            $table->foreign('data_definition_id')
                ->references('data_definitions', 'id')
                ->onDelete('NO ACTION');
            
            $table->foreign('enterprise_id')
                ->references('enterprises', 'id')
                ->onDelete('NO ACTION');

            $table->foreign('user_id')
                ->references('users', 'id')
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