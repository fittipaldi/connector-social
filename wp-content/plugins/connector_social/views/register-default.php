<style type="text/css">
    .input label {
        width: 120px;
        float: left;
        margin-top: 4px;
        font-size: 15px;
        color: #298CBA;
    }

    .file label {
        width: 225px;
        float: left;
        font-size: 14px;
        color: #298CBA;
    }
</style>
<div class="wrap">

    <h2>Form Register</h2>

    <form class="ac-custom ac-checkbox ac-checkmark" autocomplete="off" id="form-connect-social-register" action="<?php echo admin_url('admin-ajax.php'); ?>">

        <fb:login-button scope="public_profile,email,user_link,instagram_basic" onlogin="checkLoginState();">
        </fb:login-button>

        <div class="box-o-inpt">
            <input type="hidden" name="action" id="action" value="register_profile">
            <input type="hidden" name="id" id="id" value="0">
        </div>

        <div class="input">
            <label for="nome"><?php __('Name') ?></label>
            <input type="text" name="name" id="name"/>
        </div>
        <br clear="all"/>

        <div class="input">
            <label for="email"><?php __('E-mail') ?></label>
            <input type="text" name="email" id="email"/>
        </div>
        <br clear="all"/>

        <p class="submit">
            <span id="inputsubmit">
                <input id="save_slider" name="save_slider" type="submit" value="Salvar" class="button-primary"/>
            </span>
        </p>

        <div id="mensage"></div>
    </form>
</div>


<div id="fb-root"></div>
<script>
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v3.0&appId=448067748982743&autoLogAppEvents=1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function statusChangeCallback(response) {
        console.info(response);
    }

    function checkLoginState() {
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });
    }
</script>

<?php
/*
<div id="fb-root"></div>
<script>
    window.fbAsyncInit = function () {
        FB.init({
            appId: '448067748982743',
            cookie: true,
            xfbml: true,
            version: '3.0'
        });

        FB.AppEvents.logPageView();
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function statusChangeCallback(response) {
        console.info(response);
    }

    function checkLoginState() {
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });
    }

    /*
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });
        * /


</script>
*/ ?>

