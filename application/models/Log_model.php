<?php

class Log_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'log';
    const TYPE_ADD_MONEY = 1;
    const TYPE_BUY_PACK = 2;

    /** @var int */
    protected $user_id;
    /** @var int */
    protected $type;
    /** @var string|stdClass */
    protected $content;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    /**
     * @return int
     */
    public function get_type(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function set_type(int $type)
    {
        $this->type = $type;
        return $this->save('type', $type);
    }

    /**
     * @return stdClass
     */
    public function get_content()
    {
        if (is_string($this->content)) {
            $this->content = json_decode($this->content);
        }
        return $this->content;
    }

    /**
     * @param string $content
     * @return bool
     */
    public function set_content(string $content): bool
    {
        $this->content = $content;
        return $this->save('message', json_encode($content));
    }

    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     * @return bool
     */
    public function set_time_created(string $time_created): bool
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     * @return bool
     */
    public function set_time_updated(string $time_updated): bool
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    /**
     * @param User_model $user
     * @param float $sum
     * @throws Exception
     */
    public static function create_money_log(User_model $user, float $sum)
    {
        static::create([
            'user_id' => $user->get_id(),
            'type' => static::TYPE_ADD_MONEY,
            'content' => json_encode([
                'sum' => $sum,
                'wallet_balance' => $user->get_wallet_balance(),
            ]),
        ]);
    }

    public static function create_boosterpack_log(User_model $user, Boosterpack_model $boosterpack, int $likes)
    {
        static::create([
            'user_id' => $user->get_id(),
            'type' => static::TYPE_BUY_PACK,
            'content' => json_encode([
                'price' => $boosterpack->get_price(),
                'wallet_balance' => $user->get_wallet_balance(),
                'likes' => $likes,
                'likes_balance' => $user->get_likes_balance(),
            ]),
        ]);
    }

    /**
     * @param static[] $data
     * @param string $preparation
     * @return stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation) {
            case 'balances_log':
                return self::_preparation_balances_log($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param static[] $data
     * @return stdClass[]
     * @throws Exception
     */
    private static function _preparation_balances_log($data)
    {
        $ret = [];
        foreach ($data as $d) {
            $o = new stdClass();
            $o->type = $d->get_type();
            /** @see Log_model::create_money_log() */
            /** @see Log_model::create_boosterpack_log() */
            $o->content = $d->get_content();
            $o->time_created = $d->get_time_created();
            $ret[] = $o;
        }
        return $ret;
    }
}