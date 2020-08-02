<?php
class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

    public static function login($params)
    {
        if (empty($params['personaname']) || empty($params['password'])) {
            throw new UserException(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }
        if (User_model::is_logged()) {
            throw new UserException('Already logged');
        }
        $user = User_model::getOneByOrExcept($params, 'Wrong credentials');
        static::start_session($user->get_id());
        return $user;
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new CriticalException('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }


}
