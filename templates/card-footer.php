<div class="card-footer wizard-footer">
    <p
        class="text-warning text-center wizard-footer__p-text wizard-footer__p-text-first"
    >
        14-Day Free Trial
    </p>
    <div class="text-center wizard-footer__p-text">
        During the trial you will pay for ads directly to Facebook, however,
        Adwisely service will be free.
        <a
            href="https://help.adwisely.com/en/articles/900414-what-s-the-cost-of-retargetapp"
            class="link link-white"
            rel="noopener noreferrer"
            target="_blank"
        >See Pricing
        </a>
    </div>
</div>
</div>
</div>

<script>
    window.intercomSettings = {
        app_id: '<?php echo RA_WC_Config::get_intercom_app_id(); ?>',
        alignment: 'right',
        vertical_padding: 70,
    };
    (function () {
        var w = window;
        var ic = w.Intercom;
        if (typeof ic === 'function') {
            ic('reattach_activator');
            ic('update', window.intercomSettings);
        } else {
            var d = document;
            var i = function () {
                i.c(arguments)
            };
            i.q = [];
            i.c = function (args) {
                i.q.push(args)
            };
            w.Intercom = i;
            function l() {
                var s = d.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = 'https://widget.intercom.io/widget/jx5y0q3b';
                var x = d.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            }
            if (w.attachEvent) {
                w.attachEvent('onload', l);
            } else {
                w.addEventListener('load', l, false);
            }
        }
    })();
</script>
</body>
</html>