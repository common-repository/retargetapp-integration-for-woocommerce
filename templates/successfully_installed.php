<div class="card-body wizard-body">
    <div class="wizard-body__wrapper">
        <h1 class="wizard-body__title">
            Set up your account
        </h1>
        <p class="wizard-body__description">
            You have successfully installed Adwisely plugin. Yay <span role="img" aria-label="star">⭐️</span><br />
            Now, let’s set up your account. Click “Continue” <br />
            and choose a Facebook Page you want to use in your ads.
        </p>
        <div class="wizard-body__description">
            <div class="wizard-body__privacy">
                <label for="privacy_policy" class="wizard-body__checkbox-label">
                <div class="custom-checkbox-container">
                    <input class="wizard-body__checkbox" type="checkbox" name="privacyPolicy" id="privacy_policy"/>
                    <span class="checkmark"></span>
                </div>
                <div class="wizard-body__link-text">
                    I’ve read Adwisely
                    <a class="link" target="_blank" href="http://help.adwisely.com/learn-about-pricing-privacy-policy-and-terms/privacy-policy">
                        Privacy Policy
                    </a>
                    and allow Adwisely to process my personal and business
                    data.
                </div>
                </label>
            </div>
        </div>
        <button id="ra_connect" class="btn" disabled>Continue</button>
    </div>
</div>
<script>
    const btn = document.querySelector('#ra_connect');
    const checkbox = document.querySelector('#privacy_policy');

    btn.addEventListener('click', function () {
        btn.classList.add('btn_loading');
        btn.disabled=true;
        checkbox.disabled=true;
    });

    checkbox.addEventListener('change', function () {
        checkbox.checked ? btn.disabled=false : btn.disabled=true;
    })
</script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#ra_connect').click(function (e) {
            e.preventDefault();

            var data = {
                'action': 'ra_connect',
            };
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.login_url !== undefined){
                        console.log('connection succeed');
                        window.location.replace(response.login_url);
                    } else {
                        console.log('connection failed');

                        btn.classList.remove('btn_loading');
                        btn.disabled=false;
                        checkbox.disabled=false;
                    }
                }
            });
        });
    });
</script>
