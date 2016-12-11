<?php
namespace Da\User\Model;

use Da\User\Query\SocialNetworkAccountQuery;
use Da\User\Traits\ContainerTrait;
use Da\User\Traits\ModuleTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * /**
 * @property integer $id          Id
 * @property integer $user_id     User id, null if account is not bind to user
 * @property string $provider     Name of service
 * @property string $client_id    Account id
 * @property string $data         Account properties returned by social network (json encoded)
 * @property string $decodedData  Json-decoded properties
 * @property string $code
 * @property string $email
 * @property string $username
 * @property integer $created_at
 *
 * @property User $user        User that this account is connected for.
 */
class SocialNetworkAccount extends ActiveRecord
{
    use ModuleTrait;
    use ContainerTrait;

    /**
     * @var array json decoded properties
     */
    protected $decodedData;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%social_account}}';
    }

    /**
     * @return bool Whether this social account is connected to user.
     */
    public function getIsConnected()
    {
        return $this->user_id != null;
    }

    /**
     * @return array json decoded properties
     */
    public function getDecodedData()
    {
        if ($this->data !== null && $this->decodedData === null) {
            $this->decodedData = json_decode($this->data);
        }

        return $this->decodedData;
    }

    /**
     * @return string the connection url
     */
    public function getConnectionUrl()
    {
        $code = Yii::$app->security->generateRandomString();
        $this->updateAttributes(['code' => md5($code)]);

        return Url::to(['/usr/registration/connect', 'code' => $code]);
    }

    /**
     * Connects account to a user
     *
     * @param User $user
     *
     * @return int
     */
    public function connect(User $user)
    {
        return $this->updateAttributes(
            [
                'username' => null,
                'email' => null,
                'code' => null,
                'user_id' => $user->id,
            ]
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->getClassMap()->get(User::class), ['id' => 'user_id']);
    }

    /**
     * @return SocialNetworkAccountQuery
     */
    public static function find()
    {
        return new SocialNetworkAccountQuery(static::class);
    }
}
