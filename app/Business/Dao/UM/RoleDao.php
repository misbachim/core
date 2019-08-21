<?php

namespace App\Business\Dao\UM;

use App\Business\Model\Requester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Role related dao
 * @package App\Business\Dao
 */
class RoleDao
{
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->connection = 'um';
        $this->requester = $requester;
    }

    /**
     * Get user roles.
     * @param int $tenantId , tenant id
     * @param int $userId , user id to be looked up
     * @param string $flag , possible value : 'all', 'active'
     * @return
     */
    public function getByUser(int $tenantId, int $userId, string $flag = 'active')
    {
        $result =
            DB::connection($this->connection)
            ->table('roles')
            ->join('user_roles', function ($join) {
                $join
                    ->on('user_roles.tenant_id', '=', 'roles.tenant_id')
                    ->on('user_roles.role_id', '=', 'roles.id');
            })
            ->select(
                'roles.id'
            )
            ->where([
                ['user_roles.user_id', $userId],
                ['roles.tenant_id', $tenantId],
                ['roles.is_deleted', false]
            ]);

        if ($flag === 'active') {
            $result->where('user_roles.is_active', true);
        }

        return $result->get();
    }
}
