<div class="card-body wizard-body">
    <div class="wizard-body__wrapper">
        <h1 class="wizard-body__title">
            Set up your account
        </h1>
        <div class="notification__wrapper">
            <div class="notification__text"></div>
            <p class="notification__description">
                <?php 
                    $permalinks_page = admin_url() . 'options-permalink.php';
                    echo 'It seems like the permalinks are set to "Plain" status in your settings. Please, enable the permalinks <a class="wizard-body__link" href='. $permalinks_page .'>following this link</a> so that you can complete the setup for Adwisely.'
                ?>
            </p>
        </div>
        <button id="ra_connect" class="btn" disabled>Continue</button>
    </div>
</div>