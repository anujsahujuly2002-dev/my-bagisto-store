<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The namespace stored class names used before the vendor rename.
     */
    protected const LEGACY = 'Webkul\\';

    /**
     * The namespace stored class names use now.
     */
    protected const CURRENT = 'EcomForYou\\';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->rewrite(self::LEGACY, self::CURRENT);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rewrite(self::CURRENT, self::LEGACY);
    }

    /**
     * Rewrite every class name stored in a polymorphic `*_type` column from one namespace to the other.
     */
    protected function rewrite(string $from, string $to): void
    {
        foreach (Schema::getTables() as $table) {
            foreach (Schema::getColumns($table['name']) as $column) {
                if (! str_ends_with($column['name'], '_type')) {
                    continue;
                }

                $this->rewriteColumn($table['name'], $column['name'], $from, $to);
            }
        }
    }

    /**
     * Rewrite the class names of one column, matching the namespace prefix with the backslash escaped for `like`.
     */
    protected function rewriteColumn(string $table, string $column, string $from, string $to): void
    {
        $values = DB::table($table)
            ->whereLike($column, str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $from).'%', caseSensitive: true)
            ->distinct()
            ->pluck($column);

        foreach ($values as $value) {
            if (str_starts_with((string) $value, $from)) {
                DB::table($table)
                    ->where($column, $value)
                    ->update([$column => $to.substr($value, strlen($from))]);
            }
        }
    }
};
