<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class pchat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('priv_model');
        $this->load->model('pchat_model');
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('pagination', '../controllers/user'));
    }

    public function index() {
        $this->data['pchat_id'] = $this->session->userdata('pchat_id');
        $this->data['pchat_tbl'] = $this->session->userdata('pchat_tbl');
        if ($this->session->userdata('logged_in') != 1) {
            redirect('../user/jump_in', refresh);
        }

        $this->data['user_id'] = $this->session->userdata('user_id');
        $this->data['title'] = 'Hoppr Private Chat';

        $this->session->set_userdata('last_msg_id_' . $this->data['pchat_id'], 0);

        $this->load->view('templates/header', $this->data);
        $this->load->view('privatechat', $this->data);
        $this->load->view('templates/footer');
    }

    public function ajax_add_pchat_message() {
        $pchat_tbl = $this->input->post('pchat_tbl');
        $pchat_id = $this->input->post('pchat_id');
        $user_id = $this->input->post('user_id');
        $message = $this->input->post('message', TRUE);
        $this->pchat_model->insert_message($pchat_id, $user_id, $message, $pchat_tbl);

        echo $this->_get_pchat_messages($pchat_id, $pchat_tbl);
    }

    public function ajax_get_pchat_messages() {
        $pchat_id = $this->input->post('pchat_id');
        $pchat_tbl = $this->input->post('pchat_tbl');
        echo $this->_get_pchat_messages($pchat_id, $pchat_tbl);
    }

    public function _get_pchat_messages($pchat_id, $pchat_tbl) {
        $last_msg_id = (int) $this->session->userdata('last_msg_id_' . $pchat_id);

        $msg_data = $this->pchat_model->get_messages($pchat_id, $pchat_tbl, $last_msg_id);

        if ($msg_data->num_rows() > 0) {
            $last_msg_id = $msg_data->row($msg_data->num_rows() - 1)->cont_id;
            $this->session->set_userdata('last_msg_id_' . $pchat_id, $last_msg_id);

            $msg_html = "<ul>";

            foreach ($msg_data->result() as $pcmsg) {

                $time = $pcmsg->time;

                $li_class = ($this->session->userdata('user_id') == $pcmsg->user_id) ? 'class="triangle-isosceles left by_current_user"' : 'class="triangle-isosceles right other_user"';
                $msg_html .= '<li ' . $li_class . '>' . "<span class='msg_header'>" . $pcmsg->user_nickname . ' on ' . $time . "</span><p class='msg_content'>" . $pcmsg->message . "</p></li>";
            }

            $msg_html .= "</ul>";

            $result = array('status' => 'ok', 'content' => $msg_html);
            return json_encode($result);
            exit();
        } else {
            $result = array('status' => 'ok', 'content' => '');
            return json_encode($result);
            exit();
        }
    }

    public function create_pchat_tbl() {
        $user_id = $this->session->userdata('user_id');
        $mem_id = $this->session->userdata('mem_id');

        if ($user_id === $mem_id) {
            $this->pchat_list();
        } else {
            $res = $this->pchat_model->check_pchat($user_id, $mem_id);
            if ($res != FALSE) {
            $pchat_tbl = 'pc' . $res['pchat_code'];
            $pchat_id = $res['pchat_id'];
            $this->session->set_userdata('pchat_tbl', $pchat_tbl);
            $this->session->set_userdata('pchat_id', $pchat_id);
              $this->pc_enter_existing();
            } elseif ($res == FALSE) {
                $pad_str = md5(time());
                $pchat_tbl = substr($pad_str, 0, 8);
                $this->create_tbl_list();
                $this->pchat_model->add_to_tbl_list($pchat_tbl, $user_id, $mem_id);
                $this->pchat_model->create_table($pchat_tbl);
                redirect('../pchat/index', 'refresh');
            }
        }
    }

    public function create_tbl_list() {
        $this->pchat_model->create_tbl_list();
    }

    public function pchat_list() {
        if ($this->session->userdata('logged_in') != 1) {
            redirect('../user/jump_in', refresh);
        }
        $this->user_model->check_profile_status();
        $this->priv_model->checkroles();
        $nickname = $this->session->userdata('user_nickname');
        if ($this->session->userdata('logged_in') != "TRUE") {
            $this->need_login();
        } elseif ($this->session->userdata('verified') != "0") {
            $this->need_verify();
        } elseif ($this->session->userdata('state') == '1') {
            $this->myprofile();
        } elseif (($this->session->userdata('user_status') == "1" ) && (!file_exists("./hoppr/user/$nickname/video/myvideo.mp4"))) {
            $this->user->need_video();
        } elseif (($this->session->userdata('user_status') == '2') && (!file_exists("./hoppr/user/$nickname/video/myvideo.mp4"))) {
            $this->user->need_video();
        } elseif (($this->session->userdata('user_status') == '2') && (file_exists("./hoppr/user/$nickname/video/myvideo.mp4"))) {
            $data['title'] = 'Private Chat List';
            $this->load->view('templates/header', $data);
            $this->load->view('pchat_list');
            $this->load->view('templates/footer');
        }
    }

    public function pc_enter() {
        if ($this->session->userdata('logged_in') != 1) {
            redirect('../user/jump_in', 'refresh');
        }
        $values = explode("|", $_POST["mem_data"]);
        $pchat_id = $values[0];
        $pchat_tbl = $values[1];
        $mem_nickname = $values[2];

        $this->session->set_userdata('pchat_tbl', $pchat_tbl);
        $this->session->set_userdata('pchat_id', $pchat_id);
        $this->session->set_userdata('mem_nickname', $mem_nickname);
        redirect('../pchat/index', 'refresh');
    }

    public function pc_enter_existing($data=array() ) {
        if ($this->session->userdata('logged_in') != 1) {
            redirect('../user/jump_in', 'refresh');
        } elseif ($this->session->userdata('user_status') >= '2') {
            $pchat_id = $this->session->userdata('pchat_id');
            $mem_nickname = $this->session->userdata('mem_nickname');

            $this->session->set_userdata('pchat_id', $pchat_id);
            $this->session->set_userdata('mem_nickname', $mem_nickname);            
redirect('../pchat/index', 'refresh');
        } else {
            redirect('../user/need_verify', 'refresh');
        }
    }

}

?>

