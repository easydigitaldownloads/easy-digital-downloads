!function(e){var t={};function n(o){if(t[o])return t[o].exports;var i=t[o]={i:o,l:!1,exports:{}};return e[o].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(o,i,function(t){return e[t]}.bind(null,i));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=329)}({0:function(e,t){e.exports=jQuery},329:function(e,t,n){"use strict";n.r(t),n(330),n(331),n(332),n(333)},330:function(e,t,n){(function(e){!function(e,t){"use strict";var n=t(".edd-email__recipient");if(n.length){var o=t(".edd-email__recipient--custom"),i=t(".edd-email__recipient--admin");n.on("change",(function(e){"default"===e.target.value?(o.hide(),i.show()):"custom"===e.target.value?(o.show(),i.hide()):(o.hide(),i.hide())}))}}(document,e)}).call(this,n(0))},331:function(e,t){var n=document.getElementById("edd-email-reset");n&&(n.addEventListener("click",(function(e){e.preventDefault(),n.classList.remove("button-primary"),n.classList.add("updating-message"),n.disabled=!0;var t={action:"edd_reset_email",nonce:EDDAdminEmails.nonce,email_id:n.dataset.email};fetch(EDDAdminEmails.ajaxurl,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:new URLSearchParams(t)}).then((function(e){return e.json()})).then((function(e){if(e.success){var t=tinymce.get("edd-email-content"),o=document.getElementById("edd-email-content");t&&t.setContent(e.data.content),o.value=e.data.content,document.querySelector(".edd-promo-notice-dismiss").click(),n.classList.remove("updating-message"),n.classList.add("button-primary","updated-message")}})).catch((function(e){console.error(e)}))})),document.addEventListener("edd_promo_notice_dismiss",(function(e){n.classList.remove("updated-message"),n.disabled=!1})))},332:function(e,t){document.querySelectorAll(".edd-email-status-badge").forEach((function(e){setTimeout((function(){e.classList.contains("edd-hidden")||e.classList.add("edd-fadeout")}),5e3)})),document.getElementById("submit").addEventListener("click",(function(e){document.querySelectorAll(".edd-email-status-badge").forEach((function(e){e.classList.contains("edd-hidden")?e.classList.remove("edd-hidden"):e.remove()}))}))},333:function(e,t){document.addEventListener("DOMContentLoaded",(function(){for(var e=document.querySelectorAll("input, textarea"),t=0;t<e.length;t++)e[t].addEventListener("change",(function(){window.onbeforeunload=function(){return!0}}));document.getElementById("submit").addEventListener("click",(function(){window.onbeforeunload=null}))}))}});