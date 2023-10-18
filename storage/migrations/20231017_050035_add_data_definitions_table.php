<?php
declare(strict_types=1);

namespace Platine\Framework\Migration;

use Platine\Database\Schema\CreateTable;
use Platine\Framework\Migration\AbstractMigration;

class AddDataDefinitionsTable20231017050035 extends AbstractMigration
{

    public function up(): void
    {
      //Action when migrate up
        $this->create('data_definitions', function (CreateTable $table) {
            $table->integer('id')
                  ->autoincrement()
                 ->primary();
            
            $table->string('name')
                 ->description('The display name')
                 ->notNull();
            
            $table->string('description');
            
            $table->string('extractor')
                 ->description('The extractor to use')
                 ->notNull();
            
            $table->string('transformer')
                 ->description('The transformer to use');
            
            $table->string('loader')
                 ->description('The loader to use')
                 ->notNull();
            

            $table->enum('direction', ['I', 'O'])
                 ->description('I=IN (Import), O=Out (Export)')
                 ->defaultValue('I')
                 ->notNull();

            $table->string('field_separator')
                 ->description('Used for CSV, etc.');
            
            $table->string('text_delimiter')
                 ->description('Used for CSV, etc.');
            
            $table->string('escape_char')
                 ->description('Used for CSV, etc.');
            
            $table->timestamps();

            $table->engine('INNODB');
        });
    }

    public function down(): void
    {
      //Action when migrate down
        $this->drop('data_definitions');
    }
}