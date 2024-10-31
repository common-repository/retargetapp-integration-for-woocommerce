.wizard-container {
  margin: 24px auto;
  font-family: sans-serif;
  max-width: 922px;
  border-radius: 0;
  border: none;
  background: none;
  box-shadow: 0 2px 26px 0 rgba(0, 0, 0, 0.1);
}

.card {
  padding: 0;
}

.card-header{
  height: 56px;
  padding: 0;
  background: #fff;
}

@media (max-width: 576px) {
  .wizard-container {
      margin: 16px auto;
  }
}

.wizard-header {
  padding-left: 30px;
  padding-top: 8px;
  padding-bottom: 8px;
  line-height: 31px;
}

.wizard-body {
  padding: 0;
}

@media screen and (max-width: 576px) {
  .wizard-body {
      padding-left: 0;
      padding-right: 0;
  }
}

.wizard-footer {
  background: #fff;
  font-size: 13px;
  border-bottom: 4px solid #fe9929;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

.wizard-footer,
.wizard-footer:last-child {
  border-radius: 0;
}

.wizard-footer__p-text {
  margin-bottom: 0;
  font-size: 1em;
  font-weight: lighter;
  opacity: .8;
}

.wizard-footer__p-text-first {
  font-size: 1.45em;
  width: 100%;
  font-weight: normal;
}

.wizard-footer__link {
  padding: 0;
  display: inline-block;
  background: transparent;
  border: none;
  font-size: inherit;
  line-height: inherit;
  color: #1696ff;
  text-decoration: none;
  outline: none;
}

.wizard-footer__link:hover,
.wizard-footer__link:focus {
  text-decoration: none;
  box-shadow: none;
  outline: none;
  cursor: pointer;
}

/* HEADER */

:root {
  --ra-logo-width: 60px;
}

.header-navbar,
.header-navbar.navbar {
  padding: 0;
}

.header-navbar-brand,
.header-navbar-brand.navbar-brand {
  margin: 0;
  padding: 0;
  display: flex;
  flex-flow: row nowrap;
  justify-content: start;
  align-items: center;
  flex-grow: 1;
}

.header-navbar-collapse,
.header-navbar-collapse.navbar-collapse {
  flex-grow: 0;
  margin-left: 16px;
}

.header__app-logo {
  padding: 0;
  display: flex;
  flex-flow: row nowrap;
  align-items: center;
}

.header__app-logo__icon {
  height: 40px;
}

.header__app-logo__text {
  margin-left: 8px;
}

.header__page-title {
  margin-left: 16px;
}

.header__page-title__content {
  margin: 0;
  padding: 0 0 0 16px;
  display: inline-block;
  max-width: calc(100vw - var(--ra-logo-width));
  height: initial;
  border-left: 1px solid #D6DADF;
  color: #1f2f4d;
  font-size: 21px;
  font-weight: 300;
  line-height: 1.5em;
  text-overflow: ellipsis;
  overflow: hidden;
  vertical-align: middle;
}

@media (max-width: 1200px) {
  .header-navbar,
  .header-navbar.navbar {
      padding: 0 15px;
  }
}

@media (max-width: 767px) {
  .header-navbar-brand,
  .header-navbar-brand.navbar-brand {
      max-width: 75%;
  }

  .header-navbar-nav,
  .header-navbar-nav.navbar-nav {
      margin: 16px 0 0;
      flex-direction: row;
  }

  .header__page-title__content {
      padding: 4px 0 4px 8px;
      max-width: calc(100vw - var(--ra-logo-width));
      font-size: 17px;
      line-height: 1.25em;
      white-space: normal;
      vertical-align: middle;
  }
}

@media (max-width: 575px) {
  .header__page-title__content {
      max-width: calc(100vw - var(--ra-logo-width));
  }
}

/* BODY */

.wizard-body__wrapper {
  margin: 50px auto 120px;
  max-width: 540px;
  text-align: center;
  color: #1f2f4d;
}

.wizard-body__title {
  margin-bottom: 40px;
  padding-top: 6px;
  font-size: 32px;
  font-weight: 300;
  line-height: 1;
}

.wizard-body__description {
  margin-bottom: 40px;
  color: rgba(0, 34, 61, 0.77);
  font-size: 14px;
  font-weight: 400;
  letter-spacing: 0.25px;
  line-height: 21px;
}

.wizard-body__link {
  text-decoration: underline;
}

.wizard-body__privacy {
  max-width: 85%;
  width: 380px;
  margin: 0 auto;
}

.wizard-body__checkbox {
  width: 10%;
  max-width: 14px;
  vertical-align: middle;
  display: inline-block;
  cursor: pointer;
}

.wizard-body__checkbox-label {
  text-align: left;
  vertical-align: top;
  cursor: pointer;
  display: flex;
  width: 100%;
}

.wizard-body__checkbox-label,
.wizard-body__checkbox-label a {
  color: rgba(0, 34, 61, 0.77);
  font-size: 12px;
  font-weight: 400;
  letter-spacing: 0.5px;
  line-height: 18px;
}

.wizard-body__checkbox-label a {
  text-decoration: underline;
}

@media screen and (max-width: 576px) {
  .wizard-body__wrapper {
      margin: 0 auto;
  }
}

.custom-checkbox-container {
  display: inline-block;
  position: relative;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  padding: 0 23px 10px 0px;
}

/* Hide the browser's default checkbox */
.custom-checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Create a custom checkbox */
.checkmark {
  box-shadow: inset 0 0px 2px rgba(0, 34, 61, 0.25);
  border-radius: 2px;
  border: 1px solid #c4cdd5;
  background-color: #ffffff;
  position: absolute;
  top: 4px;
  left: 0;
  height: 14px;
  width: 14px;
}

/* On mouse-over, add a grey background color */
.custom-checkbox-container:hover input ~ .checkmark { 
  background-image: linear-gradient(180deg, #ffffff 0%, #f0f0f0 100%);
}

/* When the checkbox is checked, add a blue background */
.custom-checkbox-container input:checked ~ .checkmark {
  box-shadow: 0 1px 0 #e7eef3;
  border: 2px solid #1696ff;
  background: #1696ff url(<?php echo plugins_url('assets/images/checkmark.svg', dirname(__FILE__)) ?>) center no-repeat;
  background-size: 8px;
}

.custom-checkbox-container input:checked[disabled] ~ .checkmark {
  opacity: .5;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}
/* Hide default checkbox */
input[type=checkbox] {
  display: none;
}

/* GREEN BUTTON */

.btn {
  font-weight: 600;
  width: 240px;
  height: 40px;
  font-size: 14px;
  letter-spacing: 0.5px;
  text-align: center;
  position: relative;
  border-radius: 4px;
  outline: none;
  border: none;
  background-color: #07AE3A;
  color: #FFF;
  padding: 8px 16px 8px 16px;
}

.btn[disabled],
.btn[disabled]:hover {
  opacity: .5;
  background-color: #07AE3A;
  cursor: not-allowed;
  color: #FFF;
}

.btn:hover,
.btn:focus {
  cursor: pointer;
  outline: none;
  border: none;
  background-color: #07A336;
  color: #FFF;
}

.btn:focus {
  box-shadow: 0 0 0 4px #85CC9A;
}

.btn:active {
  outline: none;
  border: none;
  background-color: #07A336;
  box-shadow: inset 0 1px 4px 0 rgba(31, 77, 37, 0.6), inset 0 1px 8px 0 rgba(31, 77, 45, 0.2);
}

.btn_loading,
.btn_loading:hover,
.btn_loading:focus,
.btn_loading:active {
    color: transparent !important;
    background-color: #07AE3A;
    opacity: .7;
}

.btn_loading:before {
    content: '';
    position: absolute;
    left: calc(50% - 9px);
    top: calc(50% - 9px);
    width: 18px;
    height: 18px;
    background-image: url(<?php echo plugins_url('assets/images/white-spinner.svg', dirname(__FILE__)) ?>);
    background-repeat: no-repeat;
    animation: load 0.8s linear infinite;
}

@keyframes load {
  from {
      transform: rotate(0deg);
  }

  to {
      transform: rotate(360deg)
  }
}

/* NOTIFICATION */

.notification__wrapper {
  position: relative;
  max-width: 540px;
  min-height: 96px;
  border: 2px solid #1696ff;
  border-radius: 8px;
  padding: 16px 16px 16px 116px;
  text-align: start;
  margin-bottom: 55px;
}

.notification__wrapper::before {
  position: absolute;
  display: block;
  content: '';
  top: 0;
  bottom: 0;
  left: 0;
  width: 100px;
  background: #1696ff url(<?php echo plugins_url('assets/images/sign.svg', dirname(__FILE__)) ?>) no-repeat center 18px;
  background-size: 60px;
}

.notification__description {
  font-size: 14px;
  letter-spacing: .25;
  line-height: 21px;
  opacity: .87;
  color: #1f2f4d;
  margin-bottom: 0;
}

.notification__text {
  position: absolute;
  content: 'Required';
  font-size: 14px;
  letter-spacing: .25;
  line-height: 21px;
  opacity: .87;
  color: #1f2f4d;
}