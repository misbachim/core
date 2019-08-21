<?php

namespace App\Business\Dao\UM;


use App\Business\Model\Requester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Authorization dao
 * @package App\Business\Dao
 */
class MenuDataAccessDao
{
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->connection = 'um';
        $this->requester = $requester;
    }

    /**
     * Get menu data access by menuCode
     * @param int $tenantId
     * @return mixed
     */
    public function getMenuDataAccessByMenuCode(string $menuCode)
    {
        return DB::connection($this->connection)
            ->table('menu_data_access')
            ->select(
                'data_access_code as dataAccessCode'
            )
            ->where('menu_code', $menuCode)
            ->get();
    }
}
