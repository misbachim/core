<?php
namespace App\Business\Dao\UM;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Business\Model\Requester;

/**
 * Notification Id's dao
 * @package App\Business\Dao
 */
class NotificationMessagesDao
{
    private $requester;
    private $notificationMessageUsersDao;


    public function __construct(Requester $requester)
    {
        $this->connection = 'um';
        $this->requester = $requester;
        $this->notificationMessageUsersDao = new NotificationMessageUsersDao($requester);
    }


    public function save(string $notifCategoryCode, string $subject, string $message, array $notifIds, string $url)
    {
        $id = $this->newId();

        if (
        DB::connection($this->connection)
            ->table('notification_messages')->insert([
                'id' => $id,
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'notification_category_code' => $notifCategoryCode,
                'url' => $url,
                'subject' => $subject,
                'message' => $message,
                'created_at' => Carbon::now(),
                'created_by' => $this->requester->getUserId()
            ])) {
            if ($this->notificationMessageUsersDao->saveMany($id, $notifIds)) {
                return $id;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private function newId()
    {
        $ENCRYPTION_METHOD = 'aes-256-ctr';
        $ids = array_column(DB::connection($this->connection)
            ->table('notification_messages')->select('id')->get()->toArray(), 'id');

        do {
            $key = hex2bin("566963746f7248756265727461");
            $ivlen = openssl_cipher_iv_length($ENCRYPTION_METHOD);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $temp = bin2hex(openssl_encrypt(count($ids), $ENCRYPTION_METHOD, $key, $options = 0, $iv));
        } while ($ids && (in_array($temp, $ids)));

        return $temp;
    }

}

?>
