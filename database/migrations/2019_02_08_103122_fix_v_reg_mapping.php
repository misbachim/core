<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixVRegMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW v_reg_mapping');
        DB::statement("
                        CREATE VIEW v_reg_mapping AS
                        SELECT DISTINCT co.name AS country_name,
                        co.code AS country_code,
                        co.id AS country_id,
                        pr.name AS province_name,
                        pr.code AS province_code,
                        pr.id AS province_id,
                        ci.name AS city_name,
                        ci.code AS city_code,
                        ci.id AS city_id
                    FROM countries co
                        LEFT JOIN provinces pr 
                            ON pr.country_code::text = co.code::text
                            AND pr.tenant_id = co.tenant_id
                            and pr.company_id = co.company_id
                        LEFT JOIN cities ci 
                            ON ci.province_code::text = pr.code::text
                            and ci.tenant_id = pr.tenant_id 
                            and ci.company_id = pr.company_id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW v_reg_mapping');
        DB::statement("
                        CREATE VIEW v_reg_mapping AS
                        SELECT DISTINCT co.name AS country_name,
                            co.code AS country_code,
                            co.id AS country_id,
                            pr.name AS province_name,
                            pr.code AS province_code,
                            pr.id AS province_id,
                            ci.name AS city_name,
                            ci.code AS city_code,
                            ci.id AS city_id
                        FROM countries co
                            LEFT JOIN provinces pr ON pr.country_code = co.code
                            AND pr.tenant_id = co.tenant_id
                            AND pr.company_id = co.company_id
                            LEFT JOIN cities ci ON ci.province_code = pr.code
                            AND ci.tenant_id = pr.tenant_id
                            AND ci.company_id = pr.company_id;");
    }
}
