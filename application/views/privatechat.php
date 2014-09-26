<script>
    var pchat_id = "<?php echo $pchat_id; ?>";
    var user_id = "<?php echo $user_id; ?>";
    var pchat_tbl = "<?php echo $this->session->userdata('pchat_tbl'); ?>";


    $(document).ready(function() {


        setInterval(function() {
            get_chat_messages();
        }, 15000);

        $("input#pchat_message").keypress(function(e) {
            if (e.which == 13) {
                $("a#submit_message").click();
                return false;
            }
        });


        $("a#submit_message").click(function() {

            var message = $("input#pchat_message").val();
            if (message == "") {
                return false;
            }
            $.post(base_url + "pchat/ajax_add_pchat_message", {message: message, pchat_id: pchat_id, user_id: user_id, pchat_tbl: pchat_tbl}, function(data) {
                if (data.status == 'ok')
                {
                    var curcont = $("div#pc_viewport").html();
                    $("#pc_viewport").html(curcont + data.content);
                    $('#pc_viewport').animate({scrollTop: $('#pc_viewport').get(0).scrollHeight}, 3000);
                }

            }, "json");

            $("input#pchat_message").val("");

            return false;

        });

        function get_chat_messages()
        {
            $.post(base_url + 'pchat/ajax_get_pchat_messages', {pchat_id: pchat_id, pchat_tbl: pchat_tbl}, function(data) {

                if (data.status == 'ok')
                {
                    var curcont = $("div#pc_viewport").html();
                    $("#pc_viewport").html(curcont + data.content);
                    $('#pc_viewport').animate({scrollTop: $('#pc_viewport').get(0).scrollHeight}, 3000);
                }
                else
                {
                    ///something wrong
                }
            }, "json");
        }
        get_chat_messages();
    });
</script>
<style>
    div#pc_viewport{
        height: 350px;
        border: 1px solid #777;
        overflow-y: auto;
        background-image: url(../assets/images/pcbg.png);
    }

    div#pc_viewport ul{
        list-style-type: none;
    }

    div#pc_viewport ul li.by_current_user{
        min-width: 65%;
        color: whitesmoke;
        text-shadow: 1px 1px black;
        float: left;
        border: 1px solid black;
        border-radius: 7px;
        margin-left: -30px;
        margin-top: 7px;
        background-color:lightgrey;
    }

    div#pc_viewport ul li.other_user{
        min-width: 65%;
        color: black;
        text-shadow: 1px 1px black;
        float:right;;
        background-color: lightgoldenrodyellow;
        border: 1px solid black;
        border-radius: 3px;
        margin-top: 7px;
    }


    span.msg_header{
        font-size: .7em;
        color: paleturquoise;
    }

    input#pchat_message {
        width: 98%
    }
</style>
<?php
if ($this->session->userdata('email') != ' ') {
    ?>
    <div id='plate'>
        <div id='innerplate' style='background-color: lightgrey;'>
            <div id='container' class='move' style="color: #00406c">
                <b><?php echo $this->session->userdata('user_nickname') . '</b> is chatting with <b><em style="color: green ; font-size: 1.2em;">' . $this->session->userdata('mem_nickname') ;?></b>
                <div id='pc_viewport'></div>
                <br />
                <div id='chat_input'>
                    <input id='pchat_message' name='pchat_message' type='text' value='' tabindex="1" /><br /><br />
                    <?php echo anchor('#', 'Post', array('title' => 'Send Chat Message', 'id' => 'submit_message', 'class' => 'actbut')); ?>
                    <div class='clearer'></div>

                </div>
            </div>
             <div id='ad_tower'><?php $this->view('ad2'); ?></div>
        <div id='ad_bottom'><?php $this->view('adbot_1'); ?></div>

        </div>
    </div>
    <?php
} else {
    redirect('../user/jumpin', refresh);
}
?>

