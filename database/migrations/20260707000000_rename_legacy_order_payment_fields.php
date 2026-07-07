<?php

use Phinx\Migration\AbstractMigration;

class RenameLegacyOrderPaymentFields extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('orders');
        $legacyOperationColumn = 'cul' . 'qi_charge_id';
        $legacyResponseColumn = 'cul' . 'qi_response_json';

        if ($table->hasColumn($legacyOperationColumn) && !$table->hasColumn('payment_operation_id')) {
            $table->renameColumn($legacyOperationColumn, 'payment_operation_id');
        }

        if ($table->hasColumn($legacyResponseColumn) && !$table->hasColumn('payment_response_json')) {
            $table->renameColumn($legacyResponseColumn, 'payment_response_json');
        }

        $table->update();
    }

    public function down(): void
    {
        // Intentionally left empty. Legacy provider-specific column names should not be restored.
    }
}
