<script>
    function pchatnow(button) {
        var mem_id = $(button).val();
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', '../pchat/create_pchat_tbl');
        form.style.display = 'hidden';

        var hiddenField = document.createElement("input");
        hiddenField.setAttribute('name', 'mem_id');
        hiddenField.setAttribute('value', mem_id)
        form.appendChild(hiddenField);
        form.submit();
    }
</script>

// '$mem_id' from database query and set using $this->session->userdata('mem_id')

<button type='button' onclick='pchatnow(this)' id='mem_id' value='<?php echo $mem_id; ?>'><img src='IMAGE'  class='notif notbut'></button>
