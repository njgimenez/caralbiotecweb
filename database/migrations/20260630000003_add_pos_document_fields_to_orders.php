<?php

use Phinx\Migration\AbstractMigration;

class AddPosDocumentFieldsToOrders extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('orders');
        $table
            ->addColumn('document_type', 'string', ['limit' => 20, 'null' => true, 'after' => 'currency'])
            ->addColumn('document_number', 'string', ['limit' => 20, 'null' => true, 'after' => 'document_type'])
            ->addColumn('document_name', 'string', ['limit' => 180, 'null' => true, 'after' => 'document_number'])
            ->update();
    }
}
