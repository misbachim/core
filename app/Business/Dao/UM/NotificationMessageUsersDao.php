<?php
namespace App\Business\Dao\UM;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Business\Model\Requester;

/**
 * Notification Id's dao
 * @package App\Business\Dao
 */
class NotificationMessageUsersDao
{
    private $requester;


    public function __construct(Requester $requester)
    {
        $this->connection = 'um';
        $this->requester = $requester;
    }


    public function saveMany(string $notifMessageId, array $notifIds)
    {
        $objects = [];
        for ($n = 0; $n < count($notifIds); $n++) {
            array_push($objects, [
                'notification_message_id' => $notifMessageId,
                'user_id' => $notifIds[$n],
                'created_at' => Carbon::now(),
                'created_by' => $this->requester->getUserId()
            ]);
        }

        return
            DB::connection($this->connection)
                ->table('notification_message_users')->insert($objects);
    }

}

?>
