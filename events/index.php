<?php
/* Minimal post-cancellation support page. The full event page remains in
 * index.html for history but is blocked by this directory's .htaccess.
 * Existing ticket QR codes use /events/?ref=... and must keep working. */
declare(strict_types=1);
$ref = strtoupper(trim((string)($_GET['ref'] ?? '')));
if (preg_match('/^AP26-[A-Z0-9]{6}$/', $ref) !== 1) {
    $ref = '';
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <meta name="theme-color" content="#2d5a3d">
  <title>Existing Booking Support | Owl I Health Care Centre</title>
  <style>
    *{box-sizing:border-box}body{margin:0;padding:24px;background:#f5f7f4;color:#25352a;font:16px/1.55 Arial,sans-serif}
    main{max-width:560px;margin:8vh auto;padding:28px;background:#fff;border:1px solid #d8e2d8;border-radius:14px;box-shadow:0 8px 28px #1b35200d}
    h1{margin:0 0 8px;color:#2d5a3d;font-size:1.55rem}p{margin:8px 0 18px}.field{display:flex;gap:10px;flex-wrap:wrap}
    input,button,.ticket{min-height:46px;border-radius:8px;font:inherit}input{flex:1 1 220px;min-width:0;padding:10px 12px;border:1px solid #9aab9d;text-transform:uppercase}
    button,.ticket{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border:0;background:#2d5a3d;color:#fff;font-weight:700;text-decoration:none;cursor:pointer}
    button:disabled{opacity:.65;cursor:wait}#result{margin-top:18px;padding:14px;border-radius:8px;background:#f0f5ef;min-height:24px}#result:empty{display:none}
    .help{font-size:.94rem;color:#58685b}a{color:#2d5a3d}footer{margin-top:20px;font-size:.9rem}
  </style>
</head>
<body>
<main>
  <h1>Existing booking support</h1>
  <p>The event has been cancelled. New registrations are closed. If you already have a Booking ID, check its payment status or download your paid ticket here.</p>
  <form id="booking-form">
    <label for="booking-ref">Booking ID</label>
    <div class="field">
      <input id="booking-ref" name="ref" value="<?= htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') ?>" placeholder="AP26-XXXXXX" maxlength="11" autocomplete="off" required>
      <button id="check-button" type="submit">Check status</button>
    </div>
  </form>
  <div id="result" role="status" aria-live="polite"></div>
  <p class="help">If you need help with an existing payment or refund, contact <a href="mailto:arokyapayanam@owlihealthcare.com">arokyapayanam@owlihealthcare.com</a> or WhatsApp <a href="https://wa.me/916379610554">+91 63796 10554</a>.</p>
  <footer><a href="/">Return to Owl I Health Care Centre</a></footer>
</main>
<script>
(function(){
  var form=document.getElementById('booking-form'), input=document.getElementById('booking-ref');
  var result=document.getElementById('result'), button=document.getElementById('check-button');
  function check(ref){
    ref=(ref||'').trim().toUpperCase(); input.value=ref;
    if(!/^AP26-[A-Z0-9]{6}$/.test(ref)){result.textContent='Enter a valid Booking ID in the format AP26-XXXXXX.';return;}
    button.disabled=true; result.textContent='Checking payment status…';
    fetch('/api/status.php?ref='+encodeURIComponent(ref),{cache:'no-store'})
      .then(function(r){return r.json().then(function(body){return {ok:r.ok,body:body};});})
      .then(function(res){
        var status=res.body&&res.body.status;
        if(res.ok&&status==='paid'){
          result.textContent='Payment verified. Your ticket is ready. ';
          var link=document.createElement('a'); link.className='ticket'; link.href='/api/ticket.php?ref='+encodeURIComponent(ref); link.textContent='Download ticket (PDF)'; result.appendChild(link);
        }else if(res.ok&&status==='pending'){
          result.textContent='Payment is still pending. Please contact us if you have already paid.';
        }else if(res.ok&&status==='failed'){
          result.textContent='This booking is marked failed. Contact us for help with an existing payment.';
        }else{
          result.textContent='Booking ID not found. Check the ID or contact us using the details above.';
        }
      })
      .catch(function(){result.textContent='Could not check the status right now. Please contact us using the details above.';})
      .finally(function(){button.disabled=false;});
  }
  form.addEventListener('submit',function(e){e.preventDefault();check(input.value);});
  if(input.value)check(input.value);
})();
</script>
</body>
</html>
