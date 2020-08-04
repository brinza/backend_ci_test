<?php

class Comment_likes_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'comment_likes';

    /** @var int */
    protected $user_id;
    /** @var int */
    protected $comment_id;
    /** @var int */
    protected $amount;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // generated
    protected $comment;
    protected $user;

    function __construct($id = null)
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
    public function set_user_id(int $user_id): bool
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return int
     */
    public function get_comment_id(): int
    {
        return $this->comment_id;
    }

    /**
     * @param int $comment_id
     * @return bool
     */
    public function set_comment_id(int $comment_id): bool
    {
        $this->comment_id = $comment_id;
        return $this->save('comment_id', $comment_id);
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
    public function set_amount(int $amount): bool
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
    }

    /**
     * @return Comment_model
     * @throws Exception
     */
    public function get_comment(): Comment_model
    {
        $this->is_loaded(true);
        if (empty($this->comment)) {
            try {
                $this->comment = new Comment_model($this->get_comment_id());
            } catch (Exception $exception) {
                $this->comment = new Comment_model();
            }
        }
        return $this->comment;
    }

    /**
     * @return User_model
     * @throws Exception
     */
    public function get_user(): User_model
    {
        $this->is_loaded(true);
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

    public static function like(Comment_model $comment)
    {
        $user = User_model::get_user();
        $balance = $user->get_likes_balance() - 1;
        if ($balance < 0) {
            throw new UserException('No more likes');
        }

        App::get_ci()->s->start_trans();

        $user_like = Comment_likes_model::getOneBy([
            'user_id' => $user->get_id(),
            'comment_id' => $comment->get_id(),
        ], true);
        if (!$user_like) {
            Comment_likes_model::create([
                'user_id' => $user->get_id(),
                'comment_id' => $comment->get_id(),
                'amount' => 1,
            ]);
        } else {
            $user_like->set_amount($user_like->get_amount() + 1);
        }
        $user->set_likes_balance($balance);

        App::get_ci()->s->commit();
    }
}