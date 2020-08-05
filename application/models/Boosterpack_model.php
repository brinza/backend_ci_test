<?php

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Boosterpack_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'boosterpack';


    /** @var float Цена бустерпака */
    protected $price;
    /** @var float Банк, который наполняется  */
    protected $bank;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    /**
     * @return float
     */
    public function get_price(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return bool
     */
    public function set_price(float $price)
    {
        $this->price = $price;
        return $this->save('price', $price);
    }

    /**
     * @return float
     */
    public function get_bank(): float
    {
        return $this->bank;
    }

    /**
     * @param float $bank
     *
     * @return bool
     */
    public function set_bank(float $bank)
    {
        $this->bank = $bank;
        return $this->save('bank', $bank);
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
     *
     * @return bool
     */
    public function set_time_created(string $time_created)
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
     *
     * @return bool
     */
    public function set_time_updated(string $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    function __construct($id = NULL)
    {
        parent::__construct();

        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public static function create(array $data)
    {
        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_ci()->s->get_insert_id());
    }

    public function delete()
    {
        $this->is_loaded(TRUE);
        App::get_ci()->s->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return (App::get_ci()->s->get_affected_rows() > 0);
    }

    protected function get_likes()
    {
        $max = intval( $this->get_bank() + $this->get_price());
        $amount = rand(1, $max);
        $new_bank = $this->get_bank() + $this->get_price() - $amount;
        $this->set_bank($new_bank);
        return $amount;
    }

    public function buy(User_model $user)
    {
        $wallet_balance = $user->get_wallet_balance() - $this->get_price();
        if ($wallet_balance < 0) {
            throw new UserException('Not enough money');
        }
        $amount = $this->get_likes();
        $likes_balance = $user->get_likes_balance() + $amount;
        $total_withdrawn = $user->get_wallet_total_withdrawn() + $this->get_price();

        App::get_ci()->s->start_trans();

        $user->set_wallet_balance($wallet_balance);
        $user->set_likes_balance($likes_balance);
        $user->set_wallet_total_withdrawn($total_withdrawn);
        Log_model::create_boosterpack_log($user, $this, $amount);

        App::get_ci()->s->commit();

        return $amount;
    }
}
