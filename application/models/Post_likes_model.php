<?php

class Post_likes_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'post_likes';

    /** @var int */
    protected $user_id;
    /** @var int */
    protected $post_id;
    /** @var int */
    protected $amount;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // generated
    protected $post;
    protected $user;

    function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function set_user_id(int $user_id) :bool
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return int
     */
    public function get_post_id(): int
    {
        return $this->post_id;
    }

    /**
     * @param int $post_id
     * @return bool
     */
    public function set_post_id(int $post_id) :bool
    {
        $this->post_id = $post_id;
        return $this->save('post_id', $post_id);
    }

    /**
     * @return int
     */
    public function get_amount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function set_amount(int $amount) :bool
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
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
     * @return bool
     */
    public function set_time_updated(string $time_updated) :bool
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    /**
     * @return Post_model
     * @throws Exception
     */
    public function get_post(): Post_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->post)) {
            try {
                $this->post = new Post_model($this->get_post_id());
            } catch (Exception $exception) {
                $this->post = new Post_model();
            }
        }
        return $this->post;
    }

    /**
     * @return User_model
     * @throws Exception
     */
    public function get_user(): User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user)) {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception) {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    /**
     * @param self|self[] $data
     * @param string $preparation
     * @return int
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation) {
            case 'full_amount':
                return self::_preparation_full_amount($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param self[] $likes
     * @return int
     */
    private static function _preparation_full_amount($likes)
    {
        $ret = 0;
        foreach ($likes as $like) {
            $ret += $like->get_amount();
        }
        return $ret;
    }

    public static function like(Post_model $post)
    {
        $user = User_model::get_user();
        $balance = $user->get_likes_balance() - 1;
        if ($balance < 0) {
            throw new UserException('No more likes');
        }

        App::get_ci()->s->start_trans();

        $user_like = Post_likes_model::getOneBy([
            'user_id' => $user->get_id(),
            'post_id' => $post->get_id(),
        ], true);
        if (!$user_like) {
            Post_likes_model::create([
                'user_id' => $user->get_id(),
                'post_id' => $post->get_id(),
                'amount' => 1,
            ]);
        } else {
            $user_like->set_amount($user_like->get_amount() + 1);
        }
        $user->set_likes_balance($balance);

        App::get_ci()->s->commit();
    }
}
