<?php

namespace LucasRuroken\LaraMigrationsGenerator;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Migrate {
    
    /**
     * @var array
     */
    private static $ignore = array('migrations');

    /**
     * @var string
     */
    private static $database = "";

    /**
     * @var bool
     */
    private static $migrations = false;

    /**
     * @var array
     */
    private static $schema = array();

    /**
     * @var array
     */
    private static $selects = array('column_name as Field', 'column_type as Type', 'is_nullable as Null', 'column_key as Key', 'column_default as Default', 'extra as Extra', 'data_type as Data_Type');

    /**
     * @var $instance
     */
    private static $instance;

    /**
     * @var string
     */
    private static $up = "";

    /**
     * @var string
     */
    private static $down = "";

    /**
     * @return mixed
     */
    private static function getTables() {

        return DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema="' . self::$database . '"');
    }

    /**
     * @param $table
     * @return mixed
     */
    private static function getTableDescribes($table) {

        return DB::table('information_schema.columns')
            ->where('table_schema', '=', self::$database)
            ->where('table_name', '=', $table)
            ->get(self::$selects);
    }

    /**
     * @return mixed
     */
    private static function getForeignTables() {

        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', '=', self::$database)
            ->where('REFERENCED_TABLE_SCHEMA', '=', self::$database)
            ->select('TABLE_NAME')->distinct()
            ->get();
    }

    /**
     * @param $table
     * @return mixed
     */
    private static function getForeigns($table) {

        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', '=', self::$database)
            ->where('REFERENCED_TABLE_SCHEMA', '=', self::$database)
            ->where('TABLE_NAME', '=', $table)
            ->select('COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME')
            ->get();
    }

    /**
     * @return string
     */
    private static function compileSchema() {

        $upSchema = "";
        $downSchema = "";
        $newSchema = "";

        foreach (self::$schema as $name => $values) {

            if (in_array($name, self::$ignore)) {
                continue;
            }

            $upSchema .= "
 
            {$values['up']}";

            $downSchema .= "
            {$values['down']}";
        }

        $schema = "<?php
 
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
 
        class Create" . str_replace('_', '', Str::title(self::$database)) . "Database extends Migration {
         
            public function up() {
                " . $upSchema . "
                " . self::$up . "
            }
            
            public function down() {
                " . $downSchema . "
                " . self::$down . "
            }
        }";

        return $schema;
    }

    /**
     * @param $up
     * @return mixed
     */
    public function up($up) {

        self::$up = $up;
        return self::$instance;
    }

    /**
     * @param $down
     * @return mixed
     */
    public function down($down) {

        self::$down = $down;
        return self::$instance;
    }

    /**
     * @param $tables
     * @return mixed
     */
    public function ignore($tables) {

        self::$ignore = array_merge($tables, self::$ignore);
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function migrations() {

        self::$migrations = true;
        return self::$instance;
    }

    /**
     *
     */
    public function write() {

        $schema = self::compileSchema();
        $filename = date('Y_m_d_His') . "_create_" . self::$database . "_database";

        file_put_contents(database_path("migrations/{$filename}.php"), $schema);

        /**
         * Get the last batch
         */
        $migration = DB::table('migrations')->orderBy('batch', 'DESC')->first();
        $setBatch = $migration->batch + 1;
        DB::insert('insert into migrations (migration, batch) values (?, ?)', [$filename, $setBatch]);
    }

    /**
     * @return string
     */
    public function get() {

        return self::compileSchema();
    }

    /**
     * @param $database
     * @return SqlMigrations
     */
    public function convert($database) {

        self::$instance = new self();
        self::$database = $database;

        $table_headers = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
        $tables = self::getTables();

        foreach ($tables as $key => $value) {

            if (in_array($value->table_name, self::$ignore)) {
                continue;
            }

            $down = "Schema::drop('{$value->table_name}');";
            $up = "Schema::create('{$value->table_name}', function(Blueprint $" . "table) {\n";

            $tableDescribes = self::getTableDescribes($value->table_name);

            foreach ($tableDescribes as $values) {

                $method = "";
                $para = strpos($values->Type, '(');
                $type = $para > -1 ? substr($values->Type, 0, $para) : $values->Type;
                $numbers = "";
                $nullable = $values->Null == "NO" ? "" : "->nullable()";
                $default = empty($values->Default) ? "" : "->default(\"{$values->Default}\")";
                $unsigned = strpos($values->Type, "unsigned") === false ? '' : '->unsigned()';
                $unique = $values->Key == 'UNI' ? "->unique()" : "";

                switch ($type) {
                    case 'int' :
                        $method = 'unsignedInteger';
                        break;
                    case 'char' :
                    case 'varchar' :
                        $para = strpos($values->Type, '(');
                        $numbers = ", " . substr($values->Type, $para + 1, -1);
                        $method = 'string';
                        break;
                    case 'float' :
                        $method = 'float';
                        break;
                    case 'decimal' :
                        $para = strpos($values->Type, '(');
                        $numbers = ", " . substr($values->Type, $para + 1, -1);
                        $method = 'decimal';
                        break;
                    case 'tinyint' :
                        $method = 'boolean';
                        break;
                    case 'date':
                        $method = 'date';
                        break;
                    case 'timestamp' :
                        $method = 'timestamp';
                        break;
                    case 'datetime' :
                        $method = 'dateTime';
                        break;
                    case 'mediumtext' :
                        $method = 'mediumText';
                        break;
                    case 'text' :
                        $method = 'text';
                        break;
                    case 'double' :
                        $method = 'double';
                        break;
                }

                if ($values->Key == 'PRI') {

                    $method = 'increments';
                }

                $up .= " $" . "table->{$method}('{$values->Field}'{$numbers}){$nullable}{$default}{$unsigned}{$unique};\n";
            }

            $up .= " });\n\n";
            self::$schema[$value->table_name] = array(
                'up' => $up,
                'down' => $down
            );
        }

        $tableForeigns = self::getForeignTables();

        if (sizeof($tableForeigns) !== 0) {

            foreach ($tableForeigns as $key => $value) {

                $up = "Schema::table('{$value->TABLE_NAME}', function($" . "table) {\n";
                $foreign = self::getForeigns($value->TABLE_NAME);

                foreach ($foreign as $k => $v) {

                    $up .= " $" . "table->foreign('{$v->COLUMN_NAME}')->references('{$v->REFERENCED_COLUMN_NAME}')->on('{$v->REFERENCED_TABLE_NAME}');\n";
                }

                $up .= " });\n\n";

                self::$schema[$value->TABLE_NAME . '_foreign'] = array(
                    'up' => $up,
                    'down' => $down
                );
            }
        }
        return self::$instance;
    }
}