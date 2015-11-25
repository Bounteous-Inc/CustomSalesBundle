<?php

namespace DemacMedia\Bundle\CustomSalesBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateRegularEntityMigration implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('demac_wufoo_credentials');

        $table->addColumn('id',          'integer',  ['autoincrement' => true]);
        $table->addColumn('api_key',     'string',   ['length' => 64]);
        $table->addColumn('api_user',    'string',   ['length' => 64]);
        $table->addColumn('form_hash',   'string',   ['length' => 64]);
        $table->addColumn('form_name',   'string',   ['length' => 64]);
        $table->addColumn('form_label',  'string',   ['length' => 64]);
        $table->addColumn('domain_name', 'string',   ['length' => 128]);
        $table->addColumn('active',      'boolean');
        $table->addColumn('created_at',     'datetime', ['notnull' => false]);
        $table->addColumn('updated_at',     'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id',   'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
    }
}
