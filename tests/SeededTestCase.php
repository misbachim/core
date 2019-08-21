<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;


/**
 * A test case class with seeder support.
 */
class SeededTestCase extends TestCase
{
    use Testable;

    // A list of read csvs.
    private static $csvs = [];
    // The active table name being used by prepareRecords().
    private $activeTableName;
    // The current statement object for processing records.
    private $stmt;
    // A list of prepared records.
    private $records = [];

    public static function setUpBeforeClass()
    {
        if (env('APP_ENV') !== 'testing') {
            throw new Exception('Hold up! You are not in a testing environment. Please replace your .env with .env.testing.');
        }
        // Migrate and seed database 'testing' before every test class run.
        Artisan::call('migrate', ['--database' => 'testing']);
        Artisan::call('db:seed', ['--class' => 'TestDatabaseSeeder', '--database' => 'testing']);
    }

    public static function tearDownAfterClass()
    {
        // Reset the migration after every test class run.
        Artisan::call('migrate:reset', ['--database' => 'testing']);
    }

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction(); // we will rollback the db changes after every test case

        // Transform all read csvs into records.
        foreach (self::$csvs as $tableName => $csv) {
            $this->records[$tableName] = (new Statement())->process($csv);
        }
    }

    public function tearDown()
    {
        DB::rollback(); // no db changes allowed!
    }

    /**
     * Read a csv for test or global seeder.
     */
    public static function read(string $tableName, $prefix = 'test')
    {
        $csvName = $prefix ? "$prefix-$tableName" : $tableName;

        // Read all matching file paths.
        $filePaths = glob(base_path()."/database/seeds/csvs/$csvName.csv");
        $origTableName = $tableName;
        foreach ($filePaths as $filePath) {
            // Get the table name if we don't know it initially.
            if ($origTableName === '*') {
                $fileName = basename($filePath, '.csv');
                $fileNameParts = explode('-', $fileName);

                if (count($fileNameParts) > 2) {
                    continue;
                } else {
                    $fileNameLastParts = array_slice($fileNameParts, 1);
                }
                $tableName = implode('', $fileNameLastParts);
                assert(count($tableName) > 0);
            }

            // Store the read csv.
            self::$csvs[$tableName] = Reader::createFromPath($filePath, 'r');
            self::$csvs[$tableName]->setHeaderOffset(0);
        }
    }

    /**
     * Retrieve a prepared set of records.
     */
    public function getRecords(string $tableName)
    {
        return $this->records[$tableName];
    }

    /**
     * Prepare records by chaining it with other methods, such as offset, limit, etc.
     */
    public function prepareRecords(string $tableName)
    {
        $this->activeTableName = $tableName;
        $this->stmt = new Statement();
        return $this;
    }

    /**
     * Start selecting rows from the given offset.
     */
    public function offset(int $n)
    {
        $this->stmt = $this->stmt->offset($n);
        return $this;
    }

    /**
     * Limit the number of selected rows.
     */
    public function limit(int $n)
    {
        $this->stmt = $this->stmt->limit($n);
        return $this;
    }

    /**
     * Order the rows by using a callback.
     */
    public function orderBy(callable $callable)
    {
        $this->stmt = $this->stmt->orderBy($callable);
        return $this;
    }

    /**
     * Filter selected rows by using a callback.
     */
    public function where(callable $callable)
    {
        $this->stmt = $this->stmt->where($callable);
        return $this;
    }

    /**
     * Finish preparing and process the csv into a set of records.
     */
    public function ok()
    {
        $tableName = $this->activeTableName;

        $this->records[$tableName] = $this->stmt->process(self::$csvs[$tableName]);
        $this->activeTableName = null;

        return $this->records[$tableName];
    }
}
