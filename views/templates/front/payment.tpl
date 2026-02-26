{extends file='page.tpl'}
{block name='page_content'}
<div style="text-align:center;padding:20px;">
  <h2>QPay Төлбөр</h2>
  {if isset($invoice.qr_image) && $invoice.qr_image}
    <img src="data:image/png;base64,{$invoice.qr_image}" width="256" height="256" alt="QR Code" style="margin:20px 0;">
  {/if}
  <p>Банкны аппликейшнээр төлөх:</p>
  <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:16px 0;">
    {if isset($invoice.urls)}
      {foreach $invoice.urls as $link}
        <a href="{$link.link}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border:1px solid #ddd;border-radius:8px;text-decoration:none;color:#333;">
          {if isset($link.logo) && $link.logo}<img src="{$link.logo}" width="24" height="24">{/if}
          {$link.name}
        </a>
      {/foreach}
    {/if}
  </div>
  <p style="color:#666;">Төлбөр баталгаажихыг хүлээж байна...</p>
</div>
<script>
var qpayPoll = setInterval(function() {
  fetch('{$check_url}').then(function(r){ return r.json(); }).then(function(data){
    if(data.paid){
      clearInterval(qpayPoll);
      window.location.href = '{$return_url}';
    }
  });
}, 3000);
</script>
{/block}
