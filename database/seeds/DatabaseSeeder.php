<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('LovTypesTableSeeder');
        $this->call('SettingTypesTableSeeder');
        $this->call('SettingLovsTableSeeder');
        $this->call('MtDatabaseSeeder');
        $this->call('RatingScalesTableSeeder');
        $this->call('RatingScaleDetailsTableSeeder');
        $this->call('WidgetTypeTableSeeder');
    }
}
