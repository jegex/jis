<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'];
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $modelMorphKey = $columnNames['model_morph_key'];

        $permTable = $tableNames['model_has_permissions'];
        $rolesTable = $tableNames['model_has_roles'];
        $driver = DB::getDriverName();

        // Fix model_has_permissions
        Schema::table($permTable, function (Blueprint $table) use ($permTable, $teamForeignKey, $pivotPermission, $modelMorphKey, $driver) {
            if ($driver === 'mysql') {
                $foreignKeys = DB::select('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$permTable]);
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
            } elseif ($driver === 'sqlite') {
                // SQLite: recreate table without FKs
                $this->recreateSqliteTable($permTable, $teamForeignKey, $pivotPermission, $modelMorphKey);

                return;
            }

            $primaryKeys = $this->getPrimaryKeys($permTable, $driver);
            if (! empty($primaryKeys)) {
                $table->dropPrimary('PRIMARY');
            }

            if (! Schema::hasColumn($permTable, $teamForeignKey)) {
                $table->unsignedBigInteger($teamForeignKey)->nullable();
            }

            $table->primary(
                [$pivotPermission, $modelMorphKey, 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );

            $table->foreign($pivotPermission)
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->unique(
                [$teamForeignKey, $pivotPermission, $modelMorphKey, 'model_type'],
                'model_has_permissions_team_unique'
            );
        });

        // Fix model_has_roles
        Schema::table($rolesTable, function (Blueprint $table) use ($rolesTable, $teamForeignKey, $pivotRole, $modelMorphKey, $driver) {
            if ($driver === 'mysql') {
                $foreignKeys = DB::select('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$rolesTable]);
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
            } elseif ($driver === 'sqlite') {
                $this->recreateSqliteTable($rolesTable, $teamForeignKey, $pivotRole, $modelMorphKey);

                return;
            }

            $primaryKeys = $this->getPrimaryKeys($rolesTable, $driver);
            if (! empty($primaryKeys)) {
                $table->dropPrimary('PRIMARY');
            }

            if (! Schema::hasColumn($rolesTable, $teamForeignKey)) {
                $table->unsignedBigInteger($teamForeignKey)->nullable();
            }

            $table->primary(
                [$pivotRole, $modelMorphKey, 'model_type'],
                'model_has_roles_role_model_type_primary'
            );

            $table->foreign($pivotRole)
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->unique(
                [$teamForeignKey, $pivotRole, $modelMorphKey, 'model_type'],
                'model_has_roles_team_unique'
            );
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'];
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $modelMorphKey = $columnNames['model_morph_key'];

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey, $pivotPermission, $modelMorphKey) {
            $table->dropUnique('model_has_permissions_team_unique');
            $table->dropForeign('model_has_permissions_permission_id_foreign');
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropColumn($teamForeignKey);

            $table->primary(
                [$pivotPermission, $modelMorphKey, 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );

            $table->foreign($pivotPermission)
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey, $pivotRole, $modelMorphKey) {
            $table->dropUnique('model_has_roles_team_unique');
            $table->dropForeign('model_has_roles_role_id_foreign');
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropColumn($teamForeignKey);

            $table->primary(
                [$pivotRole, $modelMorphKey, 'model_type'],
                'model_has_roles_role_model_type_primary'
            );

            $table->foreign($pivotRole)
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    private function getPrimaryKeys(string $table, string $driver): array
    {
        if ($driver === 'mysql') {
            return DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = ? AND INDEX_NAME = 'PRIMARY'", [$table]);
        }

        return DB::select("PRAGMA table_info($table)");
    }

    private function recreateSqliteTable(string $table, string $teamForeignKey, string $pivotKey, string $modelMorphKey): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $hasTeam = in_array($teamForeignKey, $columns);

        if ($hasTeam) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ADD COLUMN {$teamForeignKey} INTEGER");
    }
};
