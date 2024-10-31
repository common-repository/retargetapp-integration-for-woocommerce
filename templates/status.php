<?php include(dirname(__FILE__) . '/head.php'); ?>
<?php include(dirname(__FILE__) . '/card-header.php'); ?>
<div class="card-body wizard-body">
    <div class="wizard-body__wrapper">
        <h1 class="wizard-body__title">
            You are all set!
        </h1>
        <p class="wizard-body__description">
            You have successfully installed Adwisely plugin. Yay <span role="img" aria-label="star">⭐️</span><br/>
            Now, click "Go to Adwisely" <br/>
            to manage your campaign and check the results
        </p>
        <a href="<?php echo $login_url ?>" target="_blank" class="btn">
            Go to Adwisely
        </a>
    </div>
</div>
<?php include(dirname(__FILE__) . '/card-footer.php'); ?>
