<?php

class pchat_model extends CI_Model {

    function __construct() {
        /* Call the Model constructor */
        parent::__construct();
        date_default_timezone_set('UTC');
    }

    function get_messages($pchat_id, $pchat_tbl, $last_msg_id = 0) {
        $query_str = "SELECT pt.cont_id, "
                . "pt.user_id, pt.message, "
                . "from_unixtime(pt.time, '%D %M %y %h:%i:%s') AS time, "
                . "us.user_nickname "
                . "FROM $pchat_tbl pt "
                . "JOIN users us ON us.user_id = pt.user_id "
                . "WHERE pt.pchat_id = ? "
                . "AND pt.cont_id > ? "
                . "ORDER BY pt.cont_id ASC";
        $result = $this->db->query($query_str, array($pchat_id, $last_msg_id));
        return $result;
    }

    function insert_message($pchat_id, $user_id, $message, $pchat_tbl) {
        $this->db->query("UPDATE $pchat_tbl SET status = '0' ");
        $this->pchat_id = $pchat_id;
        $this->user_id = $user_id;
        $this->message = $message;
        $this->time = time();
        $this->status = 3;
        $this->db->insert($pchat_tbl, $this);
    }

    function create_tbl_list() {
        $this->load->dbforge();
        $field = array(
            'pchat_id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user1' => array(
                'type' => 'INT'
            ),
            'user2' => array(
                'type' => 'INT'
            ),
            'pchat_code' => array(
                'type' => 'VARCHAR',
                'constraint' => 15,
            ),
            'time' => array(
                'type' => 'INT'
            ),
            'status' => array(
                'type' => 'INT'
            )
        );
        $this->dbforge->add_field($field);
        $this->dbforge->add_key('pchat_id', TRUE);
        $this->dbforge->create_table('pc_tbl_list', TRUE);
    }

    function add_to_tbl_list($pchat_tbl, $user_id, $mem_id) {
        $this->time = time();
        $data = array(
            'user1' => $user_id,
            'user2' => $mem_id,
            'pchat_code' => $pchat_tbl,
            'time' => $this->time,
            'status' => 0
        );
        $this->db->insert('pc_tbl_list', $data);
        $q = $this->db->query("SELECT pchat_id FROM pc_tbl_list WHERE pchat_code = '$pchat_tbl' ");
        foreach ($q->result() as $r) {
            $pchat_id = array('pchat_id' => $r->pchat_id);
            $this->session->set_userdata($pchat_id);
        }
    }

    function create_table($pchat_tbl) {
        $this->load->dbforge();
        $fields = array(
            'cont_id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'pchat_id' => array(
                'type' => 'INT'
            ),
            'user_id' => array(
                'type' => 'INT'
            ),
            'message' => array(
                'type' => 'TEXT'
            ),
            'time' => array(
                'type' => 'INT'
            ),
            'status' => array(
                'type' => 'INT'
            )
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('cont_id', TRUE);
        $this->dbforge->create_table("pc" . $pchat_tbl, TRUE);
        $pchat_data = array('pchat_tbl' => 'pc' . $pchat_tbl);
        $this->session->set_userdata($pchat_data);
    }

    public function check_pchat($user_id, $mem_id) {
        $q = $this->db->query("SELECT * FROM pc_tbl_list WHERE ((user1 = '$user_id' AND user2 = '$mem_id') OR (user2 = '$user_id' AND user1 = '$mem_id')) ");
        if ($q->num_rows > 0) {
            foreach ($q->result() as $r) {
            $pchat_data = array(
'pchat_id' => $r->pchat_id,
'pchat_code' => $r->pchat_code);           
            }
            return $pchat_data;
}
    }

    public function pcnew() {
        $user_id = $this->session->userdata('user_id');
        $q = $this->db->query("SELECT pchat_code, pchat_id FROM pc_tbl_list WHERE (user2 = '$user_id' OR user1 = '$user_id') AND status >= '2'");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $r) {
                $pchat_code = $r->pchat_code;
                $pchat_id = $r->pchat_id;
                $pchat_tbl = 'pc' . $pchat_code;
                $user_id = $this->session->userdata('user_id');
                $q = $this->db->query("SELECT * from $pchat_tbl WHERE user_id  != '$user_id' AND pchat_id = '$pchat_id' AND status >= '2' ");
                $pcnew = $q->num_rows();
                return $pcnew;
           } } else { return FALSE;}
        
    }

}

