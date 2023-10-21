<?php
declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddDataDefinitionImportsTable20231021143048 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('data_definition_imports', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();
            
            $table->string('description');
            
            $table->integer('total')
                   ->description('Total record')
                    ->defaultValue(0)
                    ->notNull();
            
            $table->integer('processed')
                   ->description('Total record processed')
                    ->defaultValue(0)
                    ->notNull();
            
            $table->integer('error')
                   ->description('Total record failed/error')
                    ->defaultValue(0)
                    ->notNull();
            
            $table->enum('status', ['P', 'C', 'E', 'X'])
                 ->description('P=Pending, C=Processed, E=Error, X=Cancelled')
                 ->defaultValue('P')
                 ->notNull();
            
            $table->integer('data_definition_id')
                ->description('The data definition')
                 ->notNull();
            
            $table->integer('file_id')
                ->description('The import file')
                 ->notNull();
            
            $table->timestamps();
            
            $table->foreign('data_definition_id')
                ->references('data_definitions', 'id')
                ->onDelete('NO ACTION');
            
            $table->foreign('file_id')
                ->references('files', 'id')
                ->onDelete('NO ACTION');

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('data_definition_imports');
    }
}