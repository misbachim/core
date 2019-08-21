<?php
namespace App\Business\Dao\UM;

use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;

/**
 * Notification Id's dao
 * @package App\Business\Dao
 */
class NotificationIdsDao
{
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->connection = 'um';
        $this->requester = $requester;
    }

    public function getAll(int $tenantId)
    {
        return DB::connection($this->connection)
            ->table('notification_ids')->select('user_id', 'notif_id')
            ->join('users', 'users.id', '=', 'notification_ids.user_id')
            ->where([
                ['tenant_id', $tenantId]
            ])->get()->toArray();
    }

    public function getNotifByPersonId(int $personId)
    {
        return DB::connection($this->connection)
            ->table('notification_ids')->select('user_id', 'notif_id')
            ->join('users', 'users.id', '=', 'notification_ids.user_id')
            ->where([
                ['person_id', $personId]
            ])->get()->toArray();
    }

}

?>
