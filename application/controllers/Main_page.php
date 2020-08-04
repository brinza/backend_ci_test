<?php

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        App::get_ci()->load->model('User_model');
        App::get_ci()->load->model('Login_model');
        App::get_ci()->load->model('Post_model');

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();



        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id){ // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS

        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function comment()
    {
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = $this->input->input_stream('post_id');
        $message = $this->input->input_stream('message');

        if (empty($post_id) || empty($message)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        Comment_model::create([
            'user_id' => User_model::get_user()->get_id(),
            'assign_id' => $post_id,
            'text' => $message,
        ]);

        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function login()
    {
        $params = [
            'personaname' => $this->input->input_stream('login'),
            'password' => $this->input->input_stream('password'),
        ];
        try {
            $user = Login_model::login($params);
        } catch (UserException $e) {
            return $this->response_error($e->getMessage());
        } catch (Exception $e) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }
        return $this->response_success(['user' => $user->get_id()]);
    }


    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money(){
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }

    public function buy_boosterpack(){
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }


    public function like()
    {
        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $post_id = intval($this->input->input_stream('post_id'));
        if (!$post_id) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $e) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        try {
            Post_likes_model::like($post);
        } catch (UserException $e) {
            return $this->response_error($e->getMessage());
        } catch (Exception $e) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_INTERNAL_ERROR);
        }

        $likes = Post_likes_model::preparation($post->get_likes(), 'full_amount');
        return $this->response_success(['likes' => $likes]);
    }

}
